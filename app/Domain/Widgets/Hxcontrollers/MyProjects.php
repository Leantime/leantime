<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users as UserService;

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

    private Menu $menuService;

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
        CalendarRepository $calendarRepo,
        Menu $menuService
    ) {
        $this->projectsService = $projectsService;
        $this->ticketsService = $ticketsService;
        $this->usersService = $usersService;
        $this->timesheetsService = $timesheetsService;
        $this->reportsService = $reportsService;
        $this->settingRepo = $settingRepo;
        $this->calendarRepo = $calendarRepo;
        $this->menuService = $menuService;

        session(["lastPage" => BASE_URL . "/dashboard/home"]);
    }

    public function get()
    {

        $allprojects = $this->projectsService->getProjectsAssignedToUser(session("userdata.id"), 'open');
        $clients = array();

        $projectResults = array();
        $i = 0;

        $clientId = "";

        $this->tpl->assign("background", $_GET['noBackground'] ?? "");
        $this->tpl->assign("type", $_GET['type'] ?? "simple");

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

        $projectTypeAvatars = $this->menuService->getProjectTypeAvatars();

        $this->tpl->assign("projectTypeAvatars", $projectTypeAvatars);

        $this->tpl->assign("allProjects", $projectResults);
    }
}
