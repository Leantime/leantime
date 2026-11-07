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
 * patch() field names are matched case-insensitively when written, so every check in patch()
 * (target project, parent, events) must see them the same way, whatever case the caller used.
 */
class PatchFieldNamesTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, int> Parent ticket ids of the SubtasksUpdated events dispatched. */
    private array $notifiedParents = [];

    /** @var array<int, array<int, mixed>> The arguments of every authorize() call. */
    private array $authorizations = [];

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
        $this->authorizations = [];
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

    public function test_moving_a_ticket_checks_the_target_project_whatever_the_field_case(): void
    {
        foreach (['projectId', 'projectid', 'PROJECTID'] as $field) {
            $this->authorizations = [];

            $this->buildService([5 => 0])->patchTicket(5, [$field => 99]);

            $this->assertContains(['tickets.edit', 99, null], $this->authorizations, "field '$field'");
        }
    }

    public function test_reparenting_with_another_field_case_tells_both_parents(): void
    {
        $this->buildService([5 => 4])->patchTicket(5, ['dependingticketid' => 9]);

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
            'authorize' => function (...$arguments) {
                $this->authorizations[] = $arguments;
            },
        ]));

        return $service;
    }
}
