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
        private $dashboardRepo;
        private $projectService;
        private $sprintService;
        private $ticketService;
        private $userService;
        private $timesheetService;
        private $reportService;

        public function init()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            $this->dashboardRepo = new repositories\dashboard();
            $this->projectService = new services\projects();
            $this->sprintService = new services\sprints();
            $this->ticketService = new services\tickets();
            $this->userService = new services\users();
            $this->timesheetService = new services\timesheets();

            $_SESSION['lastPage'] = BASE_URL . "/reports/show";

            $this->reportService = new services\reports();
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
            $milestones = $this->ticketService->getAllMilestones($_SESSION['currentProject'], true, "headline");
            $this->tpl->assign('milestones', $milestones);

            $this->tpl->display('reports.show');
        }

        public function post($params)
        {

            $this->tpl->redirect(BASE_URL . "/dashboard/show");
        }
    }
}
