<?php

namespace Leantime\Domain\Reports\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Permissions\ReportsPermissions;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

class Show extends Controller
{
    private ProjectService $projectService;

    private SprintService $sprintService;

    private TicketService $ticketService;

    private ReportService $reportService;

    /**
     * @throws BindingResolutionException
     */
    public function init(
        ProjectService $projectService,
        SprintService $sprintService,
        TicketService $ticketService,
        ReportService $reportService
    ): void {
        // Authorization lives on the action attributes (reports.view, session project) — the
        // Frontcontroller enforces them BEFORE the controller is instantiated, so init() (and the
        // dailyIngestion() it triggers) never runs for a denied user. Replaces the legacy
        // editor+ authOrRedirect (maintainer-approved readonly+ loosening: the page only
        // aggregates data readonly members already see item-by-item).
        $this->projectService = $projectService;
        $this->sprintService = $sprintService;
        $this->ticketService = $ticketService;

        session(['lastPage' => BASE_URL.'/reports/show']);

        $this->reportService = $reportService;
        $this->reportService->dailyIngestion();
    }

    /**
     * @throws BindingResolutionException
     */
    #[RequiresPermission(ReportsPermissions::VIEW)]
    public function get(array $params): Response
    {
        $currentProject = (int) session('currentProject');

        // Project Progress
        $this->tpl->assign('projectProgress', $this->projectService->getProjectProgress($currentProject));
        $this->tpl->assign('currentProjectName', $this->projectService->getProjectName($currentProject));

        // Sprint Burndown
        $requestedSprintId = isset($params['sprint']) ? (int) $params['sprint'] : null;
        $allSprints = $this->sprintService->getAllSprints($currentProject);

        $sprintBurndown = $this->reportService->getSprintBurndownForReport($currentProject, $requestedSprintId);

        $this->tpl->assign('sprintBurndown', $sprintBurndown['chart']);

        if ($allSprints !== false && count($allSprints) > 0) {
            $this->tpl->assign('currentSprint', $sprintBurndown['currentSprintId']);
        }

        $this->tpl->assign('backlogBurndown', $this->sprintService->getCummulativeReport($currentProject));
        $this->tpl->assign('allSprints', $allSprints);

        $this->tpl->assign('fullReport', $this->reportService->getFullReport($currentProject));
        $this->tpl->assign('fullReportLatest', $this->reportService->getRealtimeReport($currentProject, ''));

        $this->tpl->assign('states', $this->ticketService->getStatusLabels());

        // Milestones
        $allProjectMilestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => $currentProject]);
        $this->tpl->assign('milestones', $allProjectMilestones);

        return $this->tpl->display('reports.show');
    }

    #[RequiresPermission(ReportsPermissions::VIEW)]
    public function post($params): Response
    {
        return Frontcontroller::redirect(BASE_URL.'/dashboard/show');
    }
}
