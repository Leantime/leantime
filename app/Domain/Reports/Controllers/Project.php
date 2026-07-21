<?php

declare(strict_types=1);

namespace Leantime\Domain\Reports\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Models\ReportPeriod;
use Leantime\Domain\Reports\Permissions\ReportsPermissions;
use Leantime\Domain\Reports\Services\ReportEngine;

/**
 * Project status report: period-filtered milestones (accomplished / in flight / coming up),
 * outcome narratives, goals, effort and status updates for the current project. The sibling
 * /reports/show screen keeps the sprint delivery metrics (burndown, cumulative flow).
 */
class Project extends Controller
{
    private ReportEngine $reportEngine;

    private ProjectService $projectService;

    public function init(ReportEngine $reportEngine, ProjectService $projectService): void
    {
        $this->reportEngine = $reportEngine;
        $this->projectService = $projectService;

        session(['lastPage' => BASE_URL.'/reports/project']);
    }

    /**
     * Renders the project status report for the requested period (default: this quarter).
     *
     * @param  array<string, mixed>  $params
     */
    #[RequiresPermission(ReportsPermissions::VIEW)]
    public function get(array $params): \Symfony\Component\HttpFoundation\Response
    {
        // Drill-down links from plan/strategy rollups target a specific project; switch the
        // session project when the user has access (mirrors EditMilestone's behavior).
        // `id` is the Frontcontroller's path-segment param, so /reports/project/123 works too.
        $requestedProjectId = (int) ($params['projectId'] ?? $params['id'] ?? 0);
        if (
            $requestedProjectId > 0
            && $requestedProjectId !== (int) session('currentProject')
            && $this->projectService->isUserAssignedToProject((int) session('userdata.id'), $requestedProjectId)
        ) {
            $this->projectService->changeCurrentSessionProject($requestedProjectId);
        }

        $projectId = (int) session('currentProject');

        if ($projectId === 0) {
            return Frontcontroller::redirect(BASE_URL.'/dashboard/home');
        }

        $period = ReportPeriod::fromRequest($params);
        $report = $this->reportEngine->buildReport([$projectId], $period);

        $this->tpl->assign('projectId', $projectId);
        $this->tpl->assign('period', $period);
        $this->tpl->assign('report', $report);

        return $this->tpl->display('reports.project');
    }
}
