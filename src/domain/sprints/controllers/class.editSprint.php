<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use \DateTime;
    use \DateInterval;


    class editSprint
    {

        private $tpl;
        private $projects;
        private $sprintService;

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
            $this->language = new core\language();

        }

        /**
         * get - handle get requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function get($params)
        {
            if(isset($params['id'])) {
                $sprint = $this->sprintService->getSprint($params['id']);
            }else{
                $sprint = new models\sprints();
                $startDate = new DateTime();
                $endDate = new DateTime();
                $endDate = $endDate->add(new DateInterval("P13D"));
                $sprint->startDate = $startDate->format($this->language->__("language.dateformat"));
                $sprint->endDate = $endDate->format($this->language->__("language.dateformat"));

            }

            $this->tpl->assign('sprint', $sprint);
            $this->tpl->displayPartial('sprints.sprintdialog');
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function post($params)
        {
            //If ID is set its an update

            if($params['startDate'] == '' || $params['endDate'] == '') {
                $this->tpl->setNotification("First day and last day are required", "error");

                $this->tpl->assign('sprint', (object) $params);
                $this->tpl->displayPartial('sprints.sprintdialog');

                return;

            }

            if(isset($_GET['id']) && $_GET['id'] > 0) {

                $params['id'] = (int)$_GET['id'];

                if ($this->sprintService->editSprint($params) == true) {

                    $this->tpl->setNotification("Sprint edited successfully", "success");

                } else {

                    $this->tpl->setNotification("There was a problem saving the sprint", "error");

                }

            }else{

                if ($this->sprintService->addSprint($params) == true) {

                    $this->tpl->setNotification("Sprint created successfully. <br /> Go to the <a href='".BASE_URL."/tickets/showAll'>Backlog</a> to add To-Dos", "success");

                } else {

                    $this->tpl->setNotification("There was a problem saving the sprint", "error");

                }

            }
            $this->tpl->assign('sprint', (object) $params);
            $this->tpl->displayPartial('sprints.sprintdialog');
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