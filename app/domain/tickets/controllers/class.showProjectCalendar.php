<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;
    use leantime\domain\services;

    class showProjectCalendar extends controller
    {
        private repositories\calendar $calendarRepo;
        private repositories\projects $projectsRepo;
        private services\sprints $sprintService;
        private services\tickets $ticketService;
        private services\projects $projectService;

        /**
         * init - initialize private variables
         */
        public function init(
            services\projects $projectService,
            repositories\calendar $calendarRepo,
            repositories\projects $projectsRepo,
            services\sprints $sprintService,
            services\tickets $ticketService
        ) {
            $this->projectService = $projectService;
            $this->calendarRepo = $calendarRepo;
            $this->projectsRepo = $projectsRepo;
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

            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

            $allProjectMilestones = $this->ticketService->getAllMilestones($template_assignments['searchCriteria']);
            $this->tpl->assign('milestones', $allProjectMilestones);

            $this->tpl->display('tickets.calendar');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {

            $prepareTicketSearchArray = $this->ticketService->prepareTicketSearchArray(["sprint" => '', "type"=> "milestone"]);
            $allProjectMilestones = $this->ticketService->getAllMilestones($prepareTicketSearchArray);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->display('tickets.roadmap');
        }
    }

}
