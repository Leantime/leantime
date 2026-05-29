<?php

namespace Unit\app\Domain\Widgets\Services;

use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Services\Users as UserService;
use Leantime\Domain\Widgets\Services\Dashboard;
use Leantime\Domain\Widgets\Services\Widgets;
use Unit\TestCase;

/**
 * Unit tests for the Widgets Dashboard service that backs the Welcome and
 * "My To-Dos" dashboard widgets.
 */
class DashboardServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        // Provide everything dtHelper() needs so getWelcomeWidgetData() can call
        // dtHelper()->userNow() without reaching for the Environment/Language.
        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);
    }

    /**
     * Builds a Dashboard service with the supplied (mocked) collaborators,
     * filling in empty stubs for any not provided.
     */
    private function makeService(array $overrides = []): Dashboard
    {
        return new Dashboard(
            $overrides['tickets'] ?? $this->make(TicketService::class),
            $overrides['settings'] ?? $this->make(SettingService::class),
            $overrides['projects'] ?? $this->make(ProjectService::class),
            $overrides['users'] ?? $this->make(UserService::class),
            $overrides['reports'] ?? $this->make(ReportService::class),
            $overrides['widgets'] ?? $this->make(Widgets::class),
        );
    }

    public function test_resolve_quick_add_due_date_keeps_existing_date(): void
    {
        $service = $this->makeService();

        $result = $service->resolveQuickAddDueDate(['dateToFinish' => '2026-01-02', 'group' => 'thisWeek']);

        $this->assertSame('2026-01-02', $result);
    }

    public function test_resolve_quick_add_due_date_this_week_maps_to_next_friday(): void
    {
        $service = $this->makeService();

        $result = $service->resolveQuickAddDueDate(['dateToFinish' => '', 'group' => 'thisWeek']);

        $this->assertSame(date('Y-m-d', strtotime('next friday')), $result);
    }

    public function test_resolve_quick_add_due_date_overdue_maps_to_today(): void
    {
        $service = $this->makeService();

        $result = $service->resolveQuickAddDueDate(['group' => 'overdue']);

        $this->assertSame(date('Y-m-d'), $result);
    }

    public function test_resolve_quick_add_due_date_later_stays_empty(): void
    {
        $service = $this->makeService();

        $this->assertSame('', $service->resolveQuickAddDueDate(['group' => 'later']));
        $this->assertSame('', $service->resolveQuickAddDueDate([]));
    }

    public function test_map_group_to_fields_priority(): void
    {
        $service = $this->makeService();

        $this->assertSame(['priority' => 2], $service->mapGroupToFields('priority', '2'));
        $this->assertSame(['priority' => ''], $service->mapGroupToFields('priority', '999'));
        $this->assertSame([], $service->mapGroupToFields('priority', '7'));
    }

    public function test_map_group_to_fields_project(): void
    {
        $service = $this->makeService();

        $this->assertSame(['projectId' => 5], $service->mapGroupToFields('project', '5'));
        $this->assertSame([], $service->mapGroupToFields('project', '0'));
    }

    public function test_map_group_to_fields_time(): void
    {
        $service = $this->makeService();

        $this->assertSame(
            ['dateToFinish' => date('Y-m-d', strtotime('yesterday'))],
            $service->mapGroupToFields('time', 'overdue')
        );
        $this->assertSame(['dateToFinish' => ''], $service->mapGroupToFields('time', 'later'));
        $this->assertSame([], $service->mapGroupToFields('time', 'bogus'));
    }

    public function test_map_group_to_fields_unknown_group_by_returns_empty(): void
    {
        $service = $this->makeService();

        $this->assertSame([], $service->mapGroupToFields('unknown', 'whatever'));
    }

    public function test_has_more_tickets_preserves_full_page_semantics(): void
    {
        $service = $this->makeService();

        // Two groups. countNested over the whole collection counts each group node
        // plus its nested tickets: group1 = 1 + 2 = 3, group2 = 1 + 1 = 2, total = 5.
        // The legacy loop re-counts the whole collection once per group, so the
        // returned total is 5 * 2 = 10. This quirk is preserved deliberately.
        $groups = [
            ['tickets' => [['id' => 1], ['id' => 2]]],
            ['tickets' => [['id' => 3]]],
        ];

        $this->assertTrue($service->hasMoreTickets($groups, 10));
        $this->assertTrue($service->hasMoreTickets($groups, 9));
        $this->assertFalse($service->hasMoreTickets($groups, 11));
        $this->assertFalse($service->hasMoreTickets([], 1));
    }

    public function test_add_todo_resolves_due_date_then_delegates(): void
    {
        $captured = null;
        $tickets = $this->make(TicketService::class, [
            'quickAddTicket' => function ($params) use (&$captured) {
                $captured = $params;

                return ['status' => 'success'];
            },
        ]);

        $service = $this->makeService(['tickets' => $tickets]);

        $result = $service->addTodo(['quickadd' => '1', 'dateToFinish' => '', 'group' => 'overdue']);

        $this->assertSame(['status' => 'success'], $result);
        $this->assertSame(date('Y-m-d'), $captured['dateToFinish']);
    }

    public function test_toggle_task_collapse_flips_state_and_persists(): void
    {
        $saved = [];
        $settings = $this->make(SettingService::class, [
            'getSetting' => fn () => 'open',
            'saveSetting' => function ($key, $value) use (&$saved) {
                $saved[$key] = $value;

                return true;
            },
        ]);

        $service = $this->makeService(['settings' => $settings]);

        $newState = $service->toggleTaskCollapse(7, '42');

        $this->assertSame('closed', $newState);
        $this->assertSame('closed', $saved['user.7.taskCollapsed.42']);
    }

    public function test_save_todo_sorting_normalizes_order_and_persists(): void
    {
        $savedValue = null;
        $settings = $this->make(SettingService::class, [
            'saveSetting' => function ($key, $value) use (&$savedValue) {
                $savedValue = $value;

                return true;
            },
        ]);
        // No dependencies / patches expected when there are no parents.
        $tickets = $this->make(TicketService::class, [
            'patch' => fn () => true,
        ]);

        $service = $this->makeService(['settings' => $settings, 'tickets' => $tickets]);

        $rawItems = [
            json_encode(['id' => 1, 'order' => 0]),
            json_encode(['id' => 2, 'order' => 5]),
        ];

        $result = $service->saveTodoSorting(99, $rawItems, [], 'time');

        $this->assertTrue($result['sorted']);
        $this->assertSame(0, $result['successCount']);
        $this->assertSame(0, $result['errorCount']);

        $persisted = json_decode($savedValue, true);
        $this->assertSame(10, $persisted[0]['order']);
        $this->assertSame(15, $persisted[1]['order']);
    }

    public function test_save_todo_sorting_returns_not_sorted_for_non_array_payload(): void
    {
        $service = $this->makeService();

        $result = $service->saveTodoSorting(99, 'not-an-array', [], 'time');

        $this->assertFalse($result['sorted']);
        $this->assertSame(0, $result['successCount']);
        $this->assertSame(0, $result['errorCount']);
    }

    public function test_update_ticket_dependencies_sets_and_clears_parents(): void
    {
        $patches = [];
        $tickets = $this->make(TicketService::class, [
            'patch' => function ($id, $fields) use (&$patches) {
                $patches[$id] = $fields;

                return true;
            },
        ]);

        $service = $this->makeService(['tickets' => $tickets]);

        $service->updateTicketDependencies([
            ['id' => 1, 'parentId' => 5, 'parentType' => 'ticket'],
            ['id' => 2, 'parentId' => null, 'parentType' => null],
            ['id' => 3, 'parentId' => 3, 'parentType' => 'ticket'], // self-reference skipped
        ]);

        $this->assertSame(['dependingTicketId' => 5], $patches[1]);
        $this->assertSame(['dependingTicketId' => '', 'milestoneid' => ''], $patches[2]);
        $this->assertArrayNotHasKey(3, $patches);
    }

    public function test_get_welcome_widget_data_aggregates_counts(): void
    {
        session(['userdata' => ['id' => 4]]);
        session(['usersettings.timezone' => 'UTC']);

        $tickets = $this->make(TicketService::class, [
            'simpleTicketCounter' => fn () => 12,
            'getRecentlyCompletedTicketsByUser' => fn () => [['id' => 1], ['id' => 2]],
            'goalsRelatedToWork' => fn () => [['id' => 9]],
            'getScheduledTasks' => fn () => [
                'totalTasks' => [['id' => 1], ['id' => 2], ['id' => 3]],
                'doneTasks' => [['id' => 1]],
            ],
        ]);
        $projects = $this->make(ProjectService::class, [
            'getProjectsAssignedToUser' => fn () => [['id' => 1], ['id' => 2]],
        ]);
        $users = $this->make(UserService::class, [
            'getUser' => fn () => ['id' => 4, 'username' => 'tester'],
        ]);
        $widgets = $this->make(Widgets::class, [
            'getNewWidgets' => fn () => ['todos' => true],
        ]);

        $service = $this->makeService([
            'tickets' => $tickets,
            'projects' => $projects,
            'users' => $users,
            'widgets' => $widgets,
        ]);

        $data = $service->getWelcomeWidgetData(4);

        $this->assertSame(12, $data['totalTickets']);
        $this->assertSame(2, $data['closedTicketsCount']);
        $this->assertSame(1, $data['ticketsInGoals']);
        $this->assertSame(3, $data['totalTodayCount']);
        $this->assertSame(1, $data['doneTodayCount']);
        $this->assertSame(2, $data['projectCount']);
        $this->assertTrue($data['showSettingsIndicator']);
        $this->assertSame(['id' => 4, 'username' => 'tester'], $data['currentUser']);
    }

    public function test_get_welcome_widget_data_handles_non_array_results(): void
    {
        session(['userdata' => ['id' => 4]]);
        session(['usersettings.timezone' => 'UTC']);

        $tickets = $this->make(TicketService::class, [
            'simpleTicketCounter' => fn () => 0,
            'getRecentlyCompletedTicketsByUser' => fn () => [],
            'goalsRelatedToWork' => fn () => false,
            'getScheduledTasks' => fn () => [],
        ]);
        $projects = $this->make(ProjectService::class, [
            'getProjectsAssignedToUser' => fn () => [],
        ]);
        $users = $this->make(UserService::class, [
            'getUser' => fn () => ['id' => 4],
        ]);
        $widgets = $this->make(Widgets::class, [
            'getNewWidgets' => fn () => [],
        ]);

        $service = $this->makeService([
            'tickets' => $tickets,
            'projects' => $projects,
            'users' => $users,
            'widgets' => $widgets,
        ]);

        $data = $service->getWelcomeWidgetData(4);

        $this->assertSame(0, $data['closedTicketsCount']);
        $this->assertSame(0, $data['ticketsInGoals']);
        $this->assertSame(0, $data['totalTodayCount']);
        $this->assertSame(0, $data['doneTodayCount']);
        $this->assertSame([], $data['allProjects']);
        $this->assertSame(0, $data['projectCount']);
        $this->assertFalse($data['showSettingsIndicator']);
    }
}
