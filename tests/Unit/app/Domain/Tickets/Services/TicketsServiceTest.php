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

    public function test_sort_tickets_is_denied_for_non_editor(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        $this->expectException(AuthorizationException::class);

        $this->ticketsService->sortTickets(['5' => 1]);
    }

    public function test_status_and_sorting_is_denied_for_non_editor(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        $this->assertFalse($this->ticketsService->updateTicketStatusAndSorting(['3' => 'ticket[]=5'], null));
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
}
