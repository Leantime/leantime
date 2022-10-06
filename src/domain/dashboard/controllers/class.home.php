<?php

namespace leantime\domain\controllers {

    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\repositories;
    use leantime\core;

    class home
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
            $this->reportService = new services\reports();

            $_SESSION['lastPage'] = BASE_URL."/dashboard/home";

        }

        /**
         * @return void
         */
        public function get()
        {

            $projectFilter = "";
            if(isset($_SESSION['userHomeProjectFilter'])) {
                $projectFilter = $_SESSION['userHomeProjectFilter'];
            }

            if(isset($_GET['projectFilter'])){
                $projectFilter = $_GET['projectFilter'];
                $_SESSION['userHomeProjectFilter'] = $projectFilter;
            }

            // TICKETS
            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');

            $this->tpl->assign('allAssignedprojects', $allAssignedprojects);

            $groupBy = "time";
            if(isset($_SESSION['userHomeGroupBy'])) {
                $groupBy = $_SESSION['userHomeGroupBy'];
            }

            if(isset($_GET['groupBy'])) {
                $groupBy = $_GET['groupBy'];
                $_SESSION['userHomeGroupBy'] = $groupBy;
            }

            if($groupBy == "time") {
                $tickets = $this->ticketService->getOpenUserTicketsThisWeekAndLater($_SESSION["userdata"]["id"], $projectFilter);
            }else if($groupBy == "project") {
                $tickets = $this->ticketService->getOpenUserTicketsByProject($_SESSION["userdata"]["id"], $projectFilter);
            }


            $allprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');
            $clients = array();

            $projectResults = array();
            $i = 0;

            $clientId = "";

            if(is_array($allprojects)) {
                foreach ($allprojects as $project) {
                    if (!array_key_exists($project["clientId"], $clients)) {
                        $clients[$project["clientId"]] = $project['clientName'];
                    }

                    if ($clientId == "" || $project["clientId"] == $clientId) {
                        $projectResults[$i] = $project;
                        $projectResults[$i]['progress'] = $this->projectService->getProjectProgress($project['id']);
                        //$projectResults[$i]['milestones'] = $this->ticketService->getAllMilestones($project['id']);


                        $fullReport = $this->reportService->getRealtimeReport($project['id'], "");

                        $projectResults[$i]['report'] = $fullReport;

                        $i++;
                    }
                }
            }


            $currentUser = $this->userService->getUser($_SESSION['userdata']['id']);

            $this->tpl->assign("allProjects", $projectResults);

            $this->tpl->assign('currentUser', $currentUser);
            $this->tpl->assign('tickets', $tickets);
            $this->tpl->assign("onTheClock", $this->timesheetService->isClocked($_SESSION["userdata"]["id"]));
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());
            $this->tpl->assign("types", $this->ticketService->getTicketTypes());
            $this->tpl->assign("statusLabels", $this->ticketService->getStatusLabels());

            $this->tpl->display('dashboard.home');

        }

        public function post($params)
        {

            if(services\auth::userHasRole([roles::$owner, roles::$manager, roles::$editor, roles::$commenter])) {

                if (isset($params['quickadd']) == true) {
                    $result = $this->ticketService->quickAddTicket($params);

                    if (isset($result["status"])) {
                        $this->tpl->setNotification($result["message"], $result["status"]);
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");
                    }

                    $this->tpl->redirect(BASE_URL . "/dashboard/home");
                }
            }

            $this->tpl->redirect(BASE_URL . "/dashboard/home");


        }
    }
}
