<?php

namespace Leantime\Domain\Projects\Hxcontrollers;

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Domain\Clients\Repositories\Clients;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reactions\Services\Reactions;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users as UserService;

class ProjectCardProgress extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'projects::partials.projectCardProgressBar';

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

    private Reactions $reactionService;

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
        Menu $menuService,
        Reactions $reactionService
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
        $this->reactionService = $reactionService;


        session(["lastPage" => BASE_URL . "/dashboard/home"]);
    }

    public function getProgress() {

        $projectId = $_GET['pId'];

        $project = array("id" => $projectId);

        $project['progress'] = $this->projectsService->getProjectProgress($project['id']);
        $projectComment = $this->commentsService->getComments("project", $project['id']);
        $project['team'] = $this->projectsService->getUsersAssignedToProject($project['id'], true);

        if (is_array($projectComment) && count($projectComment) > 0) {
            $project['lastUpdate'] = $projectComment[0];
            $project['status'] = $projectComment[0]['status'];
        } else {
            $project['lastUpdate'] = false;
            $project['status'] = '';
        }

        $projectTypeAvatars  = $this->menuService->getProjectTypeAvatars();

        $currentUrlPath = BASE_URL . "/" . str_replace(".", "/", Frontcontroller::getCurrentRoute());

        $this->tpl->assign("projectTypeAvatars", $projectTypeAvatars);
        $this->tpl->assign("currentUrlPath", $currentUrlPath);
        $this->tpl->assign("project", $project);
        $this->tpl->assign("type", "full");
    }
}
