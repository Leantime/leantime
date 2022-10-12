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

			if(isset($_SESSION['currentProject'])) {
			    $projectRepo = new repositories\projects();
			    $project = $projectRepo->getProject($_SESSION['currentProject']);
				$projectType = $project['projectType'];
			}
            else {
                $projectType = 'generic';
			}
			
            $tpl->assign('current', explode(".", core\frontcontroller::getCurrentRoute()));
            $tpl->assign('allAssignedProjects', $allAssignedprojects);
            $tpl->assign('allAvailableProjects', $allAvailableProjects);
            $tpl->assign('currentProject', $_SESSION['currentProject']);
            $tpl->assign('currentProjectType', $projectType);

            $tpl->assign("ticketMenuLink", $ticketService->getLastTicketViewUrl());

            $tpl->displayPartial('general.menu');

        }

    }
}
