<?php

namespace Leantime\Domain\Dashboard\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;

    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Reactions\Services\Reactions;
    use Leantime\Domain\Reports\Services\Reports;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Leantime\Domain\Reports\Services\Reports as ReportService;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
    use Leantime\Core\Controller;
    use Leantime\Domain\Widgets\Services\Widgets;

    /**
     *
     */
    class Home extends Controller
    {
        private ProjectService $projectsService;
        private TicketService $ticketsService;
        private UserService $usersService;
        private TimesheetService $timesheetsService;
        private SettingRepository $settingRepo;
        private CalendarRepository $calendarRepo;

        private Reactions $reactionsService;
        private Reports $reportService;

        private Widgets $widgetService;

        /**
         * @param ProjectService     $projectsService
         * @param TicketService      $ticketsService
         * @param UserService        $usersService
         * @param TimesheetService   $timesheetsService
         * @param SettingRepository  $settingRepo
         * @param CalendarRepository $calendarRepo
         * @return void
         */
        public function init(
            ProjectService $projectsService,
            TicketService $ticketsService,
            UserService $usersService,
            TimesheetService $timesheetsService,
            SettingRepository $settingRepo,
            CalendarRepository $calendarRepo,
            Reactions $reactionsService,
            Reports $reportsService,
            Widgets $widgetService
        ): void {
            $this->projectsService = $projectsService;
            $this->ticketsService = $ticketsService;
            $this->usersService = $usersService;
            $this->timesheetsService = $timesheetsService;
            $this->settingRepo = $settingRepo;
            $this->calendarRepo = $calendarRepo;
            $this->reactionsService = $reactionsService;
            $this->reportsService = $reportsService;
            $this->widgetService = $widgetService;

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




            $tickets = $this->ticketsService->getOpenUserTicketsByProject($_SESSION["userdata"]["id"], '');
            $totalTickets = 0;
            foreach ($tickets as $ticketGroup) {
                $totalTickets = $totalTickets + count($ticketGroup["tickets"]);
            }

            $this->tpl->assign('tickets', $tickets);
            $this->tpl->assign('totalTickets', $totalTickets);

            $allAssignedprojects = $this->projectsService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');
            $this->tpl->assign("allProjects", $allAssignedprojects);
            $this->tpl->assign("projectCount", count($allAssignedprojects));


            $currentUser = $this->usersService->getUser($_SESSION['userdata']['id']);

            $this->widgetService->resetDashboard($_SESSION['userdata']['id']);
            $dashboardGrid = $this->widgetService->getActiveWidgets($_SESSION['userdata']['id']);
            $this->tpl->assign("dashboardGrid", $dashboardGrid);

            $completedOnboarding = $this->settingRepo->getSetting("companysettings.completedOnboarding");
            $this->tpl->assign("completedOnboarding", $completedOnboarding);

            //$this->tpl->assign("allProjects", $projectResults);

            $this->tpl->assign('currentUser', $currentUser);

            //$this->tpl->assign("onTheClock", $this->timesheetsService->isClocked($_SESSION["userdata"]["id"]));
            //$this->tpl->assign('efforts', $this->ticketsService->getEffortLabels());
            //$this->tpl->assign('priorities', $this->ticketsService->getPriorityLabels());
            //$this->tpl->assign("types", $this->ticketsService->getTicketTypes());
            //$this->tpl->assign("statusLabels", $this->ticketsService->getAllStatusLabelsByUserId($_SESSION["userdata"]["id"]));

            //$allProjectMilestones = $this->ticketsService->getAllMilestonesByUserProjects($_SESSION["userdata"]["id"]);
            //$this->tpl->assign('milestones', $allProjectMilestones);
            //$this->tpl->assign('calendar', $this->calendarRepo->getCalendar($_SESSION['userdata']['id']));

            $this->tpl->display('dashboard.home');
        }

        /**
         * @param mixed $params
         * @return void
         * @throws BindingResolutionException
         */
        public function post(mixed $params): void
        {

            if (isset($params['action']) && isset($params['data']) && $params['action'] == 'saveGrid' && $params['data'] != '') {
                $this->settingRepo->saveSetting("usersettings." . $_SESSION['userdata']['id'] . ".dashboardGrid", serialize($params['data']));
                return;
            }

            if (AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor, Roles::$commenter])) {
                if (isset($params['quickadd'])) {
                    $result = $this->ticketsService->quickAddTicket($params);

                    if (isset($result["status"])) {
                        $this->tpl->setNotification($result["message"], $result["status"]);
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success", "quickticket_created");
                    }

                    $this->tpl->redirect(BASE_URL . "/dashboard/home");
                }
            }

            $this->tpl->redirect(BASE_URL . "/dashboard/home");
        }
    }
}
