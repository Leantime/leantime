<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\services;

    class showAllMilestones extends controller
    {
        private services\projects $projectService;
        private services\tickets $ticketService;
        private services\sprints $sprintService;
        private services\timesheets $timesheetService;

        public function init(
            services\projects $projectService,
            services\tickets $ticketService,
            services\sprints $sprintService,
            services\timesheets $timesheetService
        ) {
            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->sprintService = $sprintService;
            $this->timesheetService = $timesheetService;

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

            $prepareTicketSearchArray = $this->ticketService->prepareTicketSearchArray(["sprint" => '', "type"=> "milestone"]);
            $allProjectMilestones = $this->ticketService->getAllMilestones($prepareTicketSearchArray);
            $this->tpl->assign('allTickets', $allProjectMilestones);

            $this->tpl->assign('allTicketStates', $this->ticketService->getStatusLabels());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

            $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());

            $this->tpl->assign('searchCriteria', $searchCriteria);
            $this->tpl->assign('numOfFilters', $this->ticketService->countSetFilters($searchCriteria));

            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));
            $prepareTicketSearchArray = $this->ticketService->prepareTicketSearchArray(["sprint" => '', "type"=> "milestone"]);
            $allProjectMilestones = $this->ticketService->getAllMilestones($prepareTicketSearchArray);
            $this->tpl->assign('milestones', $allProjectMilestones);

            $this->tpl->display('tickets.showAllMilestones');
        }
    }

}
