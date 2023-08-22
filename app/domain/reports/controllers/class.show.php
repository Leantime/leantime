<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;
    use leantime\domain\models\auth\roles;

    class show extends controller
    {
        private repositories\dashboard $dashboardRepo;
        private services\projects $projectService;
        private services\sprints $sprintService;
        private services\tickets $ticketService;
        private services\users $userService;
        private services\timesheets $timesheetService;
        private services\reports $reportService;

        public function init(
            repositories\dashboard $dashboardRepo,
            services\projects $projectService,
            services\sprints $sprintService,
            services\tickets $ticketService,
            services\users $userService,
            services\timesheets $timesheetService,
            services\reports $reportService
        ) {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            $this->dashboardRepo = $dashboardRepo;
            $this->projectService = $projectService;
            $this->sprintService = $sprintService;
            $this->ticketService = $ticketService;
            $this->userService = $userService;
            $this->timesheetService = $timesheetService;

            $_SESSION['lastPage'] = BASE_URL . "/reports/show";

            $this->reportService = $reportService;
            $this->reportService->dailyIngestion();
        }

        /**
         * @return void
         */
        public function get()
        {

            //Project Progress
            $progress = $this->projectService->getProjectProgress($_SESSION['currentProject']);

            $this->tpl->assign('projectProgress', $progress);
            $this->tpl->assign(
                "currentProjectName",
                $this->projectService->getProjectName($_SESSION['currentProject'])
            );

            //Sprint Burndown

            $allSprints = $this->sprintService->getAllSprints($_SESSION['currentProject']);

            $sprintChart = false;

            if ($allSprints !== false && count($allSprints) > 0) {
                if (isset($_GET['sprint'])) {
                    $sprintObject = $this->sprintService->getSprint((int)$_GET['sprint']);
                    $sprintChart = $this->sprintService->getSprintBurndown($sprintObject);
                    $this->tpl->assign('currentSprint', (int)$_GET['sprint']);
                } else {
                    $currentSprint = $this->sprintService->getCurrentSprintId($_SESSION['currentProject']);

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
            $this->tpl->assign('backlogBurndown', $this->sprintService->getCummulativeReport($_SESSION['currentProject']));

            $this->tpl->assign('allSprints', $this->sprintService->getAllSprints($_SESSION['currentProject']));

            $fullReport =  $this->reportService->getFullReport($_SESSION['currentProject']);

            $this->tpl->assign("fullReport", $fullReport);
            $this->tpl->assign("fullReportLatest", $this->reportService->getRealtimeReport($_SESSION['currentProject'], ""));

            $this->tpl->assign('states', $this->ticketService->getStatusLabels());

            //Milestones

            $prepareTicketSearchArray = $this->ticketService->prepareTicketSearchArray(["sprint" => '', "type"=> "milestone"]);
            $allProjectMilestones = $this->ticketService->getAllMilestones($prepareTicketSearchArray);
            $this->tpl->assign('milestones', $allProjectMilestones);

            $this->tpl->display('reports.show');
        }

        public function post($params)
        {

            $this->tpl->redirect(BASE_URL . "/dashboard/show");
        }
    }
}
