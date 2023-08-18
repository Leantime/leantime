<?php

namespace leantime\domain\controllers {

    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\repositories;
    use leantime\core;
    use leantime\core\controller;

    class home extends controller
    {
        private services\projects $projectsService;
        private services\tickets $ticketsService;
        private services\users $usersService;
        private services\timesheets $timesheetsService;
        private services\reports $reportsService;
        private repositories\setting $settingRepo;
        private repositories\calendar $calendarRepo;

        public function init(
            services\projects $projectsService,
            services\tickets $ticketsService,
            services\users $usersService,
            services\timesheets $timesheetsService,
            services\reports $reportsService,
            repositories\setting $settingRepo,
            repositories\calendar $calendarRepo
        ) {
            $this->projectsService = $projectsService;
            $this->ticketsService = $ticketsService;
            $this->usersService = $usersService;
            $this->timesheetsService = $timesheetsService;
            $this->reportsService = $reportsService;
            $this->settingRepo = $settingRepo;
            $this->calendarRepo = $calendarRepo;

            $_SESSION['lastPage'] = BASE_URL . "/dashboard/home";
        }

        /**
         * @return void
         */
        public function get()
        {
            $images = array(
                "undraw_smiley_face_re_9uid.svg",
                "undraw_meditation_re_gll0.svg",
                "undraw_fans_re_cri3.svg",
                "undraw_air_support_re_nybl.svg",
                "undraw_join_re_w1lh.svg",
                "undraw_blooming_re_2kc4.svg",
                "undraw_happy_music_g6wc.svg",
                "undraw_powerful_re_frhr.svg",
                "undraw_welcome_re_h3d9.svg",
                "undraw_joyride_re_968t.svg",
                "undraw_welcoming_re_x0qo.svg",
            );

            $randomKey = rand(0, count($images) - 1);

            $this->tpl->assign('randomImage', $images[$randomKey]);

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
            $prepareTicketSearchArray = $this->ticketService->prepareTicketSearchArray(["sprint" => '', "type"=> "milestone"]);
            $allProjectMilestones = $this->ticketService->getAllMilestones($prepareTicketSearchArray);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign('calendar', $this->calendarRepo->getCalendar($_SESSION['userdata']['id']));

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
