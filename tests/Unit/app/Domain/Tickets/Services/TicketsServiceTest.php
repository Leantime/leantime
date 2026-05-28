<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Core\UI\Template as TemplateCore;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
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

        // Instantiate the service with mocked dependencies
        $this->ticketsService = new TicketsService(
            tpl: $tpl,
            language: $language,
            config: $config,
            projectRepository: $projectRepository,
            ticketRepository: $ticketRepository,
            timesheetsRepo: $timesheetsRepo,
            settingsRepo: $settingsRepo,
            projectService: $projectService,
            timesheetService: $timesheetService,
            sprintService: $sprintService,
            ticketHistoryRepo: $ticketHistoryRepo,
            goalcanvasService: $goalcanvasService,
            dateTimeHelper: $dateTimeHelper
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
     * Test that enrichGroupedTicketsWithCollaborators adds metadata to 'items' groups (list/kanban views).
     */
    public function test_enrich_grouped_tickets_with_collaborators_items_key()
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getCollaboratorsByTicketIds' => function ($ids) {
                return [
                    10 => [100, 200],
                    11 => [300],
                ];
            },
        ]);

        $service = new TicketsService(
            tpl: $this->make(TemplateCore::class),
            language: $this->make(LanguageCore::class),
            config: $this->make(EnvironmentCore::class),
            projectRepository: $this->make(ProjectRepository::class),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class),
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class)
        );

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
     * Test that enrichGroupedTicketsWithCollaborators supports 'tickets' key (ToDoWidget views).
     */
    public function test_enrich_grouped_tickets_with_collaborators_tickets_key()
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getCollaboratorsByTicketIds' => function ($ids) {
                return [
                    20 => [400, 500, 600],
                ];
            },
        ]);

        $service = new TicketsService(
            tpl: $this->make(TemplateCore::class),
            language: $this->make(LanguageCore::class),
            config: $this->make(EnvironmentCore::class),
            projectRepository: $this->make(ProjectRepository::class),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class),
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class)
        );

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
     * Test collaborator overflow count when more than 2 collaborators exist.
     */
    public function test_enrich_grouped_tickets_collaborator_overflow()
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getCollaboratorsByTicketIds' => function ($ids) {
                return [
                    30 => [101, 102, 103, 104, 105],
                ];
            },
        ]);

        $service = new TicketsService(
            tpl: $this->make(TemplateCore::class),
            language: $this->make(LanguageCore::class),
            config: $this->make(EnvironmentCore::class),
            projectRepository: $this->make(ProjectRepository::class),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class),
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class)
        );

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
}
