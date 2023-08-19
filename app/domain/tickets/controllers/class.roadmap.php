<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class roadmap extends controller
    {
        private repositories\projects $projectsRepo;
        private services\sprints $sprintService;
        private services\tickets $ticketService;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            repositories\projects $projectsRepo,
            services\sprints $sprintService,
            services\tickets $ticketService
        ) {
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

            $this->tpl->display('tickets.roadmap');
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
