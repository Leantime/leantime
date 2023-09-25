<?php

namespace Leantime\Domain\Menu\Composers;

use Leantime\Core\IncomingRequest as IncomingRequestCore;
use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Composer;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;

class Menu extends Composer
{
    public static $views = [
        'menu::menu',
    ];

    private ProjectService $projectService;
    private TicketService $ticketService;
    private SettingService $settingSvc;
    private MenuRepository $menuRepo;
    private IncomingRequestCore $incomingRequest;
    private \Leantime\Domain\Menu\Services\Menu $menuService;

    public function init(
        ProjectService $projectService,
        TicketService $ticketService,
        SettingService $settingSvc,
        MenuRepository $menuRepo,
        \Leantime\Domain\Menu\Services\Menu $menuService,
        IncomingRequestCore $request
    ) {
        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->settingSvc = $settingSvc;
        $this->menuRepo = $menuRepo;
        $this->menuService = $menuService;
        $this->incomingRequest = $request;
    }

    public function with()
    {
        $allAssignedprojects =
        $allAvailableProjects =
        $recentProjects =
        $returnVars = [];

        if(isset($_SESSION['userdata']["projectSelectFilter"])){
            $projectSelectFilter = $_SESSION['userdata']["projectSelectFilter"];
        } else {
            $projectSelectFilter = array(
                "groupBy" => "none",
                "clients" => ''
            );
        }

        if (isset($_SESSION['userdata'])) {

            $projectVars = $this->menuService->getUserProjectList($_SESSION['userdata']['id']);

            $allAssignedprojects = $projectVars['assignedProjects'];
            $allAvailableProjects  = $projectVars['availableProjects'];
            $allAvailableProjectsHierarchy  = $projectVars['availableProjectsHierarchy'];
            $allAssignedprojectsHierarchy  = $projectVars['assignedHierarchy'];
            $currentClient  = $projectVars['currentClient'];
            $menuType  = $projectVars['menuType'];
            $projectType  = $projectVars['projectType'];
            $recentProjects  = $projectVars['recentProjects'];
            $favoriteProjects = $projectVars['favoriteProjects'];
            $clients = $projectVars['clients'];
            $currentProject = $projectVars['currentProject'];

        }

        if (str_contains($redirectUrl = $this->incomingRequest->getRequestUri(), 'showProject')) {
            $redirectUrl = '/dashboard/show';
        }

        $projectTypeAvatars = $this->menuService->getProjectTypeAvatars();
        $projectSelectGroupOptions = $this->menuService->getProjectSelectorGroupingOptions();


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
            'menuStructure' => $this->menuRepo->getMenuStructure($menuType) ?? [],
            'settingsLink' => [
                'label' => __('menu.project_settings'),
                'module' => 'projects',
                'action' => 'showProject',
                'settingsIcon' => __('menu.project_settings_icon'),
                'settingsTooltip' => __('menu.project_settings_tooltip'),
            ],
            'redirectUrl' => $redirectUrl,
            'projectTypeAvatars' => $projectTypeAvatars,
            'favoriteProjects' => $favoriteProjects,
            'projectSelectGroupOptions' => $projectSelectGroupOptions,
            'projectSelectFilter' => $projectSelectFilter,
            'clients' => $clients
        ];
    }
}
