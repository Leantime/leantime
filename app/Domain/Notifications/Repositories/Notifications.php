<?php

namespace Leantime\Domain\Notifications\Repositories {

    use Leantime\Core\Db as DbCore;
    use PDO;

    /**
     *
     */

    /**
     *
     */
    class Notifications
    {
        private DbCore $db;

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }


        /**
         * @param $userId
         * @param $showNewOnly
         * @param $limitStart
         * @param $limitEnd
         * @param $filterOptions
         * @return array|false
         */
        /**
         * @param $userId
         * @param $showNewOnly
         * @param $limitStart
         * @param $limitEnd
         * @param $filterOptions
         * @return array|false
         */
        public function getAllNotifications($userId, $showNewOnly = false, $limitStart = 0, $limitEnd = 100, $filterOptions = array())
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
                LEFT JOIN zp_user ON zp_notifications.authorId = zp_user.id
                WHERE userId = :userId";


            if ($showNewOnly === true) {
                $query .= " AND `read` = '0' ";
            }

            if (is_array($filterOptions) && count($filterOptions) > 0) {
                foreach ($filterOptions as $key => $value) {
                    $query .= " AND " . $key . " = :" . $key . " " ;
                }
            }

            $query .= " ORDER BY datetime DESC
                LIMIT " . $limitStart . ", " . $limitEnd . "";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);

            if (is_array($filterOptions) && count($filterOptions) > 0) {
                foreach ($filterOptions as $key => $value) {
                    $stmn->bindValue(':' . $key, $value, PDO::PARAM_STR);
                }
            }

            $stmn->execute();

            $userNotifications = $stmn->fetchAll();

            return $userNotifications;
        }

        /**
         * @param array $notifications
         * @return bool|void
         */
        /**
         * @param array $notifications
         * @return boolean|void
         */
        public function addNotifications(array $notifications)
        {

            if (count($notifications) == 0) {
                return;
            }

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

            foreach ($notifications as $key => $notif) {
                $sql .= "(
                    :userId" . $key . ",
                    :read" . $key . ",
                    :type" . $key . ",
                    :module" . $key . ",
                    :moduleId" . $key . ",
                    :message" . $key . ",
                    :datetime" . $key . ",
                    :url" . $key . ",
                    :authorId" . $key . "
                ),";
            }

            $sql = substr($sql, 0, -1);

            $stmn = $this->db->database->prepare($sql);

            foreach ($notifications as $key => $notif) {
                $stmn->bindValue(':userId' . $key, $notif['userId'], PDO::PARAM_INT);
                $stmn->bindValue(':read' . $key, 0, PDO::PARAM_INT);
                $stmn->bindValue(':type' . $key, $notif['type'], PDO::PARAM_STR);
                $stmn->bindValue(':module' . $key, $notif['module'], PDO::PARAM_STR);
                $stmn->bindValue(':moduleId' . $key, $notif['moduleId'], PDO::PARAM_INT);
                $stmn->bindValue(':message' . $key, $notif['message'], PDO::PARAM_STR);
                $stmn->bindValue(':datetime' . $key, $notif['datetime'], PDO::PARAM_STR);
                $stmn->bindValue(':url' . $key, $notif['url'], PDO::PARAM_STR);
                $stmn->bindValue(':authorId' . $key, $notif['authorId'], PDO::PARAM_INT);
            }


            $results = $stmn->execute();

            $stmn->closeCursor();

            return $results;
        }

        /**
         * @param $id
         * @return bool
         */
        /**
         * @param $id
         * @return boolean
         */
        public function markNotificationRead($id)
        {

            $sql = "UPDATE zp_notifications SET `read` = 1 WHERE id = :id";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $results = $stmn->execute();

            $stmn->closeCursor();

            return $results;
        }

        /**
         * @param $userId
         * @return bool
         */
        /**
         * @param $userId
         * @return boolean
         */
        public function markAllNotificationRead($userId)
        {

            $sql = "UPDATE zp_notifications SET `read` = 1 WHERE userId = :id";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':id', $userId, PDO::PARAM_INT);

            $results = $stmn->execute();

            $stmn->closeCursor();

            return $results;
        }
    }

}
