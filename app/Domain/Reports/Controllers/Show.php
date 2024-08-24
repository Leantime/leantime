<?php

namespace Leantime\Domain\Reports\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Dashboard\Repositories\Dashboard as DashboardRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Reports\Services\Reports as ReportService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Show extends Controller
    {
        private DashboardRepository $dashboardRepo;
        private ProjectService $projectService;
        private SprintService $sprintService;
        private TicketService $ticketService;
        private UserService $userService;
        private TimesheetService $timesheetService;
        private ReportService $reportService;

        /**
         * @param DashboardRepository $dashboardRepo
         * @param ProjectService      $projectService
         * @param SprintService       $sprintService
         * @param TicketService       $ticketService
         * @param UserService         $userService
         * @param TimesheetService    $timesheetService
         * @param ReportService       $reportService
         * @return void
         * @throws BindingResolutionException
         * @throws BindingResolutionException
         */
        public function init(
            DashboardRepository $dashboardRepo,
            ProjectService $projectService,
            SprintService $sprintService,
            TicketService $ticketService,
            UserService $userService,
            TimesheetService $timesheetService,
            ReportService $reportService
        ): void {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->dashboardRepo = $dashboardRepo;
            $this->projectService = $projectService;
            $this->sprintService = $sprintService;
            $this->ticketService = $ticketService;
            $this->userService = $userService;
            $this->timesheetService = $timesheetService;

            session(["lastPage" => BASE_URL . "/reports/show"]);

            $this->reportService = $reportService;
            $this->reportService->dailyIngestion();
        }

        /**
         * @return Response
         * @throws BindingResolutionException
         */
        public function get(): Response
        {

            //Project Progress
            $progress = $this->projectService->getProjectProgress(session("currentProject"));

            $this->tpl->assign('projectProgress', $progress);
            $this->tpl->assign(
                "currentProjectName",
                $this->projectService->getProjectName(session("currentProject"))
            );

            //Sprint Burndown

            $allSprints = $this->sprintService->getAllSprints(session("currentProject"));

            $sprintChart = false;

            if ($allSprints !== false && count($allSprints) > 0) {
                if (isset($_GET['sprint'])) {
                    $sprintObject = $this->sprintService->getSprint((int)$_GET['sprint']);
                    $sprintChart = $this->sprintService->getSprintBurndown($sprintObject);
                    $this->tpl->assign('currentSprint', (int)$_GET['sprint']);
                } else {
                    $currentSprint = $this->sprintService->getCurrentSprintId(session("currentProject"));

                    if ($currentSprint !== false && $currentSprint != "all") {
                        $sprintObject = $this->sprintService->getSprint($currentSprint);
                        $sprintChart = $this->sprintService->getSprintBurndown($sprintObject);
                        $this->tpl->assign('currentSprint', $sprintObject->id);
                    } else {
                        $sprintChart = $this->sprintService->getSprintBurndown($allSprints[0]);
                        $this->tpl->assign('currentSprint', $allSprints[0]->id);
                    }
                }
            }

            $this->tpl->assign('sprintBurndown', $sprintChart);
            $this->tpl->assign('backlogBurndown', $this->sprintService->getCummulativeReport(session("currentProject")));

            $this->tpl->assign('allSprints', $this->sprintService->getAllSprints(session("currentProject")));

            $fullReport =  $this->reportService->getFullReport(session("currentProject"));

            $this->tpl->assign("fullReport", $fullReport);
            $this->tpl->assign("fullReportLatest", $this->reportService->getRealtimeReport(session("currentProject"), ""));

            $this->tpl->assign('states', $this->ticketService->getStatusLabels());

            //Milestones

            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => session("currentProject")]);
            $this->tpl->assign('milestones', $allProjectMilestones);

            return $this->tpl->display('reports.show');
        }

        /**
         * @param $params
         * @return Response
         */
        public function post($params): Response
        {
            return Frontcontroller::redirect(BASE_URL . "/dashboard/show");
        }
    }
}
