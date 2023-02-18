<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\services;

    class showList extends controller
    {
        private services\projects $projectService;
        private services\tickets $ticketService;
        private services\sprints $sprintService;
        private $timesheetService;

        public function init()
        {

            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->sprintService = new services\sprints();
            $this->timesheetService = new services\timesheets();

            $_SESSION['lastPage'] = CURRENT_URL;
            $_SESSION['lastTicketView'] = "list";
            $_SESSION['lastFilterdTicketListView'] = CURRENT_URL;
        }

        public function get($params)
        {

            $currentSprint = $this->sprintService->getCurrentSprintId($_SESSION['currentProject']);

            $params["orderBy"] = "date";
            $searchCriteria = $this->ticketService->prepareTicketSearchArray($params);

            $this->tpl->assign('allTickets', $this->ticketService->getAll($searchCriteria));
            $this->tpl->assign('allTicketStates', $this->ticketService->getStatusLabels());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());
            $this->tpl->assign('types', $this->ticketService->getTicketTypes());
            $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());

            $this->tpl->assign('searchCriteria', $searchCriteria);
            $this->tpl->assign('numOfFilters', $this->ticketService->countSetFilters($searchCriteria));

            $this->tpl->assign('onTheClock', $this->timesheetService->isClocked($_SESSION["userdata"]["id"]));

            $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));
            $this->tpl->assign('futureSprints', $this->sprintService->getAllFutureSprints($_SESSION["currentProject"]));

            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));
            $this->tpl->assign('milestones', $this->ticketService->getAllMilestones($_SESSION["currentProject"]));

            $this->tpl->assign('currentSprint', $_SESSION["currentSprint"]);
            $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));

            // fields
            $this->tpl->assign('groupBy', $this->ticketService->getGroupByFieldOptions());
            $this->tpl->assign('newField', $this->ticketService->getNewFieldOptions());

            $this->tpl->display('tickets.showList');
        }

        public function post(array $params)
        {

            //QuickAdd
            if (isset($_POST['quickadd']) == true) {
                $result = $this->ticketService->quickAddTicket($params);

                if (is_array($result)) {
                    $this->tpl->setNotification($result["message"], $result["status"]);
                }
            }

            $this->tpl->redirect(CURRENT_URL);
        }
    }

}
