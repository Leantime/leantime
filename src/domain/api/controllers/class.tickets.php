<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class tickets
    {

        private $tpl;
        private $projects;
        private $sprintService;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projects = new repositories\projects();
            $this->ticketsApiService = new services\tickets();

        }


        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {

        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {

        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            $results = $this->ticketsApiService->patchTicket($params['id'], $params);

            if($results === true) {
                echo "{status:ok}";
            }else{
                echo "{status:failure}";
            }
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function delete($params)
        {

        }

    }

}
