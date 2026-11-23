<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Tickets\Services\Suggestions;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Unit\TestCase;

/**
 * "Tasks to consider" and "Quick wins": the mobile Today screen's rules, served from core so
 * every client suggests the same to-dos. The clock is fixed at Sunday 2026-11-22 18:00 in Los
 * Angeles (02:00 UTC on Monday the 23rd), so days are read in the person's timezone, not UTC.
 */
class SuggestionsTest extends TestCase
{
    private const TZ = 'America/Los_Angeles';

    /** @var list<array<string, mixed>> The open to-dos the Tickets service returns. */
    private array $open = [];

    /** @var int[] Projects where the signed-in person's role may view to-dos. */
    private array $viewableProjects = [3];

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-23 02:00:00', 'UTC'));
        session([
            'userdata' => ['id' => 7, 'role' => 'editor', 'name' => 'Editor'],
            'usersettings.timezone' => self::TZ,
        ]);
        app()->forgetInstance(DateTimeHelper::class);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        app()->forgetInstance(DateTimeHelper::class);

        parent::tearDown();
    }

    public function test_tasks_to_consider_are_undated_and_recent_or_important(): void
    {
        $this->open = [
            $this->task(1, created: '2026-11-20 10:00:00'),                       // recent, undated
            $this->task(2, created: '2026-10-01 10:00:00', priority: '2'),        // old but High
            $this->task(3, created: '2026-10-01 10:00:00', priority: '4'),        // old and Low: no
            $this->task(4, created: '2026-11-21 10:00:00', due: '2026-11-25 07:59:59'), // dated: no
            $this->task(5, created: '2026-11-21 10:00:00', due: '0000-00-00 00:00:00'), // "no date" sentinel
            $this->task(6, created: '2026-09-01 10:00:00', priority: '1'),        // old but Critical
        ];

        $result = $this->service()->getTasksToConsider(0);

        $this->assertSame(4, $result['total']);
        // Recently added first (newest first), then the rest by priority.
        $this->assertSame([5, 1, 6, 2], array_column($result['tasks'], 'id'));
        $this->assertSame(['recent', 'recent', 'high_priority', 'high_priority'], array_column($result['tasks'], 'reason'));
    }

    public function test_the_default_limit_is_three_and_total_counts_everything(): void
    {
        $this->open = array_map(fn ($id) => $this->task($id, created: '2026-11-2'.($id % 10).' 10:00:00'), [1, 2, 3, 4, 5]);

        $result = $this->service()->getTasksToConsider();

        $this->assertCount(3, $result['tasks']);
        $this->assertSame(5, $result['total']);
    }

    public function test_quick_wins_are_xs_or_smaller_and_due_within_a_week(): void
    {
        $this->open = [
            $this->task(1, effort: '1', due: '2026-11-25 07:59:59'),   // XS, due Tue the 24th in LA
            $this->task(2, effort: '0.5', due: '2026-11-19 07:59:59'), // tiny, past due
            $this->task(3, effort: '2', due: '2026-11-24 07:59:59'),   // S: too big
            $this->task(4, effort: '1', due: '2026-11-30 00:00:00'),   // calendar date 30th: 8 days out
            $this->task(5, effort: '1', due: '2026-11-29 00:00:00'),   // calendar date 29th: day 7
            $this->task(6, effort: '1'),                               // no due date
            $this->task(7, effort: '', due: '2026-11-24 07:59:59'),    // no size
        ];

        $result = $this->service()->getQuickWins(0);

        $this->assertSame([2, 1, 5], array_column($result['tasks'], 'id'));
        $this->assertSame(['2026-11-18', '2026-11-24', '2026-11-29'], array_column($result['tasks'], 'dueDay'));
        $this->assertSame(3, $result['total']);
    }

    public function test_a_web_end_of_day_due_date_is_read_in_the_persons_timezone(): void
    {
        // The web stores "due Sunday the 22nd" for Los Angeles as 07:59:59 UTC on the 23rd.
        $this->open = [$this->task(1, effort: '1', due: '2026-11-23 07:59:59')];

        $this->assertSame('2026-11-22', $this->service()->getQuickWins()['tasks'][0]['dueDay']);
    }

    public function test_the_two_lists_never_overlap(): void
    {
        $this->open = [
            $this->task(1, created: '2026-11-21 10:00:00', effort: '1', due: '2026-11-24 07:59:59'),
            $this->task(2, created: '2026-11-21 10:00:00', effort: '1'),
        ];

        $this->assertSame([2], array_column($this->service()->getTasksToConsider(0)['tasks'], 'id'));
        $this->assertSame([1], array_column($this->service()->getQuickWins(0)['tasks'], 'id'));
    }

    public function test_to_dos_in_projects_where_viewing_to_dos_is_denied_are_left_out(): void
    {
        $this->viewableProjects = [3];
        $this->open = [
            $this->task(1, created: '2026-11-21 10:00:00'),
            $this->task(2, created: '2026-11-21 10:00:00', project: 9),
            $this->task(3, effort: '1', due: '2026-11-24 07:59:59'),
            $this->task(4, effort: '1', due: '2026-11-24 07:59:59', project: 9),
        ];

        $consider = $this->service()->getTasksToConsider(0);
        $quickWins = $this->service()->getQuickWins(0);

        $this->assertSame([1], array_column($consider['tasks'], 'id'));
        $this->assertSame(1, $consider['total'], 'the total never counts hidden to-dos');
        $this->assertSame([3], array_column($quickWins['tasks'], 'id'));
        $this->assertSame(1, $quickWins['total']);
    }

    public function test_limits_outside_the_range_are_refused(): void
    {
        foreach ([-1, 101] as $limit) {
            try {
                $this->service()->getQuickWins($limit);
                $this->fail("A limit of {$limit} should be refused.");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('limit', $e->getErrorData());
            }
        }
    }

    public function test_nothing_is_suggested_without_a_signed_in_person(): void
    {
        session()->forget('userdata');
        $this->open = [$this->task(1, created: '2026-11-21 10:00:00')];

        $this->assertSame(['tasks' => [], 'total' => 0], $this->service()->getTasksToConsider());
    }

    private function service(): Suggestions
    {
        $tickets = $this->createMock(TicketService::class);
        $tickets->method('getAllOpenUserTickets')->willReturnCallback(fn () => $this->open);

        $permissions = $this->createMock(\Leantime\Core\Auth\Permissions\PermissionService::class);
        $permissions->method('currentUserCan')->willReturnCallback(
            fn (string $permission, ?int $projectId = null) => in_array($projectId, $this->viewableProjects, true)
        );

        $suggestions = new Suggestions($tickets);
        $suggestions->setPermissionService($permissions);

        return $suggestions;
    }

    /**
     * An open to-do row as getAllOpenUserTickets returns it.
     *
     * @return array<string, mixed>
     */
    private function task(int $id, string $created = '2026-01-01 10:00:00', string $priority = '', string $effort = '', ?string $due = null, int $project = 3): array
    {
        return [
            'id' => $id,
            'headline' => 'To-do '.$id,
            'projectId' => $project,
            'type' => 'task',
            'status' => 3,
            'date' => $created,
            'priority' => $priority,
            'storypoints' => $effort,
            'dateToFinish' => $due,
            'editFrom' => null,
            'editTo' => null,
        ];
    }
}
