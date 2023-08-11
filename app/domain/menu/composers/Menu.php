<?php

namespace leantime\domain\composers\menu;

use leantime\core;
use leantime\core\Composer;
use leantime\domain\services;
use leantime\domain\repositories;

class Menu extends Composer
{
    public static $views = [
        'menu::menu',
    ];

    private services\projects $projectService;
    private services\tickets $ticketService;
    private services\setting $settingSvc;
    private repositories\menu $menuRepo;
    private core\IncomingRequest $incomingRequest;

    public function init(
        services\projects $projectService,
        services\tickets $ticketService,
        services\setting $settingSvc,
        repositories\menu $menuRepo,
        core\IncomingRequest $request
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

            $menuType = ($project !== false && isset($project['menuType']))
                ? $project['menuType']
                : repositories\menu::DEFAULT_MENU;

            $projectType = ($project !== false && isset($project['type']))
                ? $project['type']
                : "project";

            if ($projectType != '' && $projectType != 'project') {
                $menuType = $projectType;
            }

            if ($project !== false && isset($project["clientId"])) {
                $currentClient = $project["clientId"];
            } else {
                $currentClient = '';
            }
        } else {
            $menuType = repositories\menu::DEFAULT_MENU;
            $currentClient = '';
        }

        [$module, $action] = explode(".", core\frontcontroller::getCurrentRoute());

        if (str_contains($redirectUrl = $this->incomingRequest->getRequestURI(BASE_URL), 'showProject')) {
            $redirectUrl = '/dashboard/show';
        }

        return [
            'currentClient' => $currentClient,
            'module' => $module ?? '',
            'action' => $action ?? '',
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
