<?php

namespace Leantime\Domain\Dashboard\Controllers {

    use GuzzleHttp\Promise\Promise;
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
    use SimplePie\Exception;
    use Symfony\Component\HttpFoundation\Response;
    use Leantime\Core\Frontcontroller;

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
            $this->reportService = $reportsService;
            $this->widgetService = $widgetService;

            session(["lastPage" => BASE_URL . "/dashboard/home"]);
        }

        /**
         * @return Response
         * @throws BindingResolutionException
         */
        public function get(): Response
        {

            //Debug uncomment to reset dashboard
            if(isset($_GET['resetDashboard']) === true){
                $this->widgetService->resetDashboard(session("userdata.id"));
            }
            $dashboardGrid = $this->widgetService->getActiveWidgets(session("userdata.id"));
            $this->tpl->assign("dashboardGrid", $dashboardGrid);

            $completedOnboarding = $this->settingRepo->getSetting("companysettings.completedOnboarding");
            $this->tpl->assign("completedOnboarding", $completedOnboarding);


            //Fallback in case telemetry does not get executed as part of the cron job
/*            try {


               $reportService = app()->make(Reports::class);
               $promise = $reportService->sendAnonymousTelemetry();
                if($promise !== false){
                    $promise->wait();
                }

            }catch(\Exception $e){
                error_log($e);
            }*/

            return $this->tpl->display('dashboard.home');
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {

            if (isset($params['action']) && isset($params['data']) && $params['action'] == 'saveGrid' && $params['data'] != '') {
                $this->settingRepo->saveSetting("usersettings." . session("userdata.id") . ".dashboardGrid", serialize($params['data']));
                return new Response();
            }

            return Frontcontroller::redirect(BASE_URL . "/dashboard/home");
        }
    }
}
