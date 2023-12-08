<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Notifications extends Controller
    {
        public NotificationService $notificationsService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(NotificationService $notificationsService)
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

            return $this->tpl->displayJson($notifications);
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

            return new Response();
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
