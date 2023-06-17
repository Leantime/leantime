<?php

namespace leantime\plugins\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;
    use leantime\plugins\repositories\pgmPro\programs;

    class timeline extends controller
    {
        private $projectsRepo;
        private $sprintService;
        private services\tickets $ticketService;
        private $clientRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init()
        {

            $this->projectsRepo = new repositories\projects();
            $this->sprintService = new services\sprints();
            $this->ticketService = new services\tickets();
            $this->clientRepo = new repositories\clients();
            $this->programService = new programs();
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            if (isset($_SESSION["usersettings.showMilestoneTasks"]) && $_SESSION["usersettings.showMilestoneTasks"] === true) {
                $includeTasks = true;
            } else {
                $includeTasks = false;
                $_SESSION["usersettings.showMilestoneTasks"] = false;
            }

            if (isset($_GET['includeTasks']) && $_GET['includeTasks'] == "on") {
                $includeTasks = true;
                $_SESSION["usersettings.showMilestoneTasks"] = true;
            } elseif (isset($_GET['submitIncludeTasks']) && !isset($_GET['includeTasks'])) {
                $includeTasks = false;
                $_SESSION["usersettings.showMilestoneTasks"] = false;
            }


            if (isset($_GET['clientId']) && $_GET['clientId'] !== '') {
                $clientId = $_GET['clientId'];
            }
            else{
                $clientId = false;
            }

            $programId = $_SESSION['currentProject'];
            $allProgramProjects = $this->programService->getAllProgramProjects($programId);


            $allProjectMilestones = $this->ticketService->getAllMilestonesOverview(false, "date", $includeTasks, $clientId);

            $allClients = $this->clientRepo->getAll();

            $this->tpl->assign("includeTasks", $includeTasks);
            $this->tpl->assign('programs', $allProgramProjects);
            $this->tpl->assign('clients', $allClients);
            $this->tpl->display('pgmPro.roadmapAll');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {

            $allProjectMilestones = $this->ticketService->getAllMilestonesOverview();

            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->display('pgmPro.roadmapAll');
        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {
        }
    }

}
