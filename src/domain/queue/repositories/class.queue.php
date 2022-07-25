<?php
namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class queue
    {

        public function __construct()
        {

            $this->db = core\db::getInstance();
        }

        public function queueMessageToUsers($recipients,$message, $subject = "", $projectId = 0)
        {

            $sql = 'INSERT INTO zp_queue (msghash,recipient,message,thedate,projectId) VALUES (:msghash,:recipient,:message,:thedate,:projectId)';

            $recipients = array_unique($recipients);

	    foreach ($recipients as $recipient) {

                date_default_timezone_set('Europe/Paris');
                $thedate=date('Y-m-d H:i:s');
                $msghash=md5($thedate.$message.$recipient);

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindValue(':msghash', $msghash, PDO::PARAM_STR);
                $stmn->bindValue(':recipient', $recipient, PDO::PARAM_STR);
                $stmn->bindValue(':message', $message, PDO::PARAM_STR);
                $stmn->bindValue(':thedate', $thedate, PDO::PARAM_STR);
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

                $stmn->execute();
       	        $stmn->closeCursor();

	    }

        }

	public function listMessageInQueue($recipients = null, $projectId = 0)
        {
            $sql = 'SELECT * from zp_queue ORDER BY recipient, projectId ASC, thedate ASC';

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

	public function deleteMessageInQueue($msghashes)
        {
            $sql = 'DELETE FROM zp_queue WHERE msghash=:msghash';

            $stmn = $this->db->database->prepare($sql);
	    foreach ($msghashes as $msghash)
            {
                $stmn->bindValue(':msghash', $msghash, PDO::PARAM_STR);
                $result=$stmn->execute();
            }
            $stmn->closeCursor();

            return $result;

        }

    }
}
