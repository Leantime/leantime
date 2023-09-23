<?php

namespace Leantime\Domain\Menu\Services;

    use Couchbase\UpsertOptions;
    use DateTime;
    use Leantime\Core\Template as TemplateCore;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Core\Environment as EnvironmentCore;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Setting\Services\Setting;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
    use Leantime\Domain\Notifications\Models\Notification as NotificationModel;

    use Leantime\Domain\Users\Services\Users;

    use function Leantime\Domain\Tickets\Services\app;
    use function Leantime\Domain\Tickets\Services\array_sort;

    use const Leantime\Domain\Tickets\Services\BASE_URL;

class Menu
{
    private TemplateCore $tpl;
    private LanguageCore $language;
    private EnvironmentCore $config;
    private ProjectRepository $projectRepository;
    private TicketRepository $ticketRepository;
    private TimesheetRepository $timesheetsRepo;
    private SettingRepository $settingsRepo;
    private ProjectService $projectService;
    private TimesheetService $timesheetService;
    private SprintService $sprintService;
    private Users $userService;
    private Setting $settingSvc;

    public function __construct(
        TemplateCore $tpl,
        LanguageCore $language,
        EnvironmentCore $config,
        ProjectRepository $projectRepository,
        TicketRepository $ticketRepository,
        TimesheetRepository $timesheetsRepo,
        SettingRepository $settingsRepo,
        ProjectService $projectService,
        TimesheetService $timesheetService,
        SprintService $sprintService,
        Users $userService,
        Setting $settingSvc
    ) {
        $this->tpl = $tpl;
        $this->language = $language;
        $this->config = $config;
        $this->projectRepository = $projectRepository;
        $this->ticketRepository = $ticketRepository;
        $this->timesheetsRepo = $timesheetsRepo;
        $this->settingsRepo = $settingsRepo;
        $this->projectService = $projectService;
        $this->timesheetService = $timesheetService;
        $this->sprintService = $sprintService;
        $this->userService = $userService;
        $this->settingSvc = $settingSvc;
    }

    public function getUserProjectList(int $userId): array {

        $allAssignedprojects =
        $allAvailableProjects =
        $recentProjects =
        $returnVars = [];

        $user = $this->userService->getUser($userId);


        $projects = $this->projectService->getProjectHierarchyAssignedToUser($userId,'open');

        $allAssignedprojects = $projects['allAssignedprojects'];
        $allAssignedprojectsHierarchy = $projects['allAssignedprojectsHierarchy'];


        $allAvailableProjects = $this->projectService->getProjectsUserHasAccessTo(
            $userId
        );


        $recent = $this->settingSvc->getSetting("usersettings." . $userId . ".recentProjects");
        $recentArr = unserialize($recent);

        //Make sure the suer has access to the project
        if (is_array($recentArr) && is_array($allAvailableProjects)) {
            $availableProjectColumn = array_column($allAvailableProjects, 'id');
            foreach ($recentArr as $recentItem) {
                $found_key = array_search($recentItem, $availableProjectColumn);
                if ($found_key !== false) {
                    $recentProjects[] = $allAvailableProjects[$found_key];
                }
            }
        }


        $projectType = "project";
        if (isset($_SESSION['currentProject'])) {
            $project = $this->projectService->getProject($_SESSION['currentProject']);

            $projectType = ($project !== false && isset($project['type']))
                ? $project['type']
                : "project";

            if ($projectType != '' && $projectType != 'project') {
                $menuType = $projectType;
            } else {
                $menuType = \Leantime\Domain\Menu\Repositories\Menu::DEFAULT_MENU;
            }

            if ($project !== false && isset($project["clientId"])) {
                $currentClient = $project["clientId"];
            } else {
                $currentClient = '';
            }
        } else {
            $menuType = \Leantime\Domain\Menu\Repositories\Menu::DEFAULT_MENU;
            $currentClient = '';
        }

        return [
            "assignedProjects" => $allAssignedprojects,
            "availableProjects" => $allAvailableProjects,
            "assignedHierarchy" => $allAssignedprojectsHierarchy,
            "currentClient" => $currentClient,
            "menuType" => $menuType,
            "recentProjects" => $recentProjects,
            "projectType" => $projectType,
        ];



    }

}

