<?php

namespace Unit\app\Domain\Recap;

use Carbon\CarbonImmutable;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Recap\Repositories\Recap as RecapRepository;
use Leantime\Domain\Recap\Services\Recap;
use Leantime\Domain\Reminders\Services\RecipientAccess;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\app\Domain\Reminders\ReminderDatabase;
use Unit\TestCase;

/**
 * "Finished" for the recap: moved to a DONE-type status in the range, by this person or on a
 * to-do assigned to them; still done at the end of the range; no milestones; one row per to-do.
 * Runs the real history query on SQLite. Status 0 is done here, 3 is open.
 */
class RecapTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use ReminderDatabase;

    private const START = '2026-11-16 00:00:00';

    private const END = '2026-11-22 23:59:59';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createReminderDatabase();
        $this->addUser(7);
        $this->addUser(8);
        $this->addProject(1, name: 'Website');
        $this->addProject(2, name: 'Hidden');
        session(['userdata' => ['id' => 7]]);
    }

    /**
     * @param  array<int, int>  $deniedProjects  projects where user 7 may not view to-dos
     */
    private function recap(array $deniedProjects = []): Recap
    {
        $repository = (new \ReflectionClass(RecapRepository::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(RecapRepository::class, 'db'))->setValue($repository, $this->db);

        $recap = new class($repository, $this->makeEmpty(TicketRepository::class), $this->makeEmpty(ProjectRepository::class), $this->makeEmpty(RecipientAccess::class)) extends Recap
        {
            /** @var array<int, int> */
            public array $denied = [];

            /** @var array<int, int> project ids asked about */
            public array $asked = [];

            protected function isDone(int $projectId, string $status): bool
            {
                return $status === '0';
            }

            protected function canView(int $userId, int $projectId): bool
            {
                $this->asked[] = $projectId;

                return ! in_array($projectId, $this->denied, true);
            }
        };
        $recap->denied = $deniedProjects;

        return $recap;
    }

    private function finished(?Recap $recap = null): array
    {
        return ($recap ?? $this->recap())->finishedBetweenFor(7, CarbonImmutable::parse(self::START, 'UTC'), CarbonImmutable::parse(self::END, 'UTC'));
    }

    public function test_what_counts_as_finished(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'status' => 0, 'headline' => 'Closed by me']);
        $this->addStatusChange(1, 7, 0, '2026-11-18 10:00:00');

        $this->addTicket(2, ['editorId' => '7', 'status' => 0, 'headline' => 'Mine, closed by someone else', 'milestoneid' => 40]);
        $this->addStatusChange(2, 8, 0, '2026-11-19 10:00:00');

        $this->addTicket(3, ['editorId' => '99', 'userId' => 7, 'status' => 0, 'headline' => 'Someone else\'s, closed by me']);
        $this->addStatusChange(3, 7, 0, '2026-11-17 10:00:00');

        $this->addTicket(4, ['editorId' => '7', 'status' => 3, 'headline' => 'Re-opened']);
        $this->addStatusChange(4, 7, 0, '2026-11-17 10:00:00');
        $this->addStatusChange(4, 8, 3, '2026-11-20 10:00:00');

        $this->addTicket(5, ['editorId' => '7', 'type' => 'milestone', 'status' => 0]);
        $this->addStatusChange(5, 7, 0, '2026-11-18 10:00:00');

        $this->addTicket(6, ['editorId' => '99', 'status' => 0, 'headline' => 'Neither']);
        $this->addStatusChange(6, 8, 0, '2026-11-18 10:00:00');

        $this->addTicket(7, ['editorId' => '7', 'status' => 0, 'headline' => 'Before the range']);
        $this->addStatusChange(7, 7, 0, '2026-11-10 10:00:00');

        $this->addTicket(8, ['editorId' => '7', 'status' => 3, 'headline' => 'Moved, not done']);
        $this->addStatusChange(8, 7, 3, '2026-11-18 10:00:00');

        $this->addTicket(9, ['editorId' => '7', 'status' => 0, 'headline' => 'Closed twice']);
        $this->addStatusChange(9, 7, 0, '2026-11-18 10:00:00');
        $this->addStatusChange(9, 8, 3, '2026-11-19 10:00:00');
        $this->addStatusChange(9, 8, 0, '2026-11-21 10:00:00');

        $rows = $this->finished();

        $this->assertSame([9, 2, 1, 3], array_column($rows, 'taskId'), 'newest first; re-opened, milestones, others\' work, earlier and not-done changes left out');
        $this->assertSame([
            'taskId' => 2,
            'headline' => 'Mine, closed by someone else',
            'projectId' => 1,
            'projectName' => 'Website',
            'milestoneId' => 40,
            'finishedAt' => '2026-11-19T10:00:00Z',
            'closedBy' => ['userId' => 8, 'name' => 'User 8'],
        ], $rows[1]);
        $this->assertNull($rows[2]['closedBy'], 'closed by the person themselves');
        $this->assertSame('2026-11-21T10:00:00Z', $rows[0]['finishedAt'], 'the last qualifying change');
    }

    public function test_a_to_do_that_matches_both_ways_is_listed_once(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'status' => 0]);
        $this->addStatusChange(1, 7, 0, '2026-11-18 10:00:00');
        $this->addStatusChange(1, 7, 0, '2026-11-18 11:00:00');

        $this->assertSame([1], array_column($this->finished(), 'taskId'));
    }

    public function test_only_projects_the_person_may_view(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'status' => 0]);
        $this->addTicket(2, ['editorId' => '7', 'status' => 0, 'projectId' => 2]);
        $this->addStatusChange(1, 7, 0, '2026-11-18 10:00:00');
        $this->addStatusChange(2, 7, 0, '2026-11-18 10:00:00');

        $this->assertSame([1], array_column($this->finished($this->recap([2])), 'taskId'));
    }

    public function test_access_is_checked_once_per_project(): void
    {
        foreach ([1, 2, 3] as $id) {
            $this->addTicket($id, ['editorId' => '7', 'status' => 0]);
            $this->addStatusChange($id, 7, 0, '2026-11-18 10:00:00');
        }
        $recap = $this->recap();

        $this->assertCount(3, $this->finished($recap));
        $this->assertSame([1], $recap->asked, 'three to-dos in one project: one check');
    }

    public function test_the_api_is_for_the_signed_in_person_and_checks_the_range(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'status' => 0]);
        $this->addStatusChange(1, 7, 0, '2026-11-18 10:00:00');

        $this->assertSame([1], array_column($this->recap()->finishedBetween('2026-11-16T00:00:00Z', '2026-11-23T00:00:00Z'), 'taskId'));
        $this->assertSame([1], array_column($this->recap()->finishedBetween('2026-11-18T11:00:00+01:00', '2026-11-23T00:00:00Z'), 'taskId'), '11:00+01:00 is 10:00 UTC, the change itself');
        $this->assertSame([], $this->recap()->finishedBetween('2026-11-18T11:00:01+01:00', '2026-11-23T00:00:00Z'), 'one second later starts after it');

        foreach ([['2026-11-23T00:00:00Z', '2026-11-16T00:00:00Z'], ['2026-01-01T00:00:00Z', '2026-11-16T00:00:00Z'], ['yesterday-ish', 'nope']] as [$start, $end]) {
            try {
                $this->recap()->finishedBetween($start, $end);
                $this->fail('accepted '.$start.' – '.$end);
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }

        session(['userdata' => ['id' => 0]]);
        $this->expectException(AuthorizationException::class);
        $this->recap()->finishedBetween('2026-11-16T00:00:00Z', '2026-11-23T00:00:00Z');
    }
}
