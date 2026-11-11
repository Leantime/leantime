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
use Leantime\Domain\Tickets\Events\TicketDeleted;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\TicketHistory;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Unit\TestCase;

/**
 * Deleting a To-Do can take its subtasks with it (#2153): every subtask, including the subtasks of
 * subtasks, is deleted before the To-Do itself, and nothing is deleted when the user may not
 * delete one of them or time was logged on one of them.
 */
class DeleteSubtasksTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const DISPATCHER_PROPS = [
        'eventRegistry',
        'filterRegistry',
        'available_hooks',
        'patternMatchCache',
        'compiledPatternCache',
        'eventRegistryVersion',
        'filterRegistryVersion',
    ];

    /** Ticket ids in the order the repository deleted them. */
    private array $deleted = [];

    /** @var array<int, TicketDeleted> */
    private array $deletedEvents = [];

    private array $dispatcherSnapshot = [];

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

        $this->deleted = [];
        $this->deletedEvents = [];
        EventDispatcher::add_event_listener(TicketDeleted::class, function (TicketDeleted $event) {
            $this->deletedEvents[] = $event;
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
     * To-Do 5 (project 7) has subtasks 6 and 7; subtask 6 has the subtask 8.
     *
     * @param  array<int, int>  $deletableProjects  Projects the caller may delete in.
     * @param  array<int, int>  $ticketsWithTimesheets  Tickets that have logged time.
     * @param  array<int, array<int, array<string, mixed>>>|null  $children  Subtask rows by parent id.
     */
    private function buildService(array $deletableProjects = [7], array $ticketsWithTimesheets = [], ?array $children = null): TicketsService
    {
        $children ??= [
            5 => [['id' => 6, 'projectId' => 7, 'type' => 'subtask'], ['id' => 7, 'projectId' => 7, 'type' => 'subtask']],
            6 => [['id' => 8, 'projectId' => 7, 'type' => 'subtask']],
        ];

        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicket' => fn ($id) => $this->make(TicketModel::class, ['id' => (int) $id, 'projectId' => 7, 'type' => 'task', 'dependingTicketId' => '']),
            'getSubtasksByParent' => fn (int $parentId) => $children[$parentId] ?? [],
            'delticket' => function ($id) {
                $this->deleted[] = (int) $id;

                return true;
            },
        ]);

        $service = new TicketsService(
            language: $this->make(LanguageCore::class, ['__' => fn ($key) => $key]),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class, [
                'getTimesheetsByTicket' => fn ($id) => in_array((int) $id, $ticketsWithTimesheets, true) ? [['id' => 1]] : [],
            ]),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class, ['isUserAssignedToProject' => fn () => true]),
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class, ['userNow' => fn () => CarbonImmutable::now('UTC')]),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class),
            projectModules: $this->allModulesOn(),
        );
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn (string $key, ?int $projectId = null) => in_array($projectId, $deletableProjects, true),
            'authorize' => fn () => null,
        ]));

        return $service;
    }

    public function test_deleting_without_the_option_leaves_the_subtasks_alone(): void
    {
        $this->assertTrue($this->buildService()->delete(5));

        $this->assertSame([5], $this->deleted);
    }

    public function test_deleting_with_the_option_deletes_every_subtask_before_the_todo(): void
    {
        $this->assertTrue($this->buildService()->delete(5, true));

        $this->assertSame([8, 6, 7, 5], $this->deleted);
        $this->assertSame([8, 6, 7, 5], array_map(fn (TicketDeleted $event) => $event->ticketId, $this->deletedEvents));
    }

    public function test_a_todo_without_subtasks_is_deleted_with_the_option_too(): void
    {
        $this->assertTrue($this->buildService([7], [], [])->delete(5, true));

        $this->assertSame([5], $this->deleted);
    }

    public function test_nothing_is_deleted_when_a_subtask_is_in_a_project_the_user_may_not_delete_in(): void
    {
        $service = $this->buildService([7], [], [
            5 => [['id' => 6, 'projectId' => 7, 'type' => 'subtask'], ['id' => 7, 'projectId' => 9, 'type' => 'subtask']],
        ]);

        $result = $service->delete(5, true);

        $this->assertSame('notifications.ticket_delete_subtasks_error', $result['msg']);
        $this->assertSame([], $this->deleted);
        $this->assertSame([], $this->deletedEvents);
    }

    public function test_nothing_is_deleted_when_a_subtask_has_logged_time(): void
    {
        $result = $this->buildService([7], [8])->delete(5, true);

        $this->assertSame('notifications.ticket_delete_subtasks_error', $result['msg']);
        $this->assertSame([], $this->deleted);
    }

    public function test_milestones_are_not_treated_as_subtasks(): void
    {
        $service = $this->buildService([7], [], [
            5 => [['id' => 6, 'projectId' => 7, 'type' => 'subtask'], ['id' => 20, 'projectId' => 7, 'type' => 'milestone']],
        ]);

        $this->assertTrue($service->delete(5, true));

        $this->assertSame([6, 5], $this->deleted);
    }

    public function test_subtasks_are_counted_including_their_own_subtasks(): void
    {
        $this->assertSame(3, $this->buildService()->countSubtasks(5));
        $this->assertSame(1, $this->buildService()->countSubtasks(6));
        $this->assertSame(0, $this->buildService()->countSubtasks(7));
    }

    public function test_circular_parent_links_do_not_loop(): void
    {
        $service = $this->buildService([7], [], [
            5 => [['id' => 6, 'projectId' => 7, 'type' => 'subtask']],
            6 => [['id' => 5, 'projectId' => 7, 'type' => 'subtask']],
        ]);

        $this->assertSame(1, $service->countSubtasks(5));
        $this->assertTrue($service->delete(5, true));
        $this->assertSame([6, 5], $this->deleted);
    }
}
