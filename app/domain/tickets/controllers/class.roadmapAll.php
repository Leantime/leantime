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
        private services\clients $clientService;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            repositories\projects $projectsRepo,
            repositories\clients $clientRepo,
            services\clients $clientService,
            services\sprints $sprintService,
            services\tickets $ticketService
        ) {
            $this->projectsRepo = $projectsRepo;
            $this->clientRepo = $clientRepo;
            $this->clientService = $clientService;
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

            $clientId = "";
            $currentClientName = "";
            if (isset($_GET['client']) === true && $_GET['client'] != '') {
                $clientId = (int)$_GET['client'];
                $currentClient = $this->clientRepo->getClient($clientId);
                if (is_array($currentClient) && count($currentClient) > 0) {
                    $currentClientName = $currentClient['name'];
                }
            }

            $allProjectMilestones = $this->ticketService->getAllMilestonesOverview(false, "date", false, $clientId);

            $allClients = $this->clientService->getUserClients($_SESSION['userdata']['id']);

            $this->tpl->assign("currentClientName", $currentClientName);
            $this->tpl->assign("currentClient", $clientId);

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
