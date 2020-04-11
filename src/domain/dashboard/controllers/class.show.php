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

            $_SESSION['lastPage'] = BASE_URL."/dashboard/show";

            $reportService = new services\reports();
            $reportService->dailyIngestion();

        }

        /**
         * @return void
         */
        public function get()
        {

            $this->tpl->assign('allUsers', $this->userService->getAll());

            //Project Progress
            $progress = $this->projectService->getProjectProgress($_SESSION['currentProject']);

            $this->tpl->assign('projectProgress', $progress);
            $this->tpl->assign("currentProjectName", $this->projectService->getProjectName($_SESSION['currentProject']));


            //Milestones
            $milestones = $this->ticketService->getAllMilestones($_SESSION['currentProject']);
            $this->tpl->assign('milestones', $milestones);

            // TICKETS
            $this->tpl->assign('tickets', $this->ticketService->getOpenUserTicketsThisWeekAndLater($_SESSION["userdata"]["id"], $_SESSION['currentProject']));
            $this->tpl->assign("onTheClock", $this->timesheetService->isClocked($_SESSION["userdata"]["id"]));
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign("types", $this->ticketService->getTicketTypes());
            $this->tpl->assign("statusLabels", $this->ticketService->getStatusLabels());

            $this->tpl->display('dashboard.show');

        }

        public function post($params)
        {

            if (isset($params['quickadd']) == true) {

                $result = $this->ticketService->quickAddTicket($params);

                if (isset($result["status"])) {
                    $this->tpl->setNotification($result["message"], $result["status"]);
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");
                }

                $this->tpl->redirect(BASE_URL."/dashboard/show");
            }


        }
    }
}
