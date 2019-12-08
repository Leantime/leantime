<?php
namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class read
    {

        public function __construct()
        {

            $this->db = core\db::getInstance();
        }

        public function markAsRead($module,$moduleId,$userId)
        {

            $sql = 'INSERT INTO zp_read (module,moduleId,userId) VALUES (:module,:moduleId,:userId)';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_STR);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();

        }

        public function isRead($module,$moduleId,$userId)
        {

            $sql = "SELECT * FROM zp_read 
					WHERE module=:module AND moduleId=:moduleId AND userId=:userId";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_STR);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            $return = false;
            if ($values) {
                $return = true;
            }

            return $return;
        }

    }
}
