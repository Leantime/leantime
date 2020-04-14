<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class roadmap
    {

        private $tpl;
        private $projects;
        private $sprintService;
        private $ticketService;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projects = new repositories\projects();
            $this->sprintService = new services\sprints();
            $this->ticketService = new services\tickets();

        }


        /**
         * get - handle get requests
         *
         * @access public
         * @param  paramters or body of the request
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
         * @param  paramters or body of the request
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
         * @param  paramters or body of the request
         */
        public function put($params)
        {

        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function delete($params)
        {

        }

    }

}
