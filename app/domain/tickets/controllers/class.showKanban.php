<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\services;

    class showKanban extends controller
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
            $_SESSION['lastTicketView'] = "kanban";
            $_SESSION['lastFilterdTicketKanbanView'] = CURRENT_URL;
        }

        public function get(array $params)
        {

            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

            $this->tpl->assign('allKanbanColumns', $this->ticketService->getKanbanColumns());

            $this->tpl->display('tickets.showKanban');
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
