<?php

namespace leantime\domain\controllers {

    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\repositories;
    use leantime\core;
    use leantime\core\controller;

    class home extends controller
    {
        private $projectsService;
        private services\tickets $ticketsService;
        private $usersService;
        private $timesheetsService;
        private $reportsService;
        private repositories\setting $settingRepo;

        public function init()
        {
            $this->projectsService = new services\projects();
            $this->ticketsService = new services\tickets();
            $this->usersService = new services\users();
            $this->timesheetsService = new services\timesheets();
            $this->reportsService = new services\reports();
            $this->settingRepo = new repositories\setting();

            $_SESSION['lastPage'] = BASE_URL . "/dashboard/home";
        }

        /**
         * @return void
         */
        public function get()
        {

            $projectFilter = "";
            if (isset($_SESSION['userHomeProjectFilter'])) {
                $projectFilter = $_SESSION['userHomeProjectFilter'];
            }

            if (isset($_GET['projectFilter'])) {
                $projectFilter = $_GET['projectFilter'];
                $_SESSION['userHomeProjectFilter'] = $projectFilter;
            }

            // TICKETS
            $allAssignedprojects = $this->projectsService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');

            $this->tpl->assign('allAssignedprojects', $allAssignedprojects);

            $groupBy = "time";
            if (isset($_SESSION['userHomeGroupBy'])) {
                $groupBy = $_SESSION['userHomeGroupBy'];
            }

            if (isset($_GET['groupBy'])) {
                $groupBy = $_GET['groupBy'];
                $_SESSION['userHomeGroupBy'] = $groupBy;
            }

            if ($groupBy == "time") {
                $tickets = $this->ticketsService->getOpenUserTicketsThisWeekAndLater($_SESSION["userdata"]["id"], $projectFilter);
            } elseif ($groupBy == "project") {
                $tickets = $this->ticketsService->getOpenUserTicketsByProject($_SESSION["userdata"]["id"], $projectFilter);
            }

            $allprojects = $this->projectsService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');
            $clients = array();

            $projectResults = array();
            $i = 0;

            $clientId = "";

            if (is_array($allprojects)) {
                foreach ($allprojects as $project) {
                    if (!array_key_exists($project["clientId"], $clients)) {
                        $clients[$project["clientId"]] = $project['clientName'];
                    }

                    if ($clientId == "" || $project["clientId"] == $clientId) {
                        $projectResults[$i] = $project;
                        $projectResults[$i]['progress'] = $this->projectsService->getProjectProgress($project['id']);
                        //$projectResults[$i]['milestones'] = $this->ticketsService->getAllMilestones($project['id']);


                        $fullReport = $this->reportsService->getRealtimeReport($project['id'], "");

                        $projectResults[$i]['report'] = $fullReport;

                        $i++;
                    }
                }
            }

            $currentUser = $this->usersService->getUser($_SESSION['userdata']['id']);

            $completedOnboarding = $this->settingRepo->getSetting("companysettings.completedOnboarding");
            $this->tpl->assign("completedOnboarding", $completedOnboarding);

            $this->tpl->assign("allProjects", $projectResults);

            $this->tpl->assign('currentUser', $currentUser);
            $this->tpl->assign('tickets', $tickets);
            $this->tpl->assign("onTheClock", $this->timesheetsService->isClocked($_SESSION["userdata"]["id"]));
            $this->tpl->assign('efforts', $this->ticketsService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketsService->getPriorityLabels());
            $this->tpl->assign("types", $this->ticketsService->getTicketTypes());
            $this->tpl->assign("statusLabels", $this->ticketsService->getAllStatusLabelsByUserId($_SESSION["userdata"]["id"]));
            $this->tpl->assign("milestones", $this->ticketsService->getAllMilestonesByUserProjects($_SESSION["userdata"]["id"]));

            $this->tpl->display('dashboard.home');
        }

        public function post($params)
        {

            if (services\auth::userHasRole([roles::$owner, roles::$manager, roles::$editor, roles::$commenter])) {
                if (isset($params['quickadd']) == true) {
                    $result = $this->ticketsService->quickAddTicket($params);

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
