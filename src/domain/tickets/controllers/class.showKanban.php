<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\services;

    class showKanban
    {
        private $projectService;
        private $tpl;
        private $ticketService;
        private $sprintService;
        private $timesheetService;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->sprintService = new services\sprints();
            $this->timesheetService = new services\timesheets();

            $_SESSION['lastPage'] = CURRENT_URL;
            $_SESSION['lastTicketView'] = "kanban";
            $_SESSION['lastFilterdTicketKanbanView'] = CURRENT_URL;

        }

        public function get(array $params) {

            $currentSprint = $this->sprintService->getCurrentSprintId($_SESSION['currentProject']);

            $searchCriteria = $this->ticketService->prepareTicketSearchArray($params);
            $searchCriteria["orderBy"] = "kanbansort";

            $this->tpl->assign('allTickets', $this->ticketService->getAll($searchCriteria));
            $this->tpl->assign('allTicketStates', $this->ticketService->getStatusLabels());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('types', $this->ticketService->getTicketTypes());
            $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());

            $this->tpl->assign('searchCriteria', $searchCriteria);

            $this->tpl->assign('onTheClock', $this->timesheetService->isClocked($_SESSION["userdata"]["id"]));


            $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));
            $this->tpl->assign('futureSprints', $this->sprintService->getAllFutureSprints($_SESSION["currentProject"]));

            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));
            $this->tpl->assign('milestones', $this->ticketService->getAllMilestones($_SESSION["currentProject"]));

            $this->tpl->assign('currentSprint', $_SESSION["currentSprint"]);
            $this->tpl->assign('allSprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));

            $this->tpl->display('tickets.showKanban');

        }

        public function post(array $params) {

            //QuickAdd
            if(isset($_POST['quickadd']) == true) {

                $result = $this->ticketService->quickAddTicket($params);

                if(isset($result["status"]) ) {
                    $this->tpl->setNotification($result["message"], $result["status"]);
                }
            }

            $this->tpl->redirect(CURRENT_URL);

        }

    }

}


