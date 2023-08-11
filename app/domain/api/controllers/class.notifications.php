<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class notifications extends controller
    {
        public services\notifications $notificationsService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(services\notifications $notificationsService)
        {
            $this->notificationsService = $notificationsService;
        }


        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
            $notifications = $this->notificationsService->getAllNotifications($params['userId'], $params['read']);

            echo json_encode($notifications);
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
            if (isset($params['action']) && $params['action'] == "read") {
                $this->notificationsService->markNotificationRead($params['id'], $_SESSION['userdata']['id']);
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
