<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\services;

    class showAllMilestonesOverview extends controller
    {
        private services\projects $projectService;
        private services\tickets $ticketService;
        private services\sprints $sprintService;
        private services\timesheets $timesheetService;
        private services\users $userService;
        private services\clients $clientService;

        public function init(
            services\projects $projectService,
            services\tickets $ticketService,
            services\sprints $sprintService,
            services\timesheets $timesheetService,
            services\users $userService,
            services\clients $clientService
        ) {
            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->sprintService = $sprintService;
            $this->timesheetService = $timesheetService;
            $this->userService = $userService;
            $this->clientService = $clientService;

            $_SESSION['lastPage'] = CURRENT_URL;
        }

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

            $searchCriteria = $this->ticketService->prepareTicketSearchArray($params);

            //Default to not_done tickets to reduce load and make the table easier to read.
            //User can recover by choosing status in the filter box
            //We only want this on the table view
            if ($searchCriteria["status"] == "") {
                $searchCriteria["status"] = "not_done";
            }

            $this->tpl->assign('allTickets', $this->ticketService->getAllMilestonesOverview(false, "duedate",  false, $clientId));
            $this->tpl->assign('allTicketStates', $this->ticketService->getStatusLabels());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

            $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());

            $this->tpl->assign('searchCriteria', $searchCriteria);
            $this->tpl->assign('numOfFilters', $this->ticketService->countSetFilters($searchCriteria));

            $allClients = $this->clientService->getUserClients($_SESSION['userdata']['id']);

            $this->tpl->assign("clients", $allClients);
            $this->tpl->assign("currentClientName", $currentClientName);
            $this->tpl->assign("currentClient", $clientId);


            $this->tpl->assign('users', $this->userService->getAll());
            $this->tpl->assign('milestones', $this->ticketService->getAllMilestonesOverview());

            $this->tpl->display('tickets.showAllMilestonesOverview');
        }
    }

}
