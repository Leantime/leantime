<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class timer
    {

        public function __construct()
        {

            $this->db = core\db::getInstance();
        }

        public function storeLastMessageDate($recipient,$thedate)
        {
            $sql = 'INSERT INTO zp_last_msg_dates (recipient,thedate) VALUES (:recipient,:thedate) ON DUPLICATE KEY UPDATE thedate = :thedate';
            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':recipient', $recipient, PDO::PARAM_STR);
            $stmn->bindValue(':thedate', $thedate, PDO::PARAM_STR);

            $stmn->execute();
       	    $stmn->closeCursor();

        }

        public function getLastMessageDate($recipient)
        {
            $sql = 'SELECT thedate FROM zp_last_msg_dates WHERE `recipient` = :recipient LIMIT 1';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':recipient', $recipient, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;

        }

    }

}
