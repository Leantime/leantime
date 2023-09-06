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

    public function init(
        ProjectService $projectService,
        TicketService $ticketService,
        SettingService $settingSvc,
        MenuRepository $menuRepo,
        IncomingRequestCore $request
    ) {
        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->settingSvc = $settingSvc;
        $this->menuRepo = $menuRepo;
        $this->incomingRequest = $request;
    }

    public function with()
    {
        $allAssignedprojects =
        $allAvailableProjects =
        $recentProjects =
        $returnVars = [];

        if (isset($_SESSION['userdata'])) {
            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser(
                $_SESSION['userdata']['id'],
                'open'
            );

            $allAssignedprojectsHierarchy = $this->projectService->getProjectHierarchyAssignedToUser(
                $_SESSION['userdata']['id'],
                'open'
            );

            $allAvailableProjects = $this->projectService->getProjectsUserHasAccessTo(
                $_SESSION['userdata']['id'],
                'open',
                $_SESSION['userdata']['clientId']
            );

            $recent = $this->settingSvc->getSetting("usersettings." . $_SESSION['userdata']['id'] . ".recentProjects");
            $recentArr = unserialize($recent);

            if (is_array($recentArr) && is_array($allAvailableProjects)) {
                $availableProjectColumn = array_column($allAvailableProjects, 'id');
                foreach ($recentArr as $recentItem) {
                    $found_key = array_search($recentItem, $availableProjectColumn);
                    if ($found_key !== false) {
                        $recentProjects[] = $allAvailableProjects[$found_key];
                    }
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
                $menuType = MenuRepository::DEFAULT_MENU;
            }

            if ($project !== false && isset($project["clientId"])) {
                $currentClient = $project["clientId"];
            } else {
                $currentClient = '';
            }
        } else {
            $menuType = MenuRepository::DEFAULT_MENU;
            $currentClient = '';
        }

        if (str_contains($redirectUrl = $this->incomingRequest->getRequestUri(), 'showProject')) {
            $redirectUrl = '/dashboard/show';
        }

        return [
            'currentClient' => $currentClient,
            'module' => FrontcontrollerCore::getModuleName(),
            'action' => FrontcontrollerCore::getActionName(),
            'currentProjectType' => $projectType,
            'allAssignedProjects' => $allAssignedprojects,
            'allAvailableProjects' => $allAvailableProjects,
            'projectHierarchy' => $allAssignedprojectsHierarchy,
            'recentProjects' => $recentProjects,
            'currentProject' => $_SESSION['currentProject'] ?? null,
            'menuStructure' => $this->menuRepo->getMenuStructure($menuType) ?? [],
            'settingsLink' => [
                'label' => __('menu.project_settings'),
                'module' => 'projects',
                'action' => 'showProject',
                'settingsIcon' => __('menu.project_settings_icon'),
                'settingsTooltip' => __('menu.project_settings_tooltip'),
            ],
            'redirectUrl' => $redirectUrl,
        ];
    }
}
