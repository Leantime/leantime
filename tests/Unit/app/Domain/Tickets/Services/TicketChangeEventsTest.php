<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Events\MilestoneCreated;
use Leantime\Domain\Tickets\Events\TicketAssigned;
use Leantime\Domain\Tickets\Events\TicketCompleted;
use Leantime\Domain\Tickets\Events\TicketCreated;
use Leantime\Domain\Tickets\Events\TicketScheduled;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\TicketHistory;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Unit\TestCase;

/**
 * Ticket change events derived from the stored ticket read before a write: TicketCompleted fires
 * only on a persisted transition INTO a DONE-type status from a status that was not DONE;
 * TicketScheduled / TicketAssigned only when the work window / assignee actually changed.
 */
class TicketChangeEventsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, TicketCompleted> */
    private array $completed = [];

    /** @var array<int, TicketScheduled> */
    private array $scheduled = [];

    /** @var array<int, TicketAssigned> */
    private array $assigned = [];

    /** @var array<int, TicketCreated> */
    private array $created = [];

    private array $milestonesCreated = [];

    private array $dispatcherSnapshot = [];

    private const DISPATCHER_PROPS = [
        'eventRegistry',
        'filterRegistry',
        'available_hooks',
        'patternMatchCache',
        'compiledPatternCache',
        'eventRegistryVersion',
        'filterRegistryVersion',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);
        session(['userdata' => ['id' => 1, 'role' => 'editor', 'name' => 'Caller']]);

        app()->instance(EnvironmentCore::class, $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]));
        app()->instance(LanguageCore::class, $this->make(LanguageCore::class, ['__' => fn ($key) => $key]));

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (self::DISPATCHER_PROPS as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }

        $this->completed = [];
        $this->scheduled = [];
        $this->assigned = [];
        $this->created = [];
        $this->milestonesCreated = [];
        EventDispatcher::add_event_listener(TicketCompleted::class, function (TicketCompleted $event) {
            $this->completed[] = $event;
        });
        EventDispatcher::add_event_listener(TicketScheduled::class, function (TicketScheduled $event) {
            $this->scheduled[] = $event;
        });
        EventDispatcher::add_event_listener(TicketAssigned::class, function (TicketAssigned $event) {
            $this->assigned[] = $event;
        });
        EventDispatcher::add_event_listener(TicketCreated::class, function (TicketCreated $event) {
            $this->created[] = $event;
        });
        EventDispatcher::add_event_listener(MilestoneCreated::class, function (MilestoneCreated $event) {
            $this->milestonesCreated[] = $event;
        });
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }

        parent::tearDown();
    }

    /**
     * A service whose tickets all live in project 7 with the given stored statuses, writes always
     * succeed and the caller may edit project 7. Status 0 is DONE, 3 NEW, 4 INPROGRESS, 5 a second
     * DONE-type status.
     *
     * @param  array<int, int>  $storedStatusByTicket  Ticket id => stored status.
     * @param  array<string, mixed>  $storedFields  Further stored fields of every ticket (editFrom, editorId, ...).
     */
    private function buildService(array $storedStatusByTicket, array $storedFields = []): TicketsService
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicket' => fn ($id) => $this->make(TicketModel::class, array_merge([
                'id' => (int) $id,
                'projectId' => 7,
                'headline' => 'T'.$id,
                'status' => $storedStatusByTicket[(int) $id] ?? 3,
            ], $storedFields)),
            'patchTicket' => fn () => true,
            'addTicket' => fn () => 42,
            'updateTicketStatus' => fn () => true,
            'getStateLabels' => fn () => [
                0 => ['name' => 'Done', 'statusType' => 'DONE'],
                3 => ['name' => 'New', 'statusType' => 'NEW'],
                4 => ['name' => 'In Progress', 'statusType' => 'INPROGRESS'],
                5 => ['name' => 'Shipped', 'statusType' => 'DONE'],
            ],
        ]);

        $service = new TicketsService(
            language: $this->make(LanguageCore::class, ['__' => fn ($key) => $key]),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class, [
                'isUserAssignedToProject' => fn () => true,
                'notifyProjectUsers' => fn () => null,
            ]),
            timesheetService: $this->make(TimesheetService::class, ['isClocked' => fn () => false]),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class, ['userNow' => fn () => CarbonImmutable::now('UTC')]),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class)
        );
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn () => true,
            'authorize' => fn () => null,
        ]));

        return $service;
    }

    public function test_patch_from_new_to_done_fires_ticket_completed(): void
    {
        $this->assertTrue($this->buildService([5 => 3])->patch(5, ['status' => 0]));

        $this->assertCount(1, $this->completed);
        $this->assertSame(5, $this->completed[0]->ticketId);
        $this->assertSame(7, $this->completed[0]->projectId);
    }

    public function test_patch_between_done_statuses_does_not_fire(): void
    {
        $this->buildService([5 => 0])->patch(5, ['status' => 5]);

        $this->assertSame([], $this->completed, 'a ticket that already was done is not completed again');
    }

    public function test_patch_to_a_non_done_status_does_not_fire(): void
    {
        $this->buildService([5 => 3])->patch(5, ['status' => 4]);

        $this->assertSame([], $this->completed);
    }

    public function test_patch_without_status_does_not_fire(): void
    {
        $this->buildService([5 => 3])->patch(5, ['headline' => 'Renamed']);

        $this->assertSame([], $this->completed);
    }

    public function test_the_same_ticket_completes_once_per_service_instance(): void
    {
        $service = $this->buildService([5 => 3]);

        $service->patch(5, ['status' => 0]);
        $service->patch(5, ['status' => 0]);

        $this->assertCount(1, $this->completed, 'repeated writes of one transition in a request report one completion');
    }

    public function test_kanban_move_fires_only_for_tickets_that_became_done(): void
    {
        // Ticket 5 moves NEW -> DONE; ticket 6 already was DONE and is only re-posted with its column.
        $service = $this->buildService([5 => 3, 6 => 0]);

        $this->assertTrue($service->updateTicketStatusAndSorting(['0' => 'ticket[]=5&ticket[]=6'], null));

        $this->assertCount(1, $this->completed);
        $this->assertSame(5, $this->completed[0]->ticketId);
        $this->assertSame(7, $this->completed[0]->projectId);
    }

    public function test_completion_carries_the_tickets_context(): void
    {
        $this->buildService([5 => 3], [
            'type' => 'task',
            'dependingTicketId' => 4,
            'editorId' => 1,
            'date' => CarbonImmutable::now('UTC')->subDays(3)->format('Y-m-d H:i:s'),
            'dateToFinish' => CarbonImmutable::now('UTC')->subDay()->format('Y-m-d H:i:s'),
            'editFrom' => CarbonImmutable::now('UTC')->format('Y-m-d H:i:s'),
        ])->patch(5, ['status' => 0]);

        $this->assertCount(1, $this->completed);
        $event = $this->completed[0];
        $this->assertSame('subtask', $event->type, 'a task with a parent counts as a subtask');
        $this->assertTrue($event->completedByAssignee);
        $this->assertSame(3, $event->daysToComplete);
        $this->assertTrue($event->hadDueDate);
        $this->assertTrue($event->wasOverdue);
        $this->assertTrue($event->wasScheduledToday);
    }

    public function test_scheduling_an_unscheduled_ticket_fires_ticket_scheduled(): void
    {
        $start = CarbonImmutable::create(2026, 10, 6, 9, 0, 0, 'UTC');

        $this->buildService([5 => 3])->patch(5, ['editFrom' => $start, 'editTo' => $start->addHour()]);

        $this->assertCount(1, $this->scheduled);
        $this->assertSame(5, $this->scheduled[0]->ticketId);
        $this->assertSame(7, $this->scheduled[0]->projectId);
        $this->assertSame('2026-10-06 09:00:00', $this->scheduled[0]->editFrom);
        $this->assertSame('2026-10-06 10:00:00', $this->scheduled[0]->editTo);
        $this->assertFalse($this->scheduled[0]->rescheduled);
    }

    public function test_moving_a_scheduled_ticket_fires_as_rescheduled(): void
    {
        $this->buildService([5 => 3], ['editFrom' => '2026-10-06 09:00:00', 'editTo' => '2026-10-06 10:00:00'])
            ->patch(5, ['editFrom' => CarbonImmutable::create(2026, 10, 7, 9, 0, 0, 'UTC')]);

        $this->assertCount(1, $this->scheduled);
        $this->assertSame('2026-10-07 09:00:00', $this->scheduled[0]->editFrom);
        $this->assertSame('2026-10-06 10:00:00', $this->scheduled[0]->editTo, 'an untouched end keeps its stored value');
        $this->assertTrue($this->scheduled[0]->rescheduled);
    }

    public function test_an_unchanged_or_cleared_schedule_does_not_fire(): void
    {
        $service = $this->buildService([5 => 3], ['editFrom' => '2026-10-06 09:00:00', 'editTo' => '2026-10-06 10:00:00']);

        $service->patch(5, ['editFrom' => CarbonImmutable::create(2026, 10, 6, 9, 0, 0, 'UTC')]);
        $service->patch(5, ['editFrom' => '', 'editTo' => '']);

        $this->assertSame([], $this->scheduled);
    }

    public function test_reassigning_a_ticket_fires_ticket_assigned_once(): void
    {
        $service = $this->buildService([5 => 3], ['editorId' => 2]);

        $service->patch(5, ['editorId' => 3]);
        $service->patch(5, ['editorId' => 3]);

        $this->assertCount(1, $this->assigned, 'the same change written twice in a request is reported once');
        $this->assertSame(3, $this->assigned[0]->assigneeId);
        $this->assertSame(2, $this->assigned[0]->previousAssigneeId);
        $this->assertFalse($this->assigned[0]->assignedToSelf);
    }

    public function test_same_or_empty_assignee_does_not_fire(): void
    {
        $service = $this->buildService([5 => 3], ['editorId' => 2]);

        $service->patch(5, ['editorId' => 2]);
        $service->patch(5, ['editorId' => '']);

        $this->assertSame([], $this->assigned);
    }

    public function test_creating_an_assigned_ticket_fires_created_with_context_and_assigned(): void
    {
        $this->buildService([])->quickAddTicket([
            'headline' => 'New',
            'projectId' => 7,
            'editorId' => 1,
            'dateToFinish' => CarbonImmutable::create(2026, 10, 9, 0, 0, 0, 'UTC'),
            'origin' => 'mcp',
        ]);

        $this->assertCount(1, $this->created);
        $this->assertSame(42, $this->created[0]->ticketId);
        $this->assertSame('mcp', $this->created[0]->origin);
        $this->assertSame('task', $this->created[0]->type);
        $this->assertTrue($this->created[0]->hasDueDate);
        $this->assertFalse($this->created[0]->assignedToOther);
        $this->assertSame(7, $this->created[0]->projectId);

        $this->assertCount(1, $this->assigned);
        $this->assertTrue($this->assigned[0]->assignedToSelf);
        $this->assertNull($this->assigned[0]->previousAssigneeId);
    }

    public function test_creating_a_milestone_carries_its_origin_and_project(): void
    {
        $service = $this->buildService([]);

        $service->quickAddMilestone(['headline' => 'Getting Started', 'projectId' => 7, 'origin' => 'onboarding_seed']);
        $service->quickAddMilestone(['headline' => 'Q4 launch', 'projectId' => 7]);

        $this->assertCount(2, $this->milestonesCreated);
        $this->assertSame(42, $this->milestonesCreated[0]->milestoneId);
        $this->assertSame('onboarding_seed', $this->milestonesCreated[0]->origin, 'generated milestones are tagged');
        $this->assertSame('quickadd', $this->milestonesCreated[1]->origin, 'user-made milestones default to quickadd');
        $this->assertSame(7, $this->milestonesCreated[1]->projectId);
    }
}
