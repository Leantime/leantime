<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showMy extends controller
    {
        public function init()
        {

            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->reportService = new services\reports();
            $this->commentService = new services\comments();
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */

        public function get()
        {

            $clientId = "";
            if (isset($_GET['client']) === true && $_GET['client'] != '') {
                $clientId = (int)$_GET['client'];
            }

            $allprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');
            $clients = array();

            $projectResults = array();
            $i = 0;

            if (is_array($allprojects)) {
                foreach ($allprojects as $project) {
                    if (!array_key_exists($project["clientId"], $clients)) {
                        $clients[$project["clientId"]] = $project['clientName'];
                    }

                    if ($clientId == "" || $project["clientId"] == $clientId) {
                        $projectResults[$i] = $project;
                        $projectResults[$i]['progress'] = $this->projectService->getProjectProgress($project['id']);
                        $projectResults[$i]['milestones'] = $this->ticketService->getAllMilestones($project['id']);
                        $projectComment = $this->commentService->getComments("project", $project['id']);

                        if (is_array($projectComment) && count($projectComment) > 0) {
                            $projectResults[$i]['lastUpdate'] = $projectComment[0];
                        } else {
                            $projectResults[$i]['lastUpdate'] = false;
                        }

                        $fullReport = $this->reportService->getRealtimeReport($project['id'], "");

                        $projectResults[$i]['report'] = $fullReport;

                        $i++;
                    }
                }
            }

            $this->tpl->assign("currentClient", $clientId);
            $this->tpl->assign("clients", $clients);
            $this->tpl->assign("allProjects", $projectResults);
            $this->tpl->display('projects.showMy');
        }
    }

}
