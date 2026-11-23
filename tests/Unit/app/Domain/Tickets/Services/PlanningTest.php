<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Services\Planning;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Unit\TestCase;

/**
 * Planning a to-do for a day writes a date-only Work Start (local midnight, no end) and nothing
 * else: never the due date, never over a time box, never on a milestone or a hidden to-do.
 */
class PlanningTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const TZ = 'America/Los_Angeles';

    /** @var array<int, TicketModel> */
    private array $tickets = [];

    /** @var array<int, array{0: int, 1: array<string, mixed>}> Every patch() call. */
    private array $patches = [];

    /** Whether the signed-in person has tickets.view in the to-dos' project. */
    private bool $canView = true;

    protected function setUp(): void
    {
        parent::setUp();

        session([
            'userdata' => ['id' => 7, 'role' => 'editor', 'name' => 'Editor'],
            'usersettings.timezone' => self::TZ,
        ]);
        app()->forgetInstance(DateTimeHelper::class);

        $this->patches = [];
        $this->canView = true;
        $this->tickets = [
            11 => $this->ticket(11),
            12 => $this->ticket(12, editFrom: $this->utc('2026-11-26 14:00'), editTo: $this->utc('2026-11-26 15:30')),
            13 => $this->ticket(13, editFrom: $this->utc('2026-11-25 00:00')),
            30 => $this->ticket(30, type: 'milestone'),
        ];
    }

    protected function tearDown(): void
    {
        app()->forgetInstance(DateTimeHelper::class);

        parent::tearDown();
    }

    public function test_planning_writes_local_midnight_with_no_end_and_leaves_the_due_date(): void
    {
        $result = $this->service()->planTaskForDay(11, '2026-11-24');

        $this->assertSame(['taskId' => 11, 'date' => '2026-11-24'], $result);
        $this->assertCount(1, $this->patches);

        [$id, $values] = $this->patches[0];
        $this->assertSame(11, $id);
        $this->assertSame(['editFrom', 'editTo'], array_keys($values));
        $this->assertInstanceOf(CarbonImmutable::class, $values['editFrom']);
        $this->assertSame('2026-11-24 08:00:00', $values['editFrom']->setTimezone('UTC')->format('Y-m-d H:i:s'));
        $this->assertNull($values['editTo']);
    }

    public function test_replanning_a_date_only_plan_moves_it(): void
    {
        $this->service()->planTaskForDay(13, '2026-11-27');

        $this->assertSame('2026-11-27 08:00:00', $this->patches[0][1]['editFrom']->setTimezone('UTC')->format('Y-m-d H:i:s'));
    }

    public function test_a_time_box_is_never_moved(): void
    {
        $this->assertFieldError('taskId', fn () => $this->service()->planTaskForDay(12, '2026-11-24'));
        $this->assertSame([], $this->patches);
    }

    public function test_milestones_and_hidden_or_missing_to_dos_are_refused(): void
    {
        foreach ([30, 99, 0] as $taskId) {
            $this->assertFieldError('taskId', fn () => $this->service()->planTaskForDay($taskId, '2026-11-24'));
        }
        $this->assertSame([], $this->patches);
    }

    public function test_only_real_days_are_accepted(): void
    {
        foreach (['2026-02-30', 'tomorrow', '11/24/2026', ''] as $date) {
            $this->assertFieldError('date', fn () => $this->service()->planTaskForDay(11, $date));
        }
        $this->assertSame([], $this->patches);
    }

    public function test_a_failed_write_is_reported(): void
    {
        $this->assertFieldError('taskId', fn () => $this->service(patchSucceeds: false)->planTaskForDay(11, '2026-11-24'));
    }

    public function test_clearing_a_date_only_plan_empties_the_work_start(): void
    {
        $this->assertTrue($this->service()->clearTaskPlan(13));

        $this->assertSame([[13, ['editFrom' => null, 'editTo' => null]]], $this->patches);
    }

    public function test_clearing_a_to_do_without_a_plan_writes_nothing(): void
    {
        $this->assertTrue($this->service()->clearTaskPlan(11));
        $this->assertSame([], $this->patches);
    }

    public function test_clearing_never_removes_a_time_box(): void
    {
        $this->assertFieldError('taskId', fn () => $this->service()->clearTaskPlan(12));
        $this->assertSame([], $this->patches);
    }

    public function test_task_plan_reads_days_and_time_box_in_the_persons_timezone(): void
    {
        $this->assertSame(
            ['taskId' => 12, 'days' => ['2026-11-26'], 'timeBoxed' => true],
            $this->service()->getTaskPlan(12)
        );
        $this->assertSame(
            ['taskId' => 13, 'days' => ['2026-11-25'], 'timeBoxed' => false],
            $this->service()->getTaskPlan(13)
        );
        $this->assertSame(
            ['taskId' => 11, 'days' => [], 'timeBoxed' => false],
            $this->service()->getTaskPlan(11)
        );
    }

    public function test_reading_a_plan_needs_view_permission_in_the_to_dos_project(): void
    {
        $this->canView = false;

        $this->assertFieldError('taskId', fn () => $this->service()->getTaskPlan(12));
    }

    public function test_writes_without_view_permission_answer_like_a_missing_to_do(): void
    {
        $this->canView = false;
        $hiddenMessage = ["That to-do doesn't exist, or you can't see it."];

        // A milestone (30) and a time box (12) must not be told apart from a missing to-do.
        foreach ([
            fn () => $this->service()->planTaskForDay(30, '2026-11-24'),
            fn () => $this->service()->planTaskForDay(12, '2026-11-24'),
            fn () => $this->service()->clearTaskPlan(12),
            fn () => $this->service()->planTaskForDay(999, '2026-11-24'),
        ] as $call) {
            try {
                $call();
                $this->fail('Expected a ValidationException.');
            } catch (ValidationException $e) {
                $this->assertSame($hiddenMessage, $e->getErrorData()['taskId'] ?? null);
            }
        }

        $this->assertSame([], $this->patches);
    }

    private function service(bool $patchSucceeds = true): Planning
    {
        $tickets = $this->createMock(TicketService::class);
        $tickets->method('getTicket')->willReturnCallback(fn ($id) => $this->tickets[(int) $id] ?? false);
        $tickets->method('patch')->willReturnCallback(function ($id, $values) use ($patchSucceeds) {
            $this->patches[] = [(int) $id, $values];

            return $patchSucceeds;
        });

        $planning = new Planning($tickets);
        $planning->setPermissionService($this->make(\Leantime\Core\Auth\Permissions\PermissionService::class, [
            'currentUserCan' => fn () => $this->canView,
            'authorize' => fn () => null,
        ]));

        return $planning;
    }

    /** A local Los Angeles wall-clock time as stored (UTC). */
    private function utc(string $local): string
    {
        return CarbonImmutable::createFromFormat('Y-m-d H:i', $local, self::TZ)->setTimezone('UTC')->format('Y-m-d H:i:s');
    }

    private function ticket(int $id, string $type = 'task', ?string $editFrom = null, ?string $editTo = null): TicketModel
    {
        $ticket = new TicketModel;
        $ticket->id = $id;
        $ticket->headline = 'To-do '.$id;
        $ticket->projectId = 3;
        $ticket->type = $type;
        $ticket->editFrom = $editFrom;
        $ticket->editTo = $editTo;
        $ticket->dateToFinish = '2026-11-30 17:00:00';

        return $ticket;
    }

    private function assertFieldError(string $field, callable $call): void
    {
        try {
            $call();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->getErrorData());

            return;
        }

        $this->fail("Expected a ValidationException on '{$field}'.");
    }
}
