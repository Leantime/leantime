<?php

namespace Leantime\Domain\Menu\Composers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Composer;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest as IncomingRequestCore;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;

/**
 *
 */
class Menu extends Composer
{
    use DispatchesEvents;

    public static array $views = [
        'menu::menu',
    ];

    private MenuRepository $menuRepo;
    private IncomingRequestCore $incomingRequest;
    private \Leantime\Domain\Menu\Services\Menu $menuService;

    /**
     * @param MenuRepository                      $menuRepo
     * @param \Leantime\Domain\Menu\Services\Menu $menuService
     * @param IncomingRequestCore                 $request
     * @return void
     */
    public function init(
        MenuRepository $menuRepo,
        \Leantime\Domain\Menu\Services\Menu $menuService,
        IncomingRequestCore $request
    ): void {
        $this->menuRepo = $menuRepo;
        $this->menuService = $menuService;
        $this->incomingRequest = $request;
    }

    /**
     * @param array $data
     * @return array
     * @throws BindingResolutionException
     */
    public function with(): array
    {
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
        $menuType = 'default';

        $projectSelectFilter = session("usersettings.projectSelectFilter") ?? array(
            "groupBy" => "structure",
            "client" => null,
        );

        if (session()->exists("userdata")) {
            //Getting all projects (ignoring client filter, clients are filtered on the frontend)
            $projectVars = $this->menuService->getUserProjectList(session("userdata.id"), $projectSelectFilter["client"]);

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

        $menuType = $this->menuRepo->getSectionMenuType(FrontcontrollerCore::getCurrentRoute(), $menuType);

        if (str_contains($redirectUrl = $this->incomingRequest->getRequestUri(), 'showProject')) {
            $redirectUrl = '/dashboard/show';
        }

        $projectTypeAvatars = $this->menuService->getProjectTypeAvatars();
        $projectSelectGroupOptions = $this->menuService->getProjectSelectorGroupingOptions();

        $settingsLink = [
            'label' => '',
            'module' => '',
            'action' => '',
            'settingsIcon' => '',
            'settingsTooltip' => '',
            ];

        if ($menuType == "project" || $menuType == "default") {
            $settingsLink = [
                'label' => __('menu.project_settings'),
                'module' => 'projects',
                'action' => 'showProject',
                'settingsIcon' => __('menu.project_settings_icon'),
                'settingsTooltip' => __('menu.project_settings_tooltip'),
            ];
        }

        $newProjectUrl = self::dispatch_filter("startSomething", "#/projects/createnew");

        return [
            'currentClient' => $currentClient,
            'module' => FrontcontrollerCore::getModuleName(),
            'action' => FrontcontrollerCore::getActionName(),
            'currentProjectType' => $projectType,
            'allAssignedProjects' => $allAssignedprojects,
            'allAvailableProjects' => $allAvailableProjects,
            'allAvailableProjectsHierarchy' => $allAvailableProjectsHierarchy,
            'projectHierarchy' => $allAssignedprojectsHierarchy,
            'recentProjects' => $recentProjects,
            'currentProject' => $currentProject,
            'menuStructure' => $this->menuRepo->getMenuStructure($menuType ?? '') ?? [],
            'menuType' => $menuType,
            'settingsLink' => $settingsLink,
            'redirectUrl' => $redirectUrl,
            'projectTypeAvatars' => $projectTypeAvatars,
            'favoriteProjects' => $favoriteProjects,
            'projectSelectGroupOptions' => $projectSelectGroupOptions,
            'projectSelectFilter' => $projectSelectFilter,
            'clients' => $clients,
            'startSomethingUrl' => $newProjectUrl,
        ];
    }
}
