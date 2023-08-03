<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class roadmapAll extends controller
    {
        private repositories\projects $projectsRepo;
        private repositories\clients $clientRepo;
        private services\sprints $sprintService;
        private services\tickets $ticketService;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            repositories\projects $projectsRepo,
            repositories\clients $clientRepo,
            services\sprints $sprintService,
            services\tickets $ticketService
        ) {
            $this->projectsRepo = $projectsRepo;
            $this->clientRepo = $clientRepo;
            $this->sprintService = $sprintService;
            $this->ticketService = $ticketService;
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
            } else {
                $clientId = false;
            }


            $allProjectMilestones = $this->ticketService->getAllMilestonesOverview(false, "date", $includeTasks, $clientId);

            $allClients = $this->clientRepo->getAll();

            $this->tpl->assign("includeTasks", $includeTasks);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign('clients', $allClients);
            $this->tpl->display('tickets.roadmapAll');
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
            $this->tpl->display('tickets.roadmapAll');
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
