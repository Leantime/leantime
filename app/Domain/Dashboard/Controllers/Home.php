<?php

namespace Leantime\Domain\Dashboard\Controllers {

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

    class Home extends Controller
    {
        private ProjectService $projectsService;
        private TicketService $ticketsService;
        private UserService $usersService;
        private TimesheetService $timesheetsService;
        private ReportService $reportsService;
        private SettingRepository $settingRepo;
        private CalendarRepository $calendarRepo;

        public function init(
            ProjectService $projectsService,
            TicketService $ticketsService,
            UserService $usersService,
            TimesheetService $timesheetsService,
            ReportService $reportsService,
            SettingRepository $settingRepo,
            CalendarRepository $calendarRepo
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

            $dashboardGrid = $this->settingRepo->getSetting("usersettings.". $_SESSION['userdata']['id'] . ".dashboardGrid");
            $unserializedData =  unserialize($dashboardGrid);
            $unserializedData = array_sort($unserializedData, function($a, $b) {

                $first = intval($a['y'].$a['x']);
                $second = intval(($b['y'] ?? 0).($b['x'] ?? 0));
                return $first - $second;
            });
            $this->tpl->assign("dashboardGrid", $unserializedData);

            $completedOnboarding = $this->settingRepo->getSetting("companysettings.completedOnboarding");
            $this->tpl->assign("completedOnboarding", $completedOnboarding);


            $this->tpl->display('dashboard.home');
        }

        public function post($params) {

            if(isset($params['action']) && isset($params['data']) && $params['action'] == 'saveGrid' && $params['data'] != '' ) {
                $this->settingRepo->saveSetting("usersettings.". $_SESSION['userdata']['id'] . ".dashboardGrid", serialize($params['data']));
                return;
            }

        }

    }
}
