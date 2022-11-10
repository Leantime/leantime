<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class roadmap extends controller
    {

        private $projectsRepo;
        private $sprintService;
        private $ticketService;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init()
        {

            $this->projectsRepo = new repositories\projects();
            $this->sprintService = new services\sprints();
            $this->ticketService = new services\tickets();

        }


        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            $allProjectMilestones = $this->ticketService->getAllMilestones($_SESSION['currentProject'], false, "date");

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

            $allProjectMilestones = $this->ticketService->getAllMilestones($_SESSION['currentProject']);

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
