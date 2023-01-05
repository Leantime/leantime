<?php

namespace leantime\domain\services {

    use leantime\core;
    use pdo;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class notifications
    {


        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();
            $this->notificationsRepo = new repositories\notifications();

        }


        public function getAllNotifications($userId, $showNewOnly = 0, $limitStart = 0, $limitEnd = 100)
        {

            return $this->notificationsRepo->getAllNotifications($userId, $showNewOnly, $limitStart, $limitEnd);

        }

        public function addNotifications(array $notifications){

            return $this->notificationsRepo->addNotifications($notifications);

        }

        public function markNotificationRead($id) {

            return $this->notificationsRepo->markNotificationRead($id);

        }


    }

}
