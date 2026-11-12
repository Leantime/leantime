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
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\TicketHistory;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Unit\TestCase;

/**
 * Ticket notifications say what changed: "moved 'X' to <status>" for a status change and
 * "changed the due date of 'X' to <date>" for a due date (which used to notify nobody).
 * Everything else keeps the generic "updated the To-Do" line.
 */
class TicketUpdateNotificationsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const MESSAGES = [
        'email_notifications.todo_update_message' => "%1\$s updated the To-Do: '%2\$s'",
        'email_notifications.todo_status_message' => "%1\$s moved '%2\$s' to %3\$s",
        'email_notifications.todo_due_date_message' => "%1\$s changed the due date of '%2\$s' to %3\$s",
        'email_notifications.todo_due_date_removed_message' => "%1\$s removed the due date of '%2\$s'",
    ];

    /** @var array<int, Notification> */
    private array $sent = [];

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
        session(['userdata' => ['id' => 1, 'role' => 'editor', 'name' => 'Sam']]);

        app()->instance(EnvironmentCore::class, $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]));
        app()->instance(LanguageCore::class, $this->make(LanguageCore::class, ['__' => fn ($key) => self::MESSAGES[$key] ?? $key]));

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'M j, Y', 'H:i'));

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (self::DISPATCHER_PROPS as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }

        $this->sent = [];
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
     * Tickets live in project 7 with the given stored status and fields; writes succeed.
     * Status 0 is Done, 3 New, 4 In Progress, 6 Approved.
     *
     * @param  array<int, int>  $storedStatusByTicket  Ticket id => stored status.
     * @param  array<string, mixed>  $storedFields  Further stored fields of every ticket.
     */
    private function buildService(array $storedStatusByTicket, array $storedFields = []): TicketsService
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicket' => fn ($id) => $this->make(TicketModel::class, array_merge([
                'id' => (int) $id,
                'projectId' => 7,
                'headline' => 'Fix login',
                'status' => $storedStatusByTicket[(int) $id] ?? 3,
            ], $storedFields)),
            'patchTicket' => fn () => true,
            'updateTicket' => fn () => true,
            'updateTicketStatus' => fn () => true,
            'getStateLabels' => fn () => [
                0 => ['name' => 'Done', 'statusType' => 'DONE'],
                3 => ['name' => 'New', 'statusType' => 'NEW'],
                4 => ['name' => 'In Progress', 'statusType' => 'INPROGRESS'],
                6 => ['name' => 'Approved', 'statusType' => 'INPROGRESS'],
            ],
        ]);

        $service = new TicketsService(
            language: app()->make(LanguageCore::class),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class, [
                'isUserAssignedToProject' => fn () => true,
                'notifyProjectUsers' => function (Notification $notification) {
                    $this->sent[] = $notification;
                },
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

    /** @return array<int, string> */
    private function sentMessages(): array
    {
        return array_map(fn (Notification $n) => $n->message, $this->sent);
    }

    public function test_a_status_change_says_where_the_task_went(): void
    {
        $this->buildService([5 => 3])->patch(5, ['status' => 6]);

        $this->assertSame(["Sam moved 'Fix login' to Approved"], $this->sentMessages());
        $this->assertSame('status_changed', $this->sent[0]->action);
        $this->assertSame('tickets', $this->sent[0]->module);
    }

    public function test_a_resaved_status_keeps_the_generic_line(): void
    {
        $this->buildService([5 => 4])->patch(5, ['status' => 4]);

        $this->assertSame(["Sam updated the To-Do: 'Fix login'"], $this->sentMessages());
    }

    public function test_a_due_date_change_notifies_with_the_new_date(): void
    {
        $this->buildService([5 => 3], ['dateToFinish' => '2026-11-01 00:00:00'])->patch(5, ['dateToFinish' => '2026-11-20']);

        $this->assertSame(["Sam changed the due date of 'Fix login' to Nov 20, 2026"], $this->sentMessages());
        $this->assertSame('due_date_changed', $this->sent[0]->action);
    }

    public function test_setting_a_first_due_date_notifies(): void
    {
        $this->buildService([5 => 3], ['dateToFinish' => null])->patch(5, ['dateToFinish' => '2026-11-20']);

        $this->assertSame(["Sam changed the due date of 'Fix login' to Nov 20, 2026"], $this->sentMessages());
    }

    public function test_removing_the_due_date_says_so(): void
    {
        $this->buildService([5 => 3], ['dateToFinish' => '2026-11-01 00:00:00'])->patch(5, ['dateToFinish' => '']);

        $this->assertSame(["Sam removed the due date of 'Fix login'"], $this->sentMessages());
    }

    public function test_an_unchanged_due_date_or_other_field_notifies_nobody(): void
    {
        $service = $this->buildService([5 => 3], ['dateToFinish' => '2026-11-20 00:00:00']);

        $service->patch(5, ['dateToFinish' => '2026-11-20']);
        $service->patch(5, ['headline' => 'Renamed']);

        $this->assertSame([], $this->sent);
    }

    public function test_status_and_due_date_together_send_one_status_line(): void
    {
        $this->buildService([5 => 3], ['dateToFinish' => null])->patch(5, ['status' => 0, 'dateToFinish' => '2026-11-20']);

        $this->assertSame(["Sam moved 'Fix login' to Done"], $this->sentMessages());
    }

    public function test_dragging_a_card_to_another_column_says_where_it_went(): void
    {
        $this->buildService([5 => 3])->updateTicketStatusAndSorting(['4' => 'ticket[]=5'], 'ticket_5');

        $this->assertSame(["Sam moved 'Fix login' to In Progress"], $this->sentMessages());
    }

    public function test_re_sorting_a_card_in_its_column_keeps_the_generic_line(): void
    {
        $this->buildService([5 => 4])->updateTicketStatusAndSorting(['4' => 'ticket[]=5'], 'ticket_5');

        $this->assertSame(["Sam updated the To-Do: 'Fix login'"], $this->sentMessages());
    }

    public function test_the_full_edit_names_a_status_change(): void
    {
        $this->buildService([5 => 3])->updateTicket(['id' => 5, 'status' => 6]);

        $this->assertSame(["Sam moved 'Fix login' to Approved"], $this->sentMessages());
    }

    public function test_the_full_edit_names_a_due_date_change(): void
    {
        $this->buildService([5 => 3], ['dateToFinish' => '2026-11-01 00:00:00'])->updateTicket(['id' => 5, 'dateToFinish' => '2026-11-20']);

        $this->assertSame(["Sam changed the due date of 'Fix login' to Nov 20, 2026"], $this->sentMessages());
    }

    public function test_the_full_edit_of_other_fields_keeps_the_generic_line(): void
    {
        $this->buildService([5 => 3], ['dateToFinish' => '2026-11-20 00:00:00'])->updateTicket(['id' => 5, 'headline' => 'Fix login']);

        $this->assertSame(["Sam updated the To-Do: 'Fix login'"], $this->sentMessages());
    }
}
