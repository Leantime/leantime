<?php

namespace Leantime\Domain\Menu\Services;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\Setting;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users;

class Menu
{
    use DispatchesEvents;

    private ProjectService $projectService;

    private TimesheetService $timesheetService;

    private SprintService $sprintService;

    private Users $userService;

    private Setting $settingSvc;

    private MenuRepository $menuRepo;

    /**
     * @param  TimesheetRepository  $timesheetsRepo
     * @param  SettingRepository  $settingsRepo
     */
    public function __construct(
        ProjectService $projectService,
        TimesheetService $timesheetService,
        SprintService $sprintService,
        Users $userService,
        Setting $settingSvc,
        MenuRepository $menuRepo
    ) {

        $this->projectService = $projectService;
        $this->timesheetService = $timesheetService;
        $this->sprintService = $sprintService;
        $this->userService = $userService;
        $this->settingSvc = $settingSvc;
        $this->menuRepo = $menuRepo;
    }

    public function getUserProjectList(int $userId, null|int|string $client = null): array
    {

        $allAssignedprojects =
        $allAvailableProjects =
        $recentProjects =
        $returnVars = [];

        $user = $this->userService->getUser($userId);

        $projects = $this->projectService->getProjectHierarchyAssignedToUser($userId, 'open', $client);
        $allAssignedprojects = $projects['allAssignedProjects'];
        $allAssignedprojectsHierarchy = $projects['allAssignedProjectsHierarchy'];
        $favoriteProjects = $projects['favoriteProjects'];

        // Filtered
        $projects = $this->projectService->getProjectHierarchyAvailableToUser($userId, 'open', empty($client) ? session('userdata.clientId') : $client);
        $allAvailableProjects = $projects['allAvailableProjects'];
        $allAvailableProjectsHierarchy = $projects['allAvailableProjectsHierarchy'];

        $clients = $this->projectService->getAllClientsAvailableToUser($userId, 'open', $client);

        $recent = $this->settingSvc->getSetting('usersettings.'.$userId.'.recentProjects');
        $recentArr = safe_unserialize($recent, []);

        // Make sure the suer has access to the project
        if (is_array($recentArr) && is_array($allAvailableProjects)) {
            $availableProjectColumn = array_column($allAvailableProjects, 'id');
            foreach ($recentArr as $recentItem) {
                $found_key = array_search($recentItem, $availableProjectColumn);
                if ($found_key !== false) {
                    $recentProjects[] = $allAvailableProjects[$found_key];
                }
            }
        }

        $projectType = 'project';
        $project = [];
        if ($currentProjectId = $this->projectService->getCurrentProjectId()) {
            $project = $this->projectService->getProject($currentProjectId);

            $projectType = ($project !== false && isset($project['type']))
                ? $project['type']
                : 'project';

            if ($projectType != '' && $projectType != 'project') {
                $menuType = $projectType;
            } else {
                $menuType = \Leantime\Domain\Menu\Repositories\Menu::DEFAULT_MENU;
            }

            if ($project !== false && isset($project['clientId'])) {
                $currentClient = $project['clientId'];
            } else {
                $currentClient = '';
            }
        } else {
            $menuType = \Leantime\Domain\Menu\Repositories\Menu::DEFAULT_MENU;
            $currentClient = '';
        }

        return [
            'assignedProjects' => $allAssignedprojects,
            'availableProjects' => $allAvailableProjects,
            'assignedHierarchy' => $allAssignedprojectsHierarchy,
            'availableProjectsHierarchy' => $allAvailableProjectsHierarchy,
            'currentClient' => $currentClient,
            'menuType' => $menuType,
            'recentProjects' => $recentProjects,
            'projectType' => $projectType,
            'favoriteProjects' => $favoriteProjects,
            'clients' => $clients,
            'currentProject' => $project,

        ];
    }

    public function getProjectTypeAvatars(): array
    {

        $projectTypeAvatars = [
            'project' => 'avatar',
            'strategy' => 'fa fa-chess',
            'program' => 'fa fa-layer-group',
        ];

        return self::dispatch_filter('projectTypeAvatars', $projectTypeAvatars);
    }

    public function getProjectSelectorGroupingOptions(): array
    {

        $projectSelectGrouping =
            [
                'structure' => 'Group by Project Structure',
                'client' => 'Group by Client',
                'none' => 'No Grouping',
            ];

        return self::dispatch_filter('projectSelectorGrouping', $projectSelectGrouping);
    }

    /**
     * Computes the settings link shown in the project selector for a given menu type.
     *
     * Returns the project settings link for project/default menus and an empty
     * link structure for all other menu types.
     *
     * @param  string  $menuType  The resolved menu type (e.g. 'project', 'default', 'personal').
     * @return array<string, string> The settings link template structure.
     */
    public function getProjectSelectorSettingsLink(string $menuType): array
    {
        if ($menuType == 'project' || $menuType == 'default') {
            return [
                'label' => __('menu.project_settings'),
                'module' => 'projects',
                'action' => 'showProject',
                'settingsIcon' => __('menu.project_settings_icon'),
                'settingsTooltip' => __('menu.project_settings_tooltip'),
            ];
        }

        return [
            'label' => '',
            'module' => '',
            'action' => '',
            'settingsIcon' => '',
            'settingsTooltip' => '',
        ];
    }

    /**
     * Rewrites the redirect URL used by the project selector.
     *
     * Requests originating from the project overview ('showProject') are
     * redirected to the dashboard so that switching projects does not keep the
     * user on a project-specific settings page.
     *
     * @param  string  $requestUri  The incoming request URI.
     * @return string The redirect URL to use after a project change.
     */
    public function getProjectSelectorRedirectUrl(string $requestUri): string
    {
        if (str_contains($requestUri, 'showProject')) {
            return '/dashboard/show';
        }

        return $requestUri;
    }

    /**
     * Builds the complete set of template variables for the project selector partial.
     *
     * Persists the project select filter to the user's session, gathers the user's
     * project list (only when a user is logged in), resolves the section menu type
     * and menu structure, and computes the settings link, redirect URL and
     * "start something" new project URL.
     *
     * @param  int|null  $userId  The current user's id, or null when no user is logged in.
     * @param  array{groupBy: string, client: int}  $projectSelectFilter  Group/client filter from the request.
     * @param  string  $currentRoute  The current Frontcontroller route (module.action).
     * @param  string  $requestUri  The incoming request URI.
     * @return array<string, mixed> Flat map of template variable names to values.
     *
     * @api
     */
    public function getProjectSelectorViewData(?int $userId, array $projectSelectFilter, string $currentRoute, string $requestUri): array
    {
        session(['usersettings.projectSelectFilter' => $projectSelectFilter]);

        $allAssignedprojects =
        $allAvailableProjects =
        $recentProjects =
        $favoriteProjects =
        $clients =
        $allAvailableProjectsHierarchy =
        $allAssignedprojectsHierarchy = [];

        $currentClient = '';
        $currentProject = '';
        $projectType = '';
        $menuType = 'project';

        if ($userId !== null) {
            // Getting all projects (ignoring client filter, clients are filtered on the frontend)
            $projectVars = $this->getUserProjectList($userId, $projectSelectFilter['client']);

            $allAssignedprojects = $projectVars['assignedProjects'];
            $allAvailableProjects = $projectVars['availableProjects'];
            $allAvailableProjectsHierarchy = $projectVars['availableProjectsHierarchy'];
            $allAssignedprojectsHierarchy = $projectVars['assignedHierarchy'];
            $currentClient = $projectVars['currentClient'];
            $menuType = $projectVars['menuType'];
            $projectType = $projectVars['projectType'];
            $recentProjects = $projectVars['recentProjects'];
            $favoriteProjects = $projectVars['favoriteProjects'];
            $clients = $projectVars['clients'];
            $currentProject = $projectVars['currentProject'];
        }

        $menuType = $this->menuRepo->getSectionMenuType($currentRoute, $menuType);

        $redirectUrl = $this->getProjectSelectorRedirectUrl($requestUri);

        $settingsLink = $this->getProjectSelectorSettingsLink($menuType);

        $newProjectUrl = self::dispatch_filter('startSomething', BASE_URL.'/projects/newProject');

        return [
            'currentClient' => $currentClient,
            'currentProjectType' => $projectType,
            'allAssignedProjects' => $allAssignedprojects,
            'allAvailableProjects' => $allAvailableProjects,
            'allAvailableProjectsHierarchy' => $allAvailableProjectsHierarchy,
            'projectHierarchy' => $allAssignedprojectsHierarchy,
            'recentProjects' => $recentProjects,
            'currentProject' => $currentProject,
            'menuStructure' => $this->menuRepo->getMenuStructure($menuType) ?? [],
            'menuType' => $menuType,
            'settingsLink' => $settingsLink,
            'redirectUrl' => $redirectUrl,
            'projectTypeAvatars' => $this->getProjectTypeAvatars(),
            'favoriteProjects' => $favoriteProjects,
            'projectSelectGroupOptions' => $this->getProjectSelectorGroupingOptions(),
            'projectSelectFilter' => $projectSelectFilter,
            'clients' => $clients,
            'startSomethingUrl' => $newProjectUrl,
        ];
    }
}
