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
use Leantime\Domain\Tickets\Events\TicketCompleted;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\TicketHistory;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Unit\TestCase;

/**
 * TicketCompleted fires only on a persisted transition INTO a DONE-type status from a status that
 * was not DONE — never on re-saves of a done ticket or moves between non-done statuses.
 */
class TicketCompletedTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, TicketCompleted> */
    private array $completed = [];

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
        EventDispatcher::add_event_listener(TicketCompleted::class, function (TicketCompleted $event) {
            $this->completed[] = $event;
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
     */
    private function buildService(array $storedStatusByTicket): TicketsService
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicket' => fn ($id) => $this->make(TicketModel::class, [
                'id' => (int) $id,
                'projectId' => 7,
                'headline' => 'T'.$id,
                'status' => $storedStatusByTicket[(int) $id] ?? 3,
            ]),
            'patchTicket' => fn () => true,
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
}
