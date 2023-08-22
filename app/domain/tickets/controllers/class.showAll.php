<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\services;

    class showAll extends controller
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
            $_SESSION['lastTicketView'] = "table";
            $_SESSION['lastFilterdTicketTableView'] = CURRENT_URL;
        }

        public function get($params)
        {

            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

            $allProjectMilestones = $this->ticketService->getAllMilestones($template_assignments['searchCriteria']);
            $this->tpl->assign('milestones', $allProjectMilestones);

            $this->tpl->display('tickets.showAll');
        }
    }

}
