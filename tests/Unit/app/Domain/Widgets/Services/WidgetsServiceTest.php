<?php

namespace Unit\app\Domain\Widgets\Services;

use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Widgets\Services\Widgets;
use Unit\TestCase;

/**
 * Unit tests for the Widgets service aggregation extracted from the
 * Widgets/MyProjects HxController.
 */
class WidgetsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function makeService(ProjectService $projectService, ReportService $reportService): Widgets
    {
        return new Widgets($this->make(Setting::class), $projectService, $reportService);
    }

    /**
     * The $userId parameters are pinned to the session user (RPC IDOR guard): the argument must
     * be ignored and the session id is what reaches the collaborators.
     */
    public function test_my_projects_widget_data_uses_the_session_user_not_the_argument(): void
    {
        session(['userdata' => ['id' => 7]]);

        $requestedUserId = null;
        $projectService = $this->make(ProjectService::class, [
            'getProjectsAssignedToUser' => function ($userId) use (&$requestedUserId) {
                $requestedUserId = $userId;

                return [];
            },
        ]);

        $this->makeService($projectService, $this->make(ReportService::class))->getMyProjectsWidgetData(999);

        $this->assertSame(7, $requestedUserId);
    }

    public function test_reset_dashboard_clears_the_session_users_grid_not_the_arguments(): void
    {
        session(['userdata' => ['id' => 7]]);

        $deletedKey = null;
        $settingRepo = $this->make(Setting::class, [
            'deleteSetting' => function ($key) use (&$deletedKey) {
                $deletedKey = $key;

                return true;
            },
        ]);

        (new Widgets($settingRepo, $this->make(ProjectService::class), $this->make(ReportService::class)))
            ->resetDashboard(999);

        $this->assertSame('usersettings.7.dashboardGrid', $deletedKey);
    }

    /**
     * #3791: the Welcome widget holds the only link to the Widget Manager. A saved grid that
     * lost it (the mobile layout drops it, then saveGrid persists that) must get it back.
     */
    public function test_active_widgets_restore_an_always_visible_widget_missing_from_the_saved_grid(): void
    {
        $userId = 77;
        \Illuminate\Support\Facades\Cache::forget('usersettings.'.$userId.'.dashboardGrid');

        $savedGridWithoutWelcome = serialize([
            ['id' => 'todos', 'gridX' => 0, 'gridY' => 2, 'gridWidth' => 6, 'gridHeight' => 4],
        ]);
        $settingRepo = $this->make(Setting::class, [
            'getSetting' => fn ($key) => str_ends_with($key, '.dashboardGrid') ? $savedGridWithoutWelcome : false,
        ]);

        $service = new Widgets($settingRepo, $this->make(ProjectService::class), $this->make(ReportService::class));
        $active = $service->getActiveWidgets($userId);

        $this->assertArrayHasKey('welcome', $active);
        $this->assertArrayHasKey('todos', $active);
        $this->assertArrayNotHasKey('calendar', $active, 'only ALWAYS-visible widgets are restored');
    }

    public function test_active_widgets_restore_welcome_on_a_cached_grid_too(): void
    {
        $userId = 78;
        session(['userdata' => ['id' => $userId]]); // getActiveWidgets is pinned to the session user
        $service = new Widgets($this->make(Setting::class, ['getSetting' => fn () => false]), $this->make(ProjectService::class), $this->make(ReportService::class));

        $todosOnly = ['todos' => app()->make(\Leantime\Domain\Widgets\Models\Widget::class, ['id' => 'todos', 'name' => 'widgets.title.my_todos', 'description' => '', 'widgetUrl' => '', 'gridX' => 0, 'gridY' => 2])];
        \Illuminate\Support\Facades\Cache::set('usersettings.'.$userId.'.dashboardGrid', $todosOnly, new \DateInterval('PT1H'));

        $active = $service->getActiveWidgets($userId);

        $this->assertArrayHasKey('welcome', $active);
        $this->assertArrayHasKey('todos', $active);

        \Illuminate\Support\Facades\Cache::forget('usersettings.'.$userId.'.dashboardGrid');
    }

    public function test_my_projects_widget_data_enriches_each_project(): void
    {
        $projectService = $this->make(ProjectService::class, [
            'getProjectsAssignedToUser' => fn () => [
                ['id' => 1, 'clientId' => 10, 'clientName' => 'Acme'],
                ['id' => 2, 'clientId' => 20, 'clientName' => 'Globex'],
            ],
            'getProjectProgress' => fn ($id) => ['percent' => 42, 'projectId' => $id],
        ]);
        $reportService = $this->make(ReportService::class, [
            'getRealtimeReport' => fn ($id, $sprint) => ['report' => true, 'projectId' => $id],
        ]);

        $result = $this->makeService($projectService, $reportService)->getMyProjectsWidgetData(5);

        $this->assertCount(2, $result['projects']);
        $this->assertSame(42, $result['projects'][0]['progress']['percent']);
        $this->assertSame(1, $result['projects'][0]['report']['projectId']);
        $this->assertSame([10 => 'Acme', 20 => 'Globex'], $result['clients']);
    }

    public function test_my_projects_widget_data_filters_by_client(): void
    {
        $projectService = $this->make(ProjectService::class, [
            'getProjectsAssignedToUser' => fn () => [
                ['id' => 1, 'clientId' => 10, 'clientName' => 'Acme'],
                ['id' => 2, 'clientId' => 20, 'clientName' => 'Globex'],
            ],
            'getProjectProgress' => fn ($id) => ['percent' => 0],
        ]);
        $reportService = $this->make(ReportService::class, [
            'getRealtimeReport' => fn ($id, $sprint) => [],
        ]);

        $result = $this->makeService($projectService, $reportService)->getMyProjectsWidgetData(5, '20');

        // Both clients are still mapped, but only the matching project is enriched/returned.
        $this->assertCount(1, $result['projects']);
        $this->assertSame(2, $result['projects'][0]['id']);
        $this->assertArrayHasKey(10, $result['clients']);
        $this->assertArrayHasKey(20, $result['clients']);
    }

    public function test_my_projects_widget_data_handles_no_projects(): void
    {
        $projectService = $this->make(ProjectService::class, [
            'getProjectsAssignedToUser' => fn () => [],
        ]);
        $reportService = $this->make(ReportService::class);

        $result = $this->makeService($projectService, $reportService)->getMyProjectsWidgetData(5);

        $this->assertSame([], $result['projects']);
        $this->assertSame([], $result['clients']);
    }
}
