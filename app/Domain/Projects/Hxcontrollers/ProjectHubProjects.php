<?php

namespace Leantime\Domain\Projects\Hxcontrollers;

use Leantime\Core\Frontcontroller;
use Leantime\Core\HtmxController;
use Leantime\Domain\Clients\Repositories\Clients;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Services\Users as UserService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;

class ProjectHubProjects extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'projects::partials.projectHubProjects';

    private ProjectService $projectsService;
    private TicketService $ticketsService;
    private UserService $usersService;
    private TimesheetService $timesheetsService;
    private ReportService $reportsService;
    private SettingRepository $settingRepo;
    private CalendarRepository $calendarRepo;

    private Clients $clientRepo;

    private Comments $commentsService;

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
        Clients $clientRepo,
        Comments $commentsService,
        Menu $menuService
    ) {
        $this->projectsService = $projectsService;
        $this->ticketsService = $ticketsService;
        $this->usersService = $usersService;
        $this->timesheetsService = $timesheetsService;
        $this->reportsService = $reportsService;
        $this->settingRepo = $settingRepo;
        $this->calendarRepo = $calendarRepo;
        $this->clientRepo = $clientRepo;
        $this->commentsService = $commentsService;
        $this->menuService = $menuService;


        $_SESSION['lastPage'] = BASE_URL . "/dashboard/home";
    }

    public function get()
    {

        $clientId = "";
        $currentClientName = "";
        if (isset($_GET['client']) === true && $_GET['client'] != '') {
            $clientId = (int)$_GET['client'];
            $currentClient = $this->clientRepo->getClient($clientId);
            if (is_array($currentClient) && count($currentClient) > 0) {
                $currentClientName = $currentClient['name'];
            }
        }


        $allprojects = $this->projectsService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');
        $clients = array();


        $projectResults = array();
        $i = 0;

        if (is_array($allprojects)) {
            foreach ($allprojects as $project) {
                if (!array_key_exists($project["clientId"], $clients)) {
                    $clients[$project["clientId"]] = array("name" => $project['clientName'], "id" => $project["clientId"]);
                }

                if ($clientId == "" || $project["clientId"] == $clientId) {
                    $projectResults[$i] = $project;
                    $projectResults[$i]['progress'] = $this->projectsService->getProjectProgress($project['id']);

                    //$allProjectMilestones = $this->ticketsService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $_SESSION["currentProject"]]);

                    //$projectResults[$i]['milestones'] = $allProjectMilestones;
                    $projectComment = $this->commentsService->getComments("project", $project['id']);

                    if (is_array($projectComment) && count($projectComment) > 0) {
                        $projectResults[$i]['lastUpdate'] = $projectComment[0];
                    } else {
                        $projectResults[$i]['lastUpdate'] = false;
                    }

                    //$fullReport = $this->reportsService->getRealtimeReport($project['id'], "");

                    //$projectResults[$i]['report'] = $fullReport;

                    $i++;
                }
            }
        }

        $projectTypeAvatars = $this->menuService->getProjectTypeAvatars();

        $currentUrlPath = BASE_URL . "/" . str_replace(".", "/", Frontcontroller::getCurrentRoute());

        $this->tpl->assign("projectTypeAvatars", $projectTypeAvatars);
        $this->tpl->assign("currentUrlPath", $currentUrlPath);
        $this->tpl->assign("currentClientName", $currentClientName);
        $this->tpl->assign("currentClient", $clientId);
        $this->tpl->assign("clients", $clients);
        $this->tpl->assign("allProjects", $projectResults);
        $this->tpl->assign("allProjects", $projectResults);
    }
}
