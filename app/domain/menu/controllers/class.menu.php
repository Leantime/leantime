<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class menu extends controller
    {
        private services\projects $projectService;
        private services\tickets $ticketService;
        private repositories\menu $menuRepo;

        private services\setting $settingSvc;


        public function init()
        {

            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->menuRepo = new repositories\menu();
            $this->settingSvc = new services\setting();

        }

        public function run()
        {

            $allAssignedprojects = array();
            $allAvailableProjects = array();
            $recentProjects = array();

            if (isset($_SESSION['userdata'])) {
                $allAssignedprojects = $this->projectService->getProjectsAssignedToUser(
                    $_SESSION['userdata']['id'],
                    'open'
                );

                $allAvailableProjects = $this->projectService->getProjectsUserHasAccessTo(
                    $_SESSION['userdata']['id'],
                    'open',
                    $_SESSION['userdata']['clientId']
                );

                $_SESSION['menuState'] = $this->menuRepo->getSubmenuState("mainMenu");

                $recent = $this->settingSvc->getSetting("usersettings." . $_SESSION['userdata']['id'] . ".recentProjects");
                $recentArr = unserialize($recent);

                if(is_array($recentArr)) {
                    $availableProjectColumn = array_column($allAvailableProjects, 'id');
                    foreach ($recentArr as $recentItem) {
                        $found_key = array_search($recentItem, $availableProjectColumn);
                        if ($found_key !== false) {
                            $recentProjects[] = $allAvailableProjects[$found_key];
                        }
                    }
                }
            }

            if (isset($_SESSION['currentProject'])) {
                $project = $this->projectService->getProject($_SESSION['currentProject']);

                $menuType = ($project !== false && isset($project['menuType']))
                    ? $project['menuType']
                    : repositories\menu::DEFAULT_MENU;

                $this->tpl->assign('currentClient', $project["clientId"]);
            } else {
                $menuType = repositories\menu::DEFAULT_MENU;
                $this->tpl->assign('currentClient', "");
            }

            $this->tpl->assign('current', explode(".", core\frontcontroller::getCurrentRoute()));
            $this->tpl->assign('allAssignedProjects', $allAssignedprojects);
            $this->tpl->assign('allAvailableProjects', $allAvailableProjects);
            $this->tpl->assign('recentProjects', $recentProjects);
            $this->tpl->assign('currentProject', $_SESSION['currentProject']);
            $this->tpl->assign('menuStructure', $this->menuRepo->getMenuStructure($menuType));

            $this->tpl->displayPartial('menu.menu');
        }
    }
}
