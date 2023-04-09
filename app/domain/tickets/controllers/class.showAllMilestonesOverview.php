<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\services;

    class showAllMilestonesOverview extends controller
    {
        private $projectService;
        private services\tickets $ticketService;
        private $sprintService;
        private $timesheetService;
        private $userService;

        public function init()
        {

            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->sprintService = new services\sprints();
            $this->timesheetService = new services\timesheets();
            $this->userService = new services\users();

            $_SESSION['lastPage'] = CURRENT_URL;
        }

        public function get($params)
        {

            $searchCriteria = $this->ticketService->prepareTicketSearchArray($params);

            //Default to not_done tickets to reduce load and make the table easier to read.
            //User can recover by choosing status in the filter box
            //We only want this on the table view
            if ($searchCriteria["status"] == "") {
                $searchCriteria["status"] = "not_done";
            }

            $this->tpl->assign('allTickets', $this->ticketService->getAllMilestonesOverview(true));
            $this->tpl->assign('allTicketStates', $this->ticketService->getStatusLabels());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

            $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());

            $this->tpl->assign('searchCriteria', $searchCriteria);
            $this->tpl->assign('numOfFilters', $this->ticketService->countSetFilters($searchCriteria));

            $this->tpl->assign('users', $this->userService->getAll());
            $this->tpl->assign('milestones', $this->ticketService->getAllMilestonesOverview());

            $this->tpl->display('tickets.showAllMilestonesOverview');
        }
    }

}
