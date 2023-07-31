<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;
    use leantime\domain\models\auth\roles;

    class calendar extends controller
    {
        private services\calendar $calendarSvc;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(services\calendar $calendarSvc)
        {
            $this->calendarSvc = $calendarSvc;
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
            echo "{status:'Not implemented'}";
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {
            echo "{status:'Not implemented'}";
        }

        /**
         * patch - handle patch requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            if (services\auth::userIsAtLeast(roles::$editor)) {
                $results = false;

                if (isset($params['id'])) {
                    $results = $this->calendarSvc->patch($params['id'], $params);
                } else {
                    echo "{status:failure, message: 'ID not set'}";
                }

                if ($results === true) {
                    echo "{status:ok}";
                } else {
                    echo "{status:failure}";
                }
            } else {
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
            echo "{status:'Not implemented'}";
        }
    }

}
