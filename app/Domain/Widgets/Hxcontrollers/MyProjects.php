<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Services\Users as UserService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;

class MyProjects extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'widgets::partials.myProjects';

    private ProjectService $projectsService;
    private TicketService $ticketsService;
    private UserService $usersService;
    private TimesheetService $timesheetsService;
    private ReportService $reportsService;
    private SettingRepository $settingRepo;
    private CalendarRepository $calendarRepo;

    /**
     * Controller constructor
     *
     * @param \Leantime\Domain\Projects\Services\Projects $projectService The projects domain service.
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

    public function get()
    {

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


        $this->tpl->assign("allProjects", $projectResults);
    }
}
