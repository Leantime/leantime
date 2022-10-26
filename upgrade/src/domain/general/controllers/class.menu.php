<?php
namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class menu
    {

        public function run()
        {

            $tpl = new core\template();

            $projectService = new services\projects();
            $ticketService = new services\tickets();

            $allAssignedprojects = $projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');

            $allAvailableProjects = $projectService->getProjectsUserHasAccessTo($_SESSION['userdata']['id'], 'open', $_SESSION['userdata']['clientId']);

            $tpl->assign('current', explode(".", core\frontcontroller::getCurrentRoute()));
            $tpl->assign('allAssignedProjects', $allAssignedprojects);
            $tpl->assign('allAvailableProjects', $allAvailableProjects);
            $tpl->assign('currentProject', $_SESSION['currentProject']);

            $tpl->assign("ticketMenuLink", $ticketService->getLastTicketViewUrl());

            $tpl->displayPartial('general.menu');

        }

    }
}
