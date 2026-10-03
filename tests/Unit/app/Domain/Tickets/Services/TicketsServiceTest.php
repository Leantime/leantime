<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Core\UI\Template as TemplateCore;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
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

class TicketsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected TicketsService $ticketsService;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up session values needed for DateTimeHelper
        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);

        // Mock Environment and bind to container for dtHelper()
        $envMock = $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]);
        app()->instance(EnvironmentCore::class, $envMock);

        // Mock Language and bind to container
        $langMock = $this->createMock(LanguageCore::class);
        $langMock->method('__')->willReturnCallback(function ($index) {
            $map = [
                'language.dateformat' => 'Y-m-d',
                'language.timeformat' => 'H:i',
            ];

            return $map[$index] ?? $index;
        });
        app()->instance(LanguageCore::class, $langMock);

        // Register CarbonMacros for date parsing
        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));

        // Create mocks for all dependencies
        $tpl = $this->make(TemplateCore::class);
        $language = $this->make(LanguageCore::class);
        $config = $this->make(EnvironmentCore::class);
        $projectRepository = $this->make(ProjectRepository::class);
        $ticketRepository = $this->make(TicketRepository::class);
        $timesheetsRepo = $this->make(TimesheetRepository::class);
        $settingsRepo = $this->make(SettingRepository::class);
        $projectService = $this->make(ProjectService::class);
        $timesheetService = $this->make(TimesheetService::class);
        $sprintService = $this->make(SprintService::class);
        $ticketHistoryRepo = $this->make(TicketHistory::class);
        $goalcanvasService = $this->make(Goalcanvas::class);
        $dateTimeHelper = $this->make(DateTimeHelper::class);
        $commentService = $this->make(CommentService::class);
        $clientService = $this->make(ClientService::class);

        // Instantiate the service with mocked dependencies
        $this->ticketsService = new TicketsService(
            language: $language,
            ticketRepository: $ticketRepository,
            timesheetsRepo: $timesheetsRepo,
            settingsRepo: $settingsRepo,
            projectService: $projectService,
            timesheetService: $timesheetService,
            sprintService: $sprintService,
            ticketHistoryRepo: $ticketHistoryRepo,
            goalcanvasService: $goalcanvasService,
            dateTimeHelper: $dateTimeHelper,
            commentService: $commentService,
            clientService: $clientService
        );
    }

    protected function _after()
    {
        // Clear any frozen Carbon "now" so a test that freezes it (e.g. the
        // board-summary due-this-week test) can't leak into later tests.
        CarbonImmutable::setTestNow();
        $this->ticketsService = null;
    }

    /**
     * Test that timeFrom is unset when editFrom parsing fails
     */
    public function test_prepare_ticket_dates_removes_time_from_on_parse_error()
    {
        $values = [
            'editFrom' => 'Invalid DateTime',
            'timeFrom' => '12:00',
        ];

        $result = $this->ticketsService->prepareTicketDates($values);

        // Date should be cleared
        $this->assertEquals('', $result['editFrom']);

        // Time field must be removed to prevent SQL error
        $this->assertArrayNotHasKey('timeFrom', $result);
    }

    /**
     * Test that timeTo is unset when editTo parsing fails
     * This is the primary bug from issue #3139
     */
    public function test_prepare_ticket_dates_removes_time_to_on_parse_error()
    {
        $values = [
            'editTo' => 'Invalid DateTime',
            'timeTo' => '17:00',
        ];

        $result = $this->ticketsService->prepareTicketDates($values);

        $this->assertEquals('', $result['editTo']);
        $this->assertArrayNotHasKey('timeTo', $result);
    }

    /**
     * getBoardSummary should count total/unassigned/due-this-week and surface the
     * most recent modified date, working off the grouped ticket set as-is.
     */
    public function test_get_board_summary_computes_counts_and_last_updated()
    {
        // Freeze "now" to a fixed instant (noon, well clear of a midnight/week
        // boundary) so $dueToday and getBoardSummary's weekStart/weekEnd are
        // computed from the same clock — otherwise a run straddling midnight
        // could make the due-this-week assertion flaky. Cleared in _after().
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-15 12:00:00', 'UTC'));

        // getBoardSummary parses dateToFinish via parseDbDateTime() (DB tz) and
        // converts to the user tz before the "this week" compare, so the stored
        // strings must be DB-tz. Derive them from userNow()->setToDbTimezone()
        // so they round-trip user→db→user and "due today" stays stable even if
        // this test's user timezone is later moved off UTC.
        $nowUser = dtHelper()->userNow();
        $dueToday = $nowUser->setToDbTimezone()->format('Y-m-d H:i:s');
        $dueTwoMonthsAgo = $nowUser->subMonths(2)->setToDbTimezone()->format('Y-m-d H:i:s');
        $dueTwoMonthsOut = $nowUser->addMonths(2)->setToDbTimezone()->format('Y-m-d H:i:s');

        $mk = function (mixed $editorId, ?string $due, ?string $modified) {
            $ticket = new \stdClass;
            $ticket->editorId = $editorId;
            $ticket->dateToFinish = $due;
            $ticket->modified = $modified;

            return $ticket;
        };

        $grouped = [
            'all' => [
                'label' => 'all',
                'items' => [
                    // assigned, due today (this week), older change
                    $mk(5, $dueToday, '2026-07-01 10:00:00'),
                    // unassigned (empty editor), due 2 months ago (not this week), newest change
                    $mk('', $dueTwoMonthsAgo, '2026-07-15 09:00:00'),
                    // unassigned (zero editor), no due date set
                    $mk(0, '0000-00-00 00:00:00', '2026-06-01 08:00:00'),
                    // assigned, due 2 months out (beyond this week), no modified stamp
                    $mk(7, $dueTwoMonthsOut, null),
                ],
            ],
        ];

        $summary = $this->ticketsService->getBoardSummary($grouped);

        $this->assertSame(4, $summary->total);
        $this->assertSame(2, $summary->unassigned);
        $this->assertSame(1, $summary->dueThisWeek);
        $this->assertNotNull($summary->lastUpdated);
        $this->assertSame('2026-07-15 09:00:00', $summary->lastUpdated->format('Y-m-d H:i:s'));
    }

    /**
     * An empty board yields zeroed counts and a null last-updated.
     */
    public function test_get_board_summary_handles_empty_board()
    {
        $summary = $this->ticketsService->getBoardSummary(['all' => ['items' => []]]);

        $this->assertSame(0, $summary->total);
        $this->assertSame(0, $summary->unassigned);
        $this->assertSame(0, $summary->dueThisWeek);
        $this->assertNull($summary->lastUpdated);
    }

    /**
     * Sentinel date strings (0000-00-00 and 1969-12-31 — both rejected by
     * parseDbDateTime) must be skipped, not blow up the whole board summary.
     * Regression: the guard originally only filtered 0000-00-00, so a
     * 1969-12-31 stamp threw InvalidDateException and broke the header.
     */
    public function test_get_board_summary_skips_sentinel_dates_without_throwing()
    {
        $mk = function (?string $due, ?string $modified) {
            $ticket = new \stdClass;
            $ticket->editorId = 5;
            $ticket->dateToFinish = $due;
            $ticket->modified = $modified;

            return $ticket;
        };

        $grouped = [
            'all' => [
                'items' => [
                    $mk('1969-12-31 00:00:00', '1969-12-31 00:00:00'),
                    $mk('0000-00-00 00:00:00', '0000-00-00 00:00:00'),
                    // Malformed but NON-sentinel — passes isValidDateString yet
                    // parseDbDateTime throws. The try/catch must swallow it.
                    $mk('not a date', 'garbage-value'),
                    $mk(null, null),
                ],
            ],
        ];

        $summary = $this->ticketsService->getBoardSummary($grouped);

        $this->assertSame(4, $summary->total);
        // No valid due dates → none counted this week; no valid modified → null.
        $this->assertSame(0, $summary->dueThisWeek);
        $this->assertNull($summary->lastUpdated);
    }

    /**
     * Test that timeToFinish is unset when dateToFinish parsing fails
     */
    public function test_prepare_ticket_dates_removes_time_to_finish_on_parse_error()
    {
        $values = [
            'dateToFinish' => 'Invalid DateTime',
            'timeToFinish' => '23:59',
        ];

        $result = $this->ticketsService->prepareTicketDates($values);

        $this->assertEquals('', $result['dateToFinish']);
        $this->assertArrayNotHasKey('timeToFinish', $result);
    }

    /**
     * Test that valid dates work correctly and time fields are removed
     */
    public function test_prepare_ticket_dates_successfully_parses_valid_dates()
    {
        $values = [
            'editFrom' => '2025-11-30',
            'timeFrom' => '09:00',
            'editTo' => '2025-11-30',
            'timeTo' => '17:00',
        ];

        $result = $this->ticketsService->prepareTicketDates($values);

        // Dates should be formatted for DB (not empty)
        $this->assertNotEmpty($result['editFrom']);
        $this->assertNotEmpty($result['editTo']);

        // Time fields should be removed after successful parsing
        $this->assertArrayNotHasKey('timeFrom', $result);
        $this->assertArrayNotHasKey('timeTo', $result);
    }

    /**
     * normalizeRoadmapParams defaults the type to milestone when not provided.
     */
    public function test_normalize_roadmap_params_defaults_type_to_milestone()
    {
        $result = $this->ticketsService->normalizeRoadmapParams([]);

        $this->assertEquals('milestone', $result['type']);
        $this->assertArrayNotHasKey('excludeType', $result);
    }

    /**
     * normalizeRoadmapParams keeps an explicitly provided type.
     */
    public function test_normalize_roadmap_params_keeps_provided_type()
    {
        $result = $this->ticketsService->normalizeRoadmapParams(['type' => 'task']);

        $this->assertEquals('task', $result['type']);
    }

    /**
     * normalizeRoadmapParams clears type and excludeType when showing tasks.
     */
    public function test_normalize_roadmap_params_clears_filters_when_showing_tasks()
    {
        $result = $this->ticketsService->normalizeRoadmapParams(['showTasks' => 'true']);

        $this->assertEquals('', $result['type']);
        $this->assertEquals('', $result['excludeType']);
    }

    /**
     * getMilestonesOverviewSearchCriteria defaults the status to not_done when none provided.
     */
    public function test_overview_search_criteria_defaults_status_to_not_done()
    {
        $result = $this->ticketsService->getMilestonesOverviewSearchCriteria([]);

        $this->assertEquals('not_done', $result['status']);
    }

    /**
     * getMilestonesOverviewSearchCriteria respects an explicitly selected status.
     */
    public function test_overview_search_criteria_respects_selected_status()
    {
        $result = $this->ticketsService->getMilestonesOverviewSearchCriteria(['status' => '3']);

        $this->assertEquals('3', $result['status']);
    }

    /**
     * getNewMilestone returns a default milestone with status 3 and a one-week edit window.
     */
    public function test_get_new_milestone_has_default_status_and_one_week_window()
    {
        $milestone = $this->ticketsService->getNewMilestone();

        $this->assertEquals(3, $milestone->status);

        $expectedFrom = CarbonImmutable::now()->format('Y-m-d');
        $expectedTo = CarbonImmutable::now()->addWeek()->format('Y-m-d');

        $this->assertEquals($expectedFrom, $milestone->editFrom);
        $this->assertEquals($expectedTo, $milestone->editTo);
    }

    /**
     * getClientNameById returns an empty string when no client id is given.
     */
    public function test_get_client_name_by_id_returns_empty_for_zero_id()
    {
        $this->assertEquals('', $this->ticketsService->getClientNameById(0));
    }

    /**
     * getClientNameById resolves the name from the clients service.
     */
    public function test_get_client_name_by_id_resolves_name()
    {
        $service = $this->buildServiceWithClientService(
            $this->make(ClientService::class, [
                'get' => fn () => ['id' => 5, 'name' => 'Acme Inc'],
            ])
        );

        $this->assertEquals('Acme Inc', $service->getClientNameById(5));
    }

    /**
     * getClientNameById returns an empty string when the client is not found.
     */
    public function test_get_client_name_by_id_returns_empty_when_not_found()
    {
        $service = $this->buildServiceWithClientService(
            $this->make(ClientService::class, [
                'get' => fn () => false,
            ])
        );

        $this->assertEquals('', $service->getClientNameById(99));
    }

    /**
     * Builds a TicketsService using the default mocks but with a specific
     * ClientService instance, so client-name resolution can be asserted.
     */
    private function buildServiceWithClientService(ClientService $clientService): TicketsService
    {
        return new TicketsService(
            language: $this->make(LanguageCore::class),
            ticketRepository: $this->make(TicketRepository::class),
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class),
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class),
            commentService: $this->make(CommentService::class),
            clientService: $clientService
        );
    }

    /**
     * Builds a TicketsService using the default mocks but with a specific
     * TicketRepository instance, so collaborator enrichment can be asserted.
     */
    private function buildServiceWithTicketRepository(TicketRepository $ticketRepository): TicketsService
    {
        return new TicketsService(
            language: $this->make(LanguageCore::class),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class),
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class)
        );
    }

    public function test_get_all_open_user_tickets_excludes_closed_projects_at_query_level(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'admin']]);

        // Closed-project (state === -1) exclusion lives in the SQL layer now, so
        // the service's contract is simply: ask simpleTicketQuery to exclude
        // them. Capture the flag it passes.
        $captured = null;
        $ticketRepository = $this->make(TicketRepository::class, [
            'simpleTicketQuery' => function ($userId, $projectId, $types = [], $excludeClosedProjects = false) use (&$captured) {
                $captured = $excludeClosedProjects;

                return [];
            },
        ]);

        $service = $this->buildServiceWithTicketRepository($ticketRepository);
        $service->getAllOpenUserTickets(1);

        $this->assertTrue($captured, 'getAllOpenUserTickets must exclude closed-project tickets at the query level');
    }

    // ---------------------------------------------------------------------
    // JSON-RPC authorization gates (RPC has no controller-level role gate, so
    // the @api entry methods must self-authorize).
    // ---------------------------------------------------------------------

    public function test_patch_ticket_is_denied_for_non_editor(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        // patchTicket loads the ticket, then authorizes tickets.edit against its project via
        // the permission engine. Stub getTicket so it resolves, and inject a denying engine.
        $service = $this->construct(
            TicketsService::class,
            [
                $this->make(LanguageCore::class),
                $this->make(TicketRepository::class),
                $this->make(TimesheetRepository::class),
                $this->make(SettingRepository::class),
                $this->make(ProjectService::class),
                $this->make(TimesheetService::class),
                $this->make(SprintService::class),
                $this->make(TicketHistory::class),
                $this->make(Goalcanvas::class),
                $this->make(DateTimeHelper::class),
                $this->make(CommentService::class),
                $this->make(ClientService::class),
            ],
            ['getTicket' => fn () => $this->make(TicketModel::class, ['id' => 5, 'projectId' => 9])],
        );

        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => function (): void {
                throw new AuthorizationException;
            },
        ]));

        $this->expectException(AuthorizationException::class);

        $service->patchTicket(5, ['status' => 3]);
    }

    /**
     * Builds a TicketsService whose ticket repository is stubbed, whose project service reports
     * the session user as a member of every project (so getTicket() resolves), and whose
     * permission engine is the given stub.
     *
     * @param  array<string, mixed>  $ticketRepoStubs
     */
    private function buildAuthzService(array $ticketRepoStubs, PermissionService $permissions, ?TimesheetService $timesheetService = null): TicketsService
    {
        $service = new TicketsService(
            language: $this->make(LanguageCore::class, ['__' => fn ($key) => $key]),
            ticketRepository: $this->make(TicketRepository::class, $ticketRepoStubs),
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class, [
                'isUserAssignedToProject' => fn () => true,
                'notifyProjectUsers' => fn () => null,
            ]),
            timesheetService: $timesheetService ?? $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class, ['userNow' => fn () => CarbonImmutable::now('UTC')]),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class)
        );
        $service->setPermissionService($permissions);

        return $service;
    }

    /**
     * Permission stub that grants $key only in the listed projects and records every check.
     *
     * @param  array<int, int>  $allowedProjects
     * @param  array<int, array{0: string, 1: int|null}>  $checks
     */
    private function permissionsForProjects(array $allowedProjects, array &$checks = []): PermissionService
    {
        $decide = function (string $key, ?int $projectId = null) use ($allowedProjects, &$checks): bool {
            $checks[] = [$key, $projectId];

            return in_array($projectId, $allowedProjects, true);
        };

        return $this->make(PermissionService::class, [
            'currentUserCan' => $decide,
            'authorize' => function (string $key, ?int $projectId = null) use ($decide): void {
                if (! $decide($key, $projectId)) {
                    throw new AuthorizationException;
                }
            },
        ]);
    }

    /** A ticket model in the given project. */
    private function ticketIn(int $id, int $projectId, array $extra = []): TicketModel
    {
        return $this->make(TicketModel::class, array_merge(['id' => $id, 'projectId' => $projectId, 'headline' => 'T'.$id], $extra));
    }

    public function test_sort_tickets_is_denied_for_non_editor(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        // Editor in project 9 only; ticket 5 lives in project 7 where the caller is read-only.
        $service = $this->buildAuthzService([
            'getTicket' => fn ($id) => $this->ticketIn((int) $id, 7),
            'bulkUpdateSortIndex' => function () {
                throw new \RuntimeException('must not re-sort when denied');
            },
        ], $this->permissionsForProjects([9]));

        $this->expectException(AuthorizationException::class);

        $service->sortTickets(['5' => 1]);
    }

    public function test_sort_tickets_checks_each_tickets_own_project(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor'], 'currentProject' => 9]);

        // Ticket 5 is editable (project 9), ticket 6 is in project 7 — a mixed batch must fail.
        $service = $this->buildAuthzService([
            'getTicket' => fn ($id) => $this->ticketIn((int) $id, (int) $id === 5 ? 9 : 7),
            'bulkUpdateSortIndex' => function () {
                throw new \RuntimeException('must not re-sort a batch containing a foreign ticket');
            },
        ], $this->permissionsForProjects([9]));

        $this->expectException(AuthorizationException::class);

        $service->sortTickets(['5' => 1, '6' => 2]);
    }

    public function test_status_and_sorting_is_denied_for_non_editor(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        $service = $this->buildAuthzService([
            'getTicket' => fn ($id) => $this->ticketIn((int) $id, 7),
            'updateTicketStatus' => function () {
                throw new \RuntimeException('must not change status when denied');
            },
        ], $this->permissionsForProjects([9]));

        $this->assertFalse($service->updateTicketStatusAndSorting(['3' => 'ticket[]=5'], null));
    }

    public function test_patch_requires_edit_on_the_tickets_real_project(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        $checks = [];
        $service = $this->buildAuthzService([
            'getTicket' => fn ($id) => $this->ticketIn((int) $id, 7),
            'patchTicket' => function () {
                throw new \RuntimeException('patch must not write without edit rights');
            },
        ], $this->permissionsForProjects([9], $checks));

        try {
            $service->patch(5, ['headline' => 'x']);
            $this->fail('patch() must self-authorize (MCP tools and HTMX widgets call it directly)');
        } catch (AuthorizationException) {
            $this->assertContains(['tickets.edit', 7], $checks);
        }
    }

    /**
     * Service whose ticket 5 (project 7) is patchable, with the session user's timer on $clockedTicketId.
     *
     * @param  array<int, int>  $punchedOut  Collects the ticket ids punchOut() was called with.
     */
    private function buildTimerService(int $clockedTicketId, array &$punchedOut): TicketsService
    {
        $timesheetService = $this->make(TimesheetService::class, [
            'isClocked' => fn () => ['id' => $clockedTicketId],
            'punchOut' => function (int $ticketId) use (&$punchedOut) {
                $punchedOut[] = $ticketId;

                return 1.5;
            },
        ]);

        return $this->buildAuthzService([
            'getTicket' => fn ($id) => $this->ticketIn((int) $id, 7),
            'patchTicket' => fn () => true,
            'updateTicketStatus' => fn () => true,
            'getStateLabels' => fn () => [
                0 => ['name' => 'Done', 'statusType' => 'DONE'],
                3 => ['name' => 'New', 'statusType' => 'NEW'],
                4 => ['name' => 'In Progress', 'statusType' => 'INPROGRESS'],
            ],
        ], $this->permissionsForProjects([7]), $timesheetService);
    }

    public function test_moving_a_ticket_to_done_stops_the_users_timer_on_it(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor', 'name' => 'Caller']]);
        $punchedOut = [];

        $this->assertTrue($this->buildTimerService(5, $punchedOut)->patch(5, ['status' => 0]));

        $this->assertSame([5], $punchedOut, 'a DONE status must stop the timer running on the ticket (#415)');
    }

    public function test_a_non_done_status_keeps_the_timer_running(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor', 'name' => 'Caller']]);
        $punchedOut = [];

        $this->buildTimerService(5, $punchedOut)->patch(5, ['status' => 4]);

        $this->assertSame([], $punchedOut);
    }

    public function test_done_on_another_ticket_keeps_the_timer_running(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor', 'name' => 'Caller']]);
        $punchedOut = [];

        $this->buildTimerService(99, $punchedOut)->patch(5, ['status' => 0]);

        $this->assertSame([], $punchedOut, 'only a timer on the completed ticket is stopped');
    }

    public function test_kanban_batch_without_handler_stops_the_timer_of_a_ticket_moved_to_done(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor', 'name' => 'Caller']]);
        $punchedOut = [];

        $this->assertTrue($this->buildTimerService(5, $punchedOut)->updateTicketStatusAndSorting(['4' => 'ticket[]=6', '0' => 'ticket[]=5'], null));

        $this->assertSame([5], $punchedOut, 'every ticket in the batch counts, not only the optional handler');
    }

    public function test_kanban_batch_failing_later_still_stops_the_timer_of_a_persisted_done_ticket(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor', 'name' => 'Caller']]);
        $punchedOut = [];

        $timesheetService = $this->make(TimesheetService::class, [
            'isClocked' => fn () => ['id' => 5],
            'punchOut' => function (int $ticketId) use (&$punchedOut) {
                $punchedOut[] = $ticketId;

                return 1.0;
            },
        ]);
        $service = $this->buildAuthzService([
            'getTicket' => fn ($id) => $this->ticketIn((int) $id, 7),
            // Ticket 5 is written; ticket 6 reports false (e.g. 0 rows changed).
            'updateTicketStatus' => fn ($id) => (int) $id === 5,
            'getStateLabels' => fn () => [0 => ['name' => 'Done', 'statusType' => 'DONE']],
        ], $this->permissionsForProjects([7]), $timesheetService);

        $this->assertFalse($service->updateTicketStatusAndSorting(['0' => 'ticket[]=5&ticket[]=6'], null));

        $this->assertSame([5], $punchedOut, 'a status that was persisted before the failure still stops the timer');
    }

    public function test_kanban_batch_does_not_stop_the_timer_of_a_ticket_that_was_already_done(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor', 'name' => 'Caller']]);
        $punchedOut = [];

        $timesheetService = $this->make(TimesheetService::class, [
            'isClocked' => fn () => ['id' => 5],
            'punchOut' => function (int $ticketId) use (&$punchedOut) {
                $punchedOut[] = $ticketId;

                return 1.0;
            },
        ]);
        $service = $this->buildAuthzService([
            // Ticket 5 already sits in Done; ticket 6 is the card being dragged into Done.
            'getTicket' => fn ($id) => $this->ticketIn((int) $id, 7, ['status' => (int) $id === 5 ? 0 : 4]),
            'updateTicketStatus' => fn () => true,
            'getStateLabels' => fn () => [0 => ['name' => 'Done', 'statusType' => 'DONE'], 4 => ['name' => 'Doing', 'statusType' => 'INPROGRESS']],
        ], $this->permissionsForProjects([7]), $timesheetService);

        $this->assertTrue($service->updateTicketStatusAndSorting(['0' => 'ticket[]=5&ticket[]=6'], 'ticket_6'));

        $this->assertSame([], $punchedOut, 're-sorting a ticket that was already Done must not stop its timer');
    }

    public function test_upsert_subtask_reloads_the_parent_and_ignores_a_forged_project(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $checks = [];
        $service = $this->buildAuthzService([
            // The real parent (id 5) lives in project 7.
            'getTicket' => fn ($id) => $this->ticketIn((int) $id, 7),
            'addTicket' => function () {
                throw new \RuntimeException('must not create a subtask when denied');
            },
        ], $this->permissionsForProjects([9], $checks));

        try {
            // The caller claims the parent belongs to project 9 (where they are an editor).
            $service->upsertSubtask(['headline' => 'Sub', 'status' => 3], ['id' => 5, 'projectId' => 9]);
            $this->fail('upsertSubtask must authorize the parent\'s real project');
        } catch (AuthorizationException) {
            $this->assertContains(['tickets.create', 7], $checks);
            $this->assertNotContains(['tickets.create', 9], $checks);
        }
    }

    public function test_upsert_subtask_update_refuses_a_ticket_that_is_not_a_child_of_the_parent(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $service = $this->buildAuthzService([
            'getTicket' => fn ($id) => match ((int) $id) {
                5 => $this->ticketIn(5, 9),
                // Ticket 77 is an unrelated task in another project.
                77 => $this->ticketIn(77, 4, ['dependingTicketId' => 0]),
                default => false,
            },
            'updateTicket' => function () {
                throw new \RuntimeException('must not overwrite a ticket that is not this parent\'s subtask');
            },
        ], $this->permissionsForProjects([9]));

        $this->assertFalse($service->upsertSubtask(
            ['headline' => 'Pwn', 'status' => 3, 'subtaskId' => 77],
            $this->ticketIn(5, 9)
        ));
    }

    public function test_upsert_subtask_update_writes_a_real_child(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $updatedId = null;
        $service = $this->buildAuthzService([
            'getTicket' => fn ($id) => match ((int) $id) {
                5 => $this->ticketIn(5, 9),
                12 => $this->ticketIn(12, 9, ['dependingTicketId' => 5]),
                default => false,
            },
            'updateTicket' => function ($values, $id) use (&$updatedId) {
                $updatedId = $id;

                return true;
            },
        ], $this->permissionsForProjects([9]));

        $this->assertTrue($service->upsertSubtask(['headline' => 'Sub', 'status' => 3, 'subtaskId' => '12'], $this->ticketIn(5, 9)));
        $this->assertSame(12, $updatedId);
    }

    public function test_get_all_possible_parents_is_empty_for_a_project_the_caller_cannot_view(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor'], 'currentProject' => 9]);

        $service = $this->buildAuthzService([
            'getAllPossibleParents' => function () {
                throw new \RuntimeException('must not query a project the caller cannot view');
            },
        ], $this->permissionsForProjects([9]));

        $this->assertSame([], $service->getAllPossibleParents($this->ticketIn(1, 9), '7'));
        // Project 0 used to mean "every project" in the repository — never reachable now.
        $this->assertSame([], $service->getAllPossibleParents($this->ticketIn(1, 9), '0'));
    }

    public function test_prepare_ticket_search_array_ignores_caller_supplied_membership_scope(): void
    {
        session(['userdata' => ['id' => 1, 'clientId' => 3], 'currentProject' => 9]);

        $criteria = $this->ticketsService->prepareTicketSearchArray(['currentUser' => 2, 'currentClient' => 8]);

        $this->assertSame(1, $criteria['currentUser']);
        $this->assertSame(3, $criteria['currentClient']);
    }

    public function test_program_board_requires_view_on_the_program(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $service = $this->buildAuthzService([], $this->permissionsForProjects([9]));

        $this->expectException(AuthorizationException::class);

        $service->getProgramTicketTemplateAssignments([], 50, [9], []);
    }

    public function test_get_milestone_is_false_for_a_project_the_caller_cannot_view(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $service = $this->buildAuthzService([
            'getTicket' => fn ($id) => $this->ticketIn((int) $id, 7, ['type' => 'milestone']),
        ], $this->permissionsForProjects([9]));

        $this->assertFalse($service->getMilestone(5));
    }

    public function test_quick_add_ticket_is_denied_without_create_permission(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        // quickAddTicket resolves the project from its params, then authorizes tickets.create
        // through the engine before doing any work. This was one of the RPC holes: any
        // authenticated caller could create tickets. A denying engine must make it throw.
        $this->ticketsService->setPermissionService($this->make(PermissionService::class, [
            'authorize' => function (): void {
                throw new AuthorizationException;
            },
        ]));

        $this->expectException(AuthorizationException::class);

        $this->ticketsService->quickAddTicket(['headline' => 'New task', 'projectId' => 9]);
    }

    // ---------------------------------------------------------------------
    // Collaborator enrichment for grouped ticket views (list/kanban + widget)
    // ---------------------------------------------------------------------

    /**
     * enrichGroupedTicketsWithCollaborators adds metadata to 'items' groups (list/kanban views).
     */
    public function test_enrich_grouped_tickets_with_collaborators_items_key()
    {
        $service = $this->buildServiceWithTicketRepository($this->make(TicketRepository::class, [
            'getCollaboratorsByTicketIds' => fn ($ids) => [
                10 => [100, 200],
                11 => [300],
            ],
        ]));

        $groupedTickets = [
            'group1' => [
                'items' => [
                    ['id' => 10, 'editorId' => 100, 'headline' => 'Task A'],
                    ['id' => 11, 'editorId' => 0, 'headline' => 'Task B'],
                ],
            ],
        ];

        $method = new \ReflectionMethod($service, 'enrichGroupedTicketsWithCollaborators');
        $method->setAccessible(true);
        $result = $method->invoke($service, $groupedTickets);

        // Ticket 10: editorId=100 is excluded from collaborator list, leaving only [200]
        $this->assertEquals([200], $result['group1']['items'][0]['collaborators']);
        $this->assertEquals([200], $result['group1']['items'][0]['collaboratorPreview']);
        $this->assertEquals(1, $result['group1']['items'][0]['collaboratorCount']);
        $this->assertEquals(0, $result['group1']['items'][0]['collaboratorOverflow']);

        // Ticket 11: no editorId filter, so [300] stays
        $this->assertEquals([300], $result['group1']['items'][1]['collaborators']);
        $this->assertEquals(1, $result['group1']['items'][1]['collaboratorCount']);
    }

    /**
     * enrichGroupedTicketsWithCollaborators supports the 'tickets' key (ToDoWidget views).
     */
    public function test_enrich_grouped_tickets_with_collaborators_tickets_key()
    {
        $service = $this->buildServiceWithTicketRepository($this->make(TicketRepository::class, [
            'getCollaboratorsByTicketIds' => fn ($ids) => [
                20 => [400, 500, 600],
            ],
        ]));

        $groupedTickets = [
            'thisWeek' => [
                'labelName' => 'subtitles.due_this_week',
                'tickets' => [
                    ['id' => 20, 'editorId' => 400, 'headline' => 'Widget Task'],
                ],
            ],
        ];

        $method = new \ReflectionMethod($service, 'enrichGroupedTicketsWithCollaborators');
        $method->setAccessible(true);
        $result = $method->invoke($service, $groupedTickets);

        // editorId=400 excluded, leaving [500, 600]
        $this->assertEquals([500, 600], $result['thisWeek']['tickets'][0]['collaborators']);
        $this->assertEquals([500, 600], $result['thisWeek']['tickets'][0]['collaboratorPreview']);
        $this->assertEquals(2, $result['thisWeek']['tickets'][0]['collaboratorCount']);
        $this->assertEquals(0, $result['thisWeek']['tickets'][0]['collaboratorOverflow']);
    }

    /**
     * enrichGroupedTicketsWithCollaborators reports overflow when more than 2 collaborators exist.
     */
    public function test_enrich_grouped_tickets_collaborator_overflow()
    {
        $service = $this->buildServiceWithTicketRepository($this->make(TicketRepository::class, [
            'getCollaboratorsByTicketIds' => fn ($ids) => [
                30 => [101, 102, 103, 104, 105],
            ],
        ]));

        $groupedTickets = [
            'group1' => [
                'items' => [
                    ['id' => 30, 'editorId' => 0, 'headline' => 'Many collaborators'],
                ],
            ],
        ];

        $method = new \ReflectionMethod($service, 'enrichGroupedTicketsWithCollaborators');
        $method->setAccessible(true);
        $result = $method->invoke($service, $groupedTickets);

        $this->assertEquals([101, 102, 103, 104, 105], $result['group1']['items'][0]['collaborators']);
        $this->assertEquals([101, 102], $result['group1']['items'][0]['collaboratorPreview']);
        $this->assertEquals(5, $result['group1']['items'][0]['collaboratorCount']);
        $this->assertEquals(3, $result['group1']['items'][0]['collaboratorOverflow']);
    }

    /**
     * getAllMilestones() accepts a projects-only criteria array (program/cross-project boards):
     * it must query the repository and must not warn on the absent 'currentProject' key.
     */
    public function test_get_all_milestones_scopes_by_projects_without_current_project()
    {
        $captured = null;
        $service = $this->buildServiceWithTicketRepository($this->make(TicketRepository::class, [
            'getAllMilestones' => function ($searchCriteria, $sortBy) use (&$captured) {
                $captured = $searchCriteria;

                return [];
            },
        ]));

        // Projects-only criteria — no 'currentProject' key at all (the program board shape).
        $result = $service->getAllMilestones(['type' => 'milestone', 'projects' => '5,7']);

        $this->assertIsArray($result);
        $this->assertNotNull($captured, 'repository getAllMilestones should be queried for a projects-only scope');
        $this->assertSame('5,7', $captured['projects']);
        $this->assertArrayNotHasKey('currentProject', $captured);
    }

    /**
     * getAllMilestones() returns an empty array and does NOT query the repository when the
     * criteria are not project-scoped (neither a currentProject id nor a projects set).
     */
    public function test_get_all_milestones_unscoped_returns_empty_and_skips_repository()
    {
        $called = false;
        $service = $this->buildServiceWithTicketRepository($this->make(TicketRepository::class, [
            'getAllMilestones' => function () use (&$called) {
                $called = true;

                return [];
            },
        ]));

        $result = $service->getAllMilestones(['type' => 'milestone']);

        $this->assertSame([], $result);
        $this->assertFalse($called, 'repository should not be queried when criteria are not project-scoped');
    }

    /**
     * getMyClosedTicketsForRange: a reversed range is normalized (earlier date
     * first), only status changes INTO the ticket's current DONE status count,
     * and a ticket completed more than once keeps its latest completion.
     */
    public function test_closed_tickets_range_normalizes_swapped_range_and_keeps_latest_completion(): void
    {
        session(['userdata' => ['id' => 1]]);
        $capturedFrom = null;
        $capturedTo = null;

        $ticketRepository = $this->make(TicketRepository::class, [
            'simpleTicketQuery' => fn (...$args) => [
                ['id' => 10, 'type' => 'task', 'projectId' => 5, 'status' => 0],
                ['id' => 20, 'type' => 'task', 'projectId' => 5, 'status' => 0],
            ],
            'getStateLabels' => fn (...$args) => [
                0 => ['statusType' => 'DONE', 'name' => 'Done', 'class' => ''],
                3 => ['statusType' => 'INPROGRESS', 'name' => 'In Progress', 'class' => ''],
            ],
            'getStatusChangeEvents' => function ($ids, $from, $to) use (&$capturedFrom, &$capturedTo) {
                $capturedFrom = $from;
                $capturedTo = $to;

                return [
                    ['ticketId' => 10, 'changeValue' => 0, 'dateModified' => '2026-07-10 10:00:00'],
                    ['ticketId' => 10, 'changeValue' => 0, 'dateModified' => '2026-07-09 09:00:00'],
                    ['ticketId' => 20, 'changeValue' => 3, 'dateModified' => '2026-07-10 10:00:00'],
                ];
            },
        ]);

        $service = $this->buildServiceWithTicketRepository($ticketRepository);

        // Reversed range on purpose.
        $result = $service->getMyClosedTicketsForRange(1, '2026-07-12', '2026-07-05');

        $this->assertEquals('2026-07-05', $capturedFrom, 'range should be normalized earliest-first');
        $this->assertEquals('2026-07-12', $capturedTo);

        // 20's only event was a change to a non-DONE status → excluded. 10 kept
        // to its latest completion (newest event wins).
        $this->assertCount(1, $result);
        $this->assertEquals(10, $result[0]['id']);
        $this->assertEquals('2026-07-10 10:00:00', $result[0]['dateClosed']);
    }

    public function test_closed_tickets_range_forces_session_user_for_non_admin(): void
    {
        // Non-admin session user (no admin role granted).
        session(['userdata' => ['id' => 1]]);
        $capturedUserId = 'unset';

        $ticketRepository = $this->make(TicketRepository::class, [
            'simpleTicketQuery' => function (...$args) use (&$capturedUserId) {
                $capturedUserId = $args[0] ?? null;

                return []; // no done tickets — the asserted-on value is the userId
            },
            'getStateLabels' => fn (...$args) => [],
        ]);

        $service = $this->buildServiceWithTicketRepository($ticketRepository);

        // Caller supplies SOMEONE ELSE's id — the IDOR guard must force it back
        // to the session user before any query runs.
        $service->getMyClosedTicketsForRange(999, '2026-07-01', '2026-07-10');

        $this->assertSame(1, $capturedUserId, 'a non-admin must not read another user\'s closures — userId is forced to the session user');
    }

    /**
     * Builds a service with a specific ticket repository AND project service —
     * the two deps getMyCommentedTicketsForRange exercises.
     */
    private function buildServiceWithTicketRepoAndProjectService(
        TicketRepository $ticketRepository,
        ProjectService $projectService
    ): TicketsService {
        return new TicketsService(
            language: $this->make(LanguageCore::class),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $projectService,
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class)
        );
    }

    /**
     * Supported = tickets you commented on within accessible projects, minus
     * the ones you're the editor of. Editor-owned tickets are dropped; tickets
     * outside the project-scoped fetch never appear.
     */
    public function test_commented_tickets_range_excludes_owned_and_scopes_by_projects(): void
    {
        session(['userdata' => ['id' => 1]]);

        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicketIdsCommentedByUser' => fn (...$args) => [10, 20, 30],
            // Project-scoped fetch only returns 10 + 20 (30 is outside access).
            'getTicketsByIdsWithinProjects' => fn (...$args) => [
                ['id' => 10, 'headline' => 'A', 'editorId' => '99', 'projectId' => 5, 'projectName' => 'P'],
                ['id' => 20, 'headline' => 'B', 'editorId' => '1', 'projectId' => 5, 'projectName' => 'P'],
            ],
        ]);
        $projectService = $this->make(ProjectService::class, [
            'getProjectsUserHasAccessTo' => fn (...$args) => [['id' => 5], ['id' => 7]],
        ]);

        $service = $this->buildServiceWithTicketRepoAndProjectService($ticketRepository, $projectService);

        $result = $service->getMyCommentedTicketsForRange(1, '2026-07-01', '2026-07-07');

        // 20 is the user's own (editorId === 1) → excluded; 30 wasn't returned
        // by the project-scoped fetch → absent. Only 10 remains.
        $this->assertCount(1, $result);
        $this->assertEquals(10, $result[0]['id']);
    }

    /**
     * No accessible projects → empty, without ever fetching tickets.
     */
    public function test_commented_tickets_range_empty_without_project_access(): void
    {
        session(['userdata' => ['id' => 1]]);

        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicketIdsCommentedByUser' => fn (...$args) => [10],
            'getTicketsByIdsWithinProjects' => fn (...$args) => [['id' => 10, 'editorId' => '99']],
        ]);
        $projectService = $this->make(ProjectService::class, [
            'getProjectsUserHasAccessTo' => fn (...$args) => false,
        ]);

        $service = $this->buildServiceWithTicketRepoAndProjectService($ticketRepository, $projectService);

        $this->assertSame([], $service->getMyCommentedTicketsForRange(1, '2026-07-01', '2026-07-07'));
    }

    public function test_commented_tickets_range_forces_session_user_for_non_admin(): void
    {
        session(['userdata' => ['id' => 1]]);
        $capturedUserId = 'unset';

        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicketIdsCommentedByUser' => function (...$args) use (&$capturedUserId) {
                $capturedUserId = $args[0] ?? null;

                return [];
            },
        ]);
        $projectService = $this->make(ProjectService::class, [
            'getProjectsUserHasAccessTo' => fn (...$args) => [['id' => 5]],
        ]);

        $service = $this->buildServiceWithTicketRepoAndProjectService($ticketRepository, $projectService);

        // Non-admin supplies someone else's id — forced back to the session user.
        $service->getMyCommentedTicketsForRange(999, '2026-07-01', '2026-07-07');

        $this->assertSame(1, $capturedUserId, 'a non-admin must not read another user\'s comment activity — userId forced to session user');
    }

    public function test_commented_tickets_range_normalizes_reversed_range(): void
    {
        session(['userdata' => ['id' => 1]]);
        $capturedFrom = null;
        $capturedTo = null;

        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicketIdsCommentedByUser' => function (...$args) use (&$capturedFrom, &$capturedTo) {
                $capturedFrom = $args[1] ?? null;
                $capturedTo = $args[2] ?? null;

                return [];
            },
        ]);
        $projectService = $this->make(ProjectService::class, [
            'getProjectsUserHasAccessTo' => fn (...$args) => [['id' => 5]],
        ]);

        $service = $this->buildServiceWithTicketRepoAndProjectService($ticketRepository, $projectService);

        // Reversed on purpose — must be swapped earliest-first before the query.
        $service->getMyCommentedTicketsForRange(1, '2026-07-12', '2026-07-05');

        $this->assertSame('2026-07-05', $capturedFrom, 'range normalized earliest-first');
        $this->assertSame('2026-07-12', $capturedTo);
    }

    public function test_commented_tickets_range_short_circuits_when_no_comments(): void
    {
        session(['userdata' => ['id' => 1]]);
        $fetchCalled = false;

        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicketIdsCommentedByUser' => fn (...$args) => [], // nothing commented
            'getTicketsByIdsWithinProjects' => function (...$args) use (&$fetchCalled) {
                $fetchCalled = true;

                return [];
            },
        ]);
        $projectService = $this->make(ProjectService::class, [
            'getProjectsUserHasAccessTo' => fn (...$args) => [['id' => 5]],
        ]);

        $service = $this->buildServiceWithTicketRepoAndProjectService($ticketRepository, $projectService);

        $result = $service->getMyCommentedTicketsForRange(1, '2026-07-01', '2026-07-07');

        $this->assertSame([], $result);
        $this->assertFalse($fetchCalled, 'an empty commented set must short-circuit before the ticket fetch');
    }

    /**
     * dateToFinish is stored in UTC as the user's end-of-day. A task due on 17 March in
     * Los Angeles is stored as 2026-03-18 06:59:59 UTC; on the 18th (LA) it is overdue. Parsing
     * the stored value in the process timezone put it on the 18th, so it showed as "due today".
     */
    public function test_due_date_bucket_uses_the_users_calendar_day(): void
    {
        $bucket = new \ReflectionMethod($this->ticketsService, 'getDueDateBucket');
        $bucket->setAccessible(true);

        $todayLa = \Carbon\CarbonImmutable::parse('2026-03-18 09:00:00', 'America/Los_Angeles')->startOfDay();

        $this->assertSame('overdue', $bucket->invoke($this->ticketsService, '2026-03-18 06:59:59', $todayLa));
        $this->assertSame('due-this-week', $bucket->invoke($this->ticketsService, '2026-03-19 06:59:59', $todayLa));
        // Due 24 March (LA) = 2026-03-25 06:59:59 UTC: six days out, so still this week. Read on the
        // UTC calendar it landed 6.7 days out and was bucketed as next week.
        $this->assertSame('due-this-week', $bucket->invoke($this->ticketsService, '2026-03-25 06:59:59', $todayLa));
    }

    /**
     * #1798: the parent's "including subtasks" figures sum each direct subtask's planned hours
     * and, in one aggregate query, the hours logged against those subtask ids.
     */
    public function test_get_subtask_hour_totals_sums_plan_and_logged_hours(): void
    {
        $summedIds = new \ArrayObject;
        $service = $this->subtaskHoursService(
            [
                ['id' => 11, 'projectId' => 5, 'planHours' => '2'],
                ['id' => 12, 'projectId' => 5, 'planHours' => null],
                ['id' => 13, 'projectId' => 5, 'planHours' => '0.5'],
            ],
            [5],
            $summedIds,
            3.75
        );

        $this->assertSame(
            ['subtaskCount' => 3, 'planHours' => 2.5, 'loggedHours' => 3.75],
            $service->getSubtaskHourTotals(10)
        );
        $this->assertSame([[11, 12, 13]], $summedIds->getArrayCopy(), 'one SUM query over all included ids');
    }

    /**
     * A subtask left in a project the viewer can't access (parent moved elsewhere) is skipped
     * instead of failing the whole parent view; the count reflects what was actually included.
     */
    public function test_get_subtask_hour_totals_skips_subtasks_in_inaccessible_projects(): void
    {
        $summedIds = new \ArrayObject;
        $service = $this->subtaskHoursService(
            [
                ['id' => 11, 'projectId' => 5, 'planHours' => '2'],
                ['id' => 12, 'projectId' => 9, 'planHours' => '7'],
            ],
            [5],
            $summedIds,
            1.0
        );

        $this->assertSame(
            ['subtaskCount' => 1, 'planHours' => 2.0, 'loggedHours' => 1.0],
            $service->getSubtaskHourTotals(10)
        );
        $this->assertSame([[11]], $summedIds->getArrayCopy());
    }

    public function test_get_subtask_hour_totals_without_subtasks_is_zero(): void
    {
        $service = $this->subtaskHoursService(false, [5], new \ArrayObject, 0.0);

        $this->assertSame(
            ['subtaskCount' => 0, 'planHours' => 0.0, 'loggedHours' => 0.0],
            $service->getSubtaskHourTotals(10)
        );
    }

    /**
     * @param  false|array<int, array<string, mixed>>  $subtasks
     * @param  array<int, int>  $viewableProjects  Projects the user may view
     * @param  \ArrayObject  $summedIds  Records the id lists passed to the SUM query
     */
    private function subtaskHoursService(false|array $subtasks, array $viewableProjects, \ArrayObject $summedIds, float $loggedSum): TicketsService
    {
        $service = new TicketsService(
            language: $this->make(LanguageCore::class),
            ticketRepository: $this->make(TicketRepository::class, [
                'getAllSubtasks' => fn () => $subtasks,
                'sumLoggedHoursForTickets' => function (array $ticketIds) use ($summedIds, $loggedSum) {
                    $summedIds->append($ticketIds);

                    return $ticketIds === [] ? 0.0 : $loggedSum;
                },
            ]),
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class),
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class)
        );
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn (string $key, ?int $projectId = null) => in_array($projectId, $viewableProjects, true),
        ]));

        return $service;
    }
}
