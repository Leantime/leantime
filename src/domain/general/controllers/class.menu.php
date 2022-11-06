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

        public function init()
        {

            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();

        }

        public function run()
        {

            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');

            $allAvailableProjects = $this->projectService->getProjectsUserHasAccessTo($_SESSION['userdata']['id'], 'open', $_SESSION['userdata']['clientId']);

            $this->tpl->assign('current', explode(".", core\frontcontroller::getCurrentRoute()));
            $this->tpl->assign('allAssignedProjects', $allAssignedprojects);
            $this->tpl->assign('allAvailableProjects', $allAvailableProjects);
            $this->tpl->assign('currentProject', $_SESSION['currentProject']);

            $this->tpl->assign("ticketMenuLink", $this->ticketService->getLastTicketViewUrl());

            $this->tpl->displayPartial('general.menu');

        }

    }
}
