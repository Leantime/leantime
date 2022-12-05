<?php

namespace leantime\domain\repositories {

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

        }


        public function getAllNotifications($userId, $showNewOnly = false, $limitStart = 0, $limitEnd = 100)
        {

            $query = "SELECT

                      zp_notifications.id,
                      `userId`,
                      `read`,
                      `type`,
                      `module`,
                      `moduleId`,
                      `datetime`,
                      `url`,
                      `message`,
                      `authorId`,
                      zp_user.firstname,
                      zp_user.lastname
                FROM zp_notifications
                LEFT JOIN zp_user ON zp_notifications.authorId = zp_user.id";


            if($showNewOnly === true) {
                $query .= " WHERE `read` = '0' ";
            }

            $query.=" ORDER BY datetime DESC
                LIMIT ".$limitStart.", ".$limitEnd."";

            $stmn = $this->db->database->prepare($query);

            $stmn->execute();

            $userNotifications= $stmn->fetchAll();

            return $userNotifications;

        }

        public function addNotifications(array $notifications){

            $sql = "INSERT INTO zp_notifications (
                    `userId`,
                    `read`,
                    `type`,
                    `module`,
                    `moduleId`,
                    `message`,
                    `datetime`,
                    `url`,
                    `authorId`
            ) VALUES ";

            foreach($notifications as $key => $notif) {
                $sql .= "(
                    :userId".$key.",
                    :read".$key.",
                    :type".$key.",
                    :module".$key.",
                    :moduleId".$key.",
                    :message".$key.",
                    :datetime".$key.",
                    :url".$key.",
                    :authorId".$key."
                ),";
            }

            $sql = substr($sql, 0, -1);

            $stmn = $this->db->database->prepare($sql);

            foreach($notifications as $key => $notif) {
                $stmn->bindValue(':userId'.$key, $notif['userId'], PDO::PARAM_INT);
                $stmn->bindValue(':read'.$key, 0, PDO::PARAM_INT);
                $stmn->bindValue(':type'.$key, $notif['type'], PDO::PARAM_STR);
                $stmn->bindValue(':module'.$key, $notif['module'], PDO::PARAM_STR);
                $stmn->bindValue(':moduleId'.$key, $notif['moduleId'], PDO::PARAM_INT);
                $stmn->bindValue(':message'.$key, $notif['message'], PDO::PARAM_STR);
                $stmn->bindValue(':datetime'.$key, $notif['datetime'], PDO::PARAM_STR);
                $stmn->bindValue(':url'.$key, $notif['url'], PDO::PARAM_STR);
                $stmn->bindValue(':authorId'.$key, $notif['authorId'], PDO::PARAM_INT);
            }


            $results = $stmn->execute();

            $stmn->closeCursor();

            return $results;

        }

        public function markNotificationRead($id) {

            $sql = "UPDATE zp_notifications SET `read` = 1 WHERE id = :id";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $results = $stmn->execute();

            $stmn->closeCursor();

            return $results;
        }


    }

}
