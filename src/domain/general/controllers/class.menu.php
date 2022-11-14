<?php
namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class menu extends controller
    {

        private $projectService;
        private $ticketService;
        private $menuRepo;
        private $projectRepo;

        public function init()
        {

            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->menuRepo = new repositories\menu();
            $this->projectRepo = new repositories\projects();

        }

        public function run()
        {

            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');

            $allAvailableProjects = $this->projectService->getProjectsUserHasAccessTo($_SESSION['userdata']['id'], 'open', $_SESSION['userdata']['clientId']);

			if(isset($_SESSION['currentProject'])) {
			    $project = $this->projectRepo->getProject($_SESSION['currentProject']);

                $menuType = ($project !== false && isset($project['menuType']))
                    ? $project['menuType']
                    : repositories\menu::DEFAULT_MENU;

			}
            else {

                $menuType = repositories\menu::DEFAULT_MENU;

			}

            $this->tpl->assign('current', explode(".", core\frontcontroller::getCurrentRoute()));
            $this->tpl->assign('allAssignedProjects', $allAssignedprojects);
            $this->tpl->assign('allAvailableProjects', $allAvailableProjects);
            $this->tpl->assign('currentProject', $_SESSION['currentProject']);
			$this->tpl->assign('menuStructure', $this->menuRepo->getMenuStructure($menuType));

            $this->tpl->displayPartial('general.menu');

        }

    }
}
