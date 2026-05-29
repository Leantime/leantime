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
