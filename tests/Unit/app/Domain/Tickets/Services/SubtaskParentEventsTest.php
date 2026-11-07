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
use Leantime\Domain\Tickets\Events\SubtasksUpdated;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\TicketHistory;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Unit\TestCase;

/**
 * Editing a child ticket through the general update paths tells its parent's subtask list
 * (SubtasksUpdated → lt:tickets:subtask.updated#{parentId}). Reparenting tells both parents (#3951).
 */
class SubtaskParentEventsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, int> Parent ticket ids of the SubtasksUpdated events dispatched. */
    private array $notifiedParents = [];

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

        $this->notifiedParents = [];
        EventDispatcher::add_event_listener(SubtasksUpdated::class, function (SubtasksUpdated $event) {
            $this->notifiedParents[] = $event->parentTicketId;
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

    public function test_patching_a_subtask_tells_its_parent(): void
    {
        // The dashboard to-do widget edits titles and due dates through patchTicket().
        $this->buildService([5 => 4])->patchTicket(5, ['headline' => 'Renamed']);

        $this->assertSame([4], $this->notifiedParents);
    }

    public function test_patching_a_ticket_without_parent_tells_nobody(): void
    {
        $this->buildService([5 => 0])->patch(5, ['headline' => 'Renamed']);

        $this->assertSame([], $this->notifiedParents);
    }

    public function test_reparenting_through_patch_tells_the_old_and_the_new_parent(): void
    {
        $this->buildService([5 => 4])->patch(5, ['dependingTicketId' => 9]);

        $this->assertSame([4, 9], $this->notifiedParents);
    }

    public function test_detaching_a_subtask_tells_the_parent_it_left(): void
    {
        $this->buildService([5 => 4])->patch(5, ['dependingTicketId' => null]);

        $this->assertSame([4], $this->notifiedParents);
    }

    public function test_updating_a_subtask_from_its_own_modal_tells_its_parent(): void
    {
        $result = $this->buildService([5 => 4])->updateTicket(['id' => 5, 'headline' => 'Renamed']);

        $this->assertTrue($result);
        $this->assertSame([4], $this->notifiedParents);
    }

    public function test_reparenting_through_update_ticket_tells_both_parents(): void
    {
        $this->buildService([5 => 4])->updateTicket(['id' => 5, 'dependingTicketId' => 9]);

        $this->assertSame([4, 9], $this->notifiedParents);
    }

    /**
     * A service whose tickets all live in project 7, writes always succeed and the caller may
     * edit project 7.
     *
     * @param  array<int, int>  $storedParentByTicket  Ticket id => stored dependingTicketId (0 = none).
     */
    private function buildService(array $storedParentByTicket): TicketsService
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicket' => fn ($id) => $this->make(TicketModel::class, [
                'id' => (int) $id,
                'projectId' => 7,
                'headline' => 'T'.$id,
                'type' => 'subtask',
                'status' => 3,
                'dependingTicketId' => $storedParentByTicket[(int) $id] ?? 0,
            ]),
            'patchTicket' => fn () => true,
            'updateTicket' => fn () => true,
            'getStateLabels' => fn () => [
                0 => ['name' => 'Done', 'statusType' => 'DONE'],
                3 => ['name' => 'New', 'statusType' => 'NEW'],
            ],
        ]);

        $service = new TicketsService(
            language: $this->make(LanguageCore::class, ['__' => fn ($key) => $key]),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class, [
                'isUserAssignedToProject' => fn () => true,
                'getProjectRole' => fn () => '',
                'notifyProjectUsers' => fn () => null,
            ]),
            timesheetService: $this->make(TimesheetService::class, ['isClocked' => fn () => false]),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class, ['userNow' => fn () => CarbonImmutable::now('UTC')]),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class),
            projectModules: $this->allModulesOn(),
        );
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn () => true,
            'authorize' => fn () => null,
        ]));

        return $service;
    }
}
