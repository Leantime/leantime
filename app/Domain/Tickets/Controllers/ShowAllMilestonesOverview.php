<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Clients\Repositories\Clients;
    use Leantime\Domain\Clients\Services\Clients as ClientService;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class ShowAllMilestonesOverview extends Controller
    {
        private ProjectService $projectService;
        private TicketService $ticketService;
        private SprintService $sprintService;
        private TimesheetService $timesheetService;
        private UserService $userService;
        private ClientService $clientService;
        private Clients $clientRepo;

        /**
         * @param ProjectService   $projectService
         * @param TicketService    $ticketService
         * @param SprintService    $sprintService
         * @param TimesheetService $timesheetService
         * @param UserService      $userService
         * @param ClientService    $clientService
         * @return void
         */
        public function init(
            ProjectService $projectService,
            TicketService $ticketService,
            SprintService $sprintService,
            TimesheetService $timesheetService,
            UserService $userService,
            ClientService $clientService,
            Clients $clientRepo
        ): void {
            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->sprintService = $sprintService;
            $this->timesheetService = $timesheetService;
            $this->userService = $userService;
            $this->clientService = $clientService;
            $this->clientRepo = $clientRepo;

            session(["lastPage" => CURRENT_URL]);
        }

        /**
         * @param $params
         * @return Response
         * @throws \Exception
         */
        public function get($params): Response
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

            $searchCriteria = $this->ticketService->prepareTicketSearchArray($params);

            //Default to not_done tickets to reduce load and make the table easier to read.
            //User can recover by choosing status in the filter box
            //We only want this on the table view
            if ($searchCriteria["status"] == "") {
                $searchCriteria["status"] = "not_done";
            }

            $this->tpl->assign('allTickets', $this->ticketService->getAllMilestonesOverview(false, "duedate", false, $clientId));
            $this->tpl->assign('allTicketStates', $this->ticketService->getStatusLabels());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

            $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());

            $this->tpl->assign('searchCriteria', $searchCriteria);
            $this->tpl->assign('numOfFilters', $this->ticketService->countSetFilters($searchCriteria));

            $allClients = $this->clientService->getUserClients(session("userdata.id"));

            $this->tpl->assign("clients", $allClients);
            $this->tpl->assign("currentClientName", $currentClientName);
            $this->tpl->assign("currentClient", $clientId);


            $this->tpl->assign('users', $this->userService->getAll());
            $this->tpl->assign('milestones', $this->ticketService->getAllMilestonesOverview());

            return $this->tpl->display('tickets.showAllMilestonesOverview');
        }
    }
}
