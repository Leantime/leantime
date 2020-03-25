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

            $allprojects = $projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');

            $tpl->assign('current', explode(".", core\FrontController::getCurrentRoute()));
            $tpl->assign('allProjects', $allprojects);
            $tpl->assign('currentProject', $_SESSION['currentProject']);


            $tpl->assign("ticketMenuLink", $ticketService->getLastTicketViewUrl());

            $tpl->displayPartial('general.menu');

        }

    }
}
