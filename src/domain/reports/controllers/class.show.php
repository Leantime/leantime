<?php

namespace leantime\domain\controllers {

    use leantime\domain\services;
    use leantime\domain\repositories;
    use leantime\core;

    class show
    {

        private $tpl;
        private $dashboardRepo;
        private $projectService;
        private $sprintService;
        private $ticketService;
        private $userService;
        private $timesheetService;
        private $reportService;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->dashboardRepo = new repositories\dashboard();
            $this->projectService = new services\projects();
            $this->sprintService = new services\sprints();
            $this->ticketService = new services\tickets();
            $this->userService = new services\users();
            $this->timesheetService = new services\timesheets();
            $this->language = new core\language();

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
            $this->tpl->assign("currentProjectName",
                $this->projectService->getProjectName($_SESSION['currentProject']));

            //Sprint Burndown
            if (isset($_GET['sprint'])) {
                $sprintChart = $this->sprintService->getSprintBurndown((int)$_GET['sprint']);
            } else {
                $currentSprint = $this->sprintService->getCurrentSprint($_SESSION['currentProject']);
                $sprintChart = $this->sprintService->getSprintBurndown($currentSprint);
            }

            $this->tpl->assign('sprintBurndown', $sprintChart);
            $this->tpl->assign('backlogBurndown',
                $this->sprintService->getBacklogBurndown($_SESSION['currentProject']));

            $this->tpl->assign('allSprints', $this->sprintService->getAllSprints($_SESSION['currentProject']));

            //Milestones
            $milestones = $this->ticketService->getAllMilestones($_SESSION['currentProject']);
            $this->tpl->assign('milestones', $milestones);

            $this->tpl->display('reports.show');

        }

        public function post($params)
        {

            $this->tpl->redirect(BASE_URL . "/dashboard/show");

        }

    }
}
