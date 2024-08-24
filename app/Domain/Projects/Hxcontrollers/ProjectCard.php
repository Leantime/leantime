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

class ProjectCard extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'projects::partials.projectCard';

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

    public function get()
    {
    }

    public function toggleFavorite()
    {

            $projectData = $this->incomingRequest->request->all();

            $projectId = $projectData['projectId'];
            $isFavorite = $projectData['isFavorite'];

            $project = $this->projectsService->getProject($projectId);

        if ($isFavorite) {
            $this->reactionService->removeReaction(
                userId: session("userdata.id"),
                module: "project",
                moduleId: $projectId,
                reaction: "favorite"
            );
        } else {
            $this->reactionService->addReaction(
                userId: session("userdata.id"),
                module: "project",
                moduleId: $projectId,
                reaction: "favorite"
            );
        }

        $this->setHTMXEvent("HTMX.updateProjectList");

        $project = $this->projectsService->getProject($projectId);
        $this->tpl->assign("project", $project);
    }

    public function getProgress() {

        $projectId = $_GET['projectId'];

        $project = array("id" => $projectId);

        $project['progress'] = $this->projectsService->getProjectProgress($project['id']);
        $projectComment = $this->commentsService->getComments("project", $project['id']);
        $project['team'] = $this->projectsService->getUsersAssignedToProject($project['id']);


        if (is_array($projectComment) && count($projectComment) > 0) {
            $project['lastUpdate'] = $projectComment[0];
        } else {
            $project['lastUpdate'] = false;
        }


        $projectTypeAvatars  = $this->menuService->getProjectTypeAvatars();

        $currentUrlPath = BASE_URL . "/" . str_replace(".", "/", Frontcontroller::getCurrentRoute());

        $project = $this->projectsService->getProject($projectId);

        $this->tpl->assign("projectTypeAvatars", $projectTypeAvatars);
        $this->tpl->assign("currentUrlPath", $currentUrlPath);
        $this->tpl->assign("project", $project);
        $this->tpl->assign("type", "full");
    }
}
