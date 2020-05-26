<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showMy
    {

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->reportService = new services\reports();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */

        public function get()
        {

            $clientId = "";
            if (isset($_GET['client']) === true) {
                $clientId = (int)$_GET['client'];
            }

            $allprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');
            $clients = array();

            $projectResults = array();
            $i = 0;
            foreach ($allprojects as $project) {


                if (!array_key_exists($project["clientId"], $clients)) {
                    $clients[$project["clientId"]] = $project['clientName'];
                }

                if ($clientId == "" || $project["clientId"] == $clientId) {

                    $projectResults[$i] = $project;
                    $projectResults[$i]['progress'] = $this->projectService->getProjectProgress($project['id']);
                    $projectResults[$i]['milestones'] = $this->ticketService->getAllMilestones($project['id']);


                    $fullReport = $this->reportService->getRealtimeReport($project['id'], "");

                    $projectResults[$i]['report'] = $fullReport;

                    $i++;

                }

            }

            $this->tpl->assign("currentClient", $clientId);
            $this->tpl->assign("clients", $clients);
            $this->tpl->assign("allProjects", $projectResults);
            $this->tpl->display('projects.showMy');

        }

    }

}
