<?php

namespace Leantime\Domain\Dashboard\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Leantime\Domain\Reports\Services\Reports as ReportService;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
    use Leantime\Core\Controller;

    /**
     *
     */

    /**
     *
     */
    class Home extends Controller
    {
        private ProjectService $projectsService;
        private TicketService $ticketsService;
        private UserService $usersService;
        private TimesheetService $timesheetsService;
        private ReportService $reportsService;
        private SettingRepository $settingRepo;
        private CalendarRepository $calendarRepo;

        /**
         * @param ProjectService     $projectsService
         * @param TicketService      $ticketsService
         * @param UserService        $usersService
         * @param TimesheetService   $timesheetsService
         * @param ReportService      $reportsService
         * @param SettingRepository  $settingRepo
         * @param CalendarRepository $calendarRepo
         * @return void
         */
        public function init(
            ProjectService $projectsService,
            TicketService $ticketsService,
            UserService $usersService,
            TimesheetService $timesheetsService,
            ReportService $reportsService,
            SettingRepository $settingRepo,
            CalendarRepository $calendarRepo
        ): void {
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
         * @throws BindingResolutionException
         */
        public function get(): void
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

            $allProjectMilestones = $this->ticketsService->getAllMilestonesByUserProjects($_SESSION["userdata"]["id"]);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign('calendar', $this->calendarRepo->getCalendar($_SESSION['userdata']['id']));

            $this->tpl->display('dashboard.home');
        }

        /**
         * @param $params
         * @return void
         * @throws BindingResolutionException
         */
        /**
         * @param $params
         * @return void
         * @throws BindingResolutionException
         */
        public function post($params): void
        {

            if (AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor, Roles::$commenter])) {
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
