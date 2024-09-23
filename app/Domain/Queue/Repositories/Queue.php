<?php

namespace Leantime\Domain\Queue\Repositories {

    use Leantime\Core\Db\Db as DbCore;
    use Leantime\Domain\Queue\Workers\Workers;
    use Leantime\Domain\Users\Repositories\Users as UserRepo;
    use PDO;

    /**
     *
     */
    class Queue
    {
        private DbCore $db;
        private UserRepo $users;

        /**
         * @param DbCore   $db
         * @param UserRepo $users
         */
        public function __construct(DbCore $db, UserRepo $users)
        {
            $this->db = $db;
            $this->users = $users;
        }

        /**
         * @param $recipients
         * @param $message
         * @param string     $subject
         * @param int        $projectId
         * @return void
         */
        public function queueMessageToUsers($recipients, $message, string $subject = "", int $projectId = 0): void
        {

            $sql = 'INSERT INTO zp_queue (msghash,channel,userId,subject,message,thedate,projectId) VALUES (:msghash,:channel,:userId,:subject,:message,:thedate,:projectId)';

            $recipients = array_unique($recipients);

            foreach ($recipients as $recipient) {
                $thedate = date('Y-m-d H:i:s');
                // NEW : Allowing recipients to be emails or userIds
                // TODO : Accept a list of \user objects too ?
                if (is_int($recipient)) {
                    $theuser = $this->users->getUser($recipient);
                } elseif (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    $theuser = $this->users->getUserByEmail($recipient);
                } else {
                    //skip invalid users
                    continue;
                }

                //User might not be set because it's a new user
                if (!$theuser) {
                    continue;
                }

                $userId = $theuser['id'];
                $userEmail = $theuser['username'];
                $msghash = md5($thedate . $subject . $message . $userEmail . $projectId);

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindValue(':msghash', $msghash, PDO::PARAM_STR);
                $stmn->bindValue(':channel', Workers::EMAILS->value, PDO::PARAM_STR);
                $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
                $stmn->bindValue(':subject', $subject, PDO::PARAM_STR);
                $stmn->bindValue(':message', $message, PDO::PARAM_STR);
                $stmn->bindValue(':thedate', $thedate, PDO::PARAM_STR);
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

                try {
                    $stmn->execute();
                } catch (\PDOException  $e) {
                   report($e);
                }

                $stmn->closeCursor();
            }
        }

        // TODO later : lists messages per user or per project ?

        /**
         * @param string     $channel
         * @param $recipients
         * @param int        $projectId
         * @return array|false
         */
        public function listMessageInQueue(Workers $channel, $recipients = null, int $projectId = 0): false|array
        {
            $sql = 'SELECT
                    *
                FROM zp_queue
                WHERE channel = :channel ORDER BY userId, projectId ASC, thedate ASC';

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':channel', $channel->value, PDO::PARAM_STR);
            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $msghashes
         * @return bool
         */
        public function deleteMessageInQueue($msghashes): bool
        {
            // NEW : Allowing one hash or an array of them
            if (is_string($msghashes)) {
                $thehashes = array($msghashes);
            } else {
                $thehashes = $msghashes;
            }

            $sql = 'DELETE FROM zp_queue WHERE msghash=:msghash';

            $stmn = $this->db->database->prepare($sql);
            foreach ($thehashes as $msghash) {
                $stmn->bindValue(':msghash', $msghash, PDO::PARAM_STR);
                $result = $stmn->execute();
            }
            $stmn->closeCursor();

            return $result;
        }


        /**
         * @param $recipients
         * @param $message
         * @param string     $subject
         * @param int        $projectId
         * @return void
         */
        public function addMessageToQueue($channel, $subject, $message, $userId, int $projectId = 0): void
        {

            $sql = 'INSERT INTO zp_queue
                        (msghash,channel,userId,subject,message,thedate,projectId)
                    VALUES
                        (:msghash,:channel,:userId,:subject,:message,:thedate,:projectId)';

            $thedate = date('Y-m-d H:i:s');
            $msghash = md5($thedate . $subject . $message . $projectId);

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':msghash', $msghash, PDO::PARAM_STR);
            $stmn->bindValue(':channel', $channel->value, PDO::PARAM_STR);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmn->bindValue(':subject', $subject, PDO::PARAM_STR);
            $stmn->bindValue(':message', $message, PDO::PARAM_STR);
            $stmn->bindValue(':thedate', $thedate, PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            try {
                $stmn->execute();
            } catch (\PDOException  $e) {
                report($e);
            }

            $stmn->closeCursor();

        }

    }
}
