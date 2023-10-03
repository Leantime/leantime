<?php

namespace Leantime\Domain\Queue\Repositories {

    use Leantime\Core\Db as DbCore;
    use Leantime\Domain\Users\Repositories\Users as UserRepo;
    use PDO;

    /**
     *
     */

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
         * @param $subject
         * @param $projectId
         * @return void
         */
        /**
         * @param $recipients
         * @param $message
         * @param $subject
         * @param $projectId
         * @return void
         */
        public function queueMessageToUsers($recipients, $message, $subject = "", $projectId = 0)
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
                    return;
                }
        // TODO : exit if no user was found ?
        // Low risk but still it could be deleted in the meantime
                $userId = $theuser['id'];
                $userEmail = $theuser['username'];
                $msghash = md5($thedate . $message . $userEmail);

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindValue(':msghash', $msghash, PDO::PARAM_STR);
                $stmn->bindValue(':channel', 'email', PDO::PARAM_STR);
                $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
                $stmn->bindValue(':subject', $subject, PDO::PARAM_STR);
                $stmn->bindValue(':message', $message, PDO::PARAM_STR);
                $stmn->bindValue(':thedate', $thedate, PDO::PARAM_STR);
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

                $stmn->execute();
                $stmn->closeCursor();
            }
        }

        // TODO later : lists messages per user or per project ?

        /**
         * @param $channel
         * @param $recipients
         * @param $projectId
         * @return array|false
         */
        /**
         * @param $channel
         * @param $recipients
         * @param $projectId
         * @return array|false
         */
        public function listMessageInQueue($channel = 'email', $recipients = null, $projectId = 0)
        {
            $sql = 'SELECT * from zp_queue WHERE channel = :channel ORDER BY userId, projectId ASC, thedate ASC';

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':channel', $channel, PDO::PARAM_STR);
            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $msghashes
         * @return bool
         */
        /**
         * @param $msghashes
         * @return boolean
         */
        public function deleteMessageInQueue($msghashes)
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
    }
}
