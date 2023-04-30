<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class reactions extends controller
    {
        private services\reactions $reactionsService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init()
        {

            $this->reactionsService = new services\reactions();
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
            if($params["action"] == "add") {
               return $this->reactionsService->addReaction($_SESSION['userdata']['id'], $params['module'], $params['moduleId'], $params['reaction']);

            }

            if($params["action"] == "remove") {
                return $this->reactionsService->removeReaction($_SESSION['userdata']['id'], $params['module'], $params['moduleId'], $params['reaction']);

            }


        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
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
