<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Clients\Services\Clients as ClientService;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;

    /**
     *
     */
    class RoadmapAll extends Controller
    {
        private ProjectRepository $projectsRepo;
        private ClientRepository $clientRepo;
        private SprintService $sprintService;
        private TicketService $ticketService;
        private ClientService $clientService;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            ProjectRepository $projectsRepo,
            ClientRepository $clientRepo,
            ClientService $clientService,
            SprintService $sprintService,
            TicketService $ticketService
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
            $clientId = 0;
            $currentClientName = "";
            if (isset($_GET['client']) === true && $_GET['client'] != '') {
                $clientId = (int)$_GET['client'];
                $currentClient = $this->clientRepo->getClient($clientId);
                if (is_array($currentClient) && count($currentClient) > 0) {
                    $currentClientName = $currentClient['name'];
                }
            }

            $allProjectMilestones = $this->ticketService->getAllMilestonesOverview(false, "date", false, $clientId);

            $allClients = $this->clientService->getUserClients(session("userdata.id"));

            $this->tpl->assign("currentClientName", $currentClientName);
            $this->tpl->assign("currentClient", $clientId);

            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign('clients', $allClients);
            return $this->tpl->display('tickets.roadmapAll');
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
            return $this->tpl->display('tickets.roadmapAll');
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
