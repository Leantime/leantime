<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;
    use PDOException;

    class setting
    {

        private $db;

        public $applications = array(
            'general' => 'General'
        );

        /**
         * __construct - neu db connection
         *
         * @access public
         * @return
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();

        }

        public function getSetting($type)
        {

                $sql = "SELECT
						value
				FROM zp_settings WHERE `key` = :key
				LIMIT 1";

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindvalue(':key', $type, PDO::PARAM_STR);

                try {
                    $stmn->execute();
                    $values = $stmn->fetch();
                    $stmn->closeCursor();

                }catch(PDOException $e){
                    return false;
                }

                if($values !== false && isset($values['value'])) {
                    return $values['value'];
                }

                //TODO: This needs to return null or throw an exception if the setting doesn't exist.
                return false;

        }

        public function saveSetting($type, $value)
        {

            $sql = "INSERT INTO zp_settings (`key`, `value`)
				VALUES (:key, :value) ON DUPLICATE KEY UPDATE
				  `value` = :value";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindvalue(':key', $type, PDO::PARAM_STR);
            $stmn->bindvalue(':value', $value, PDO::PARAM_STR);

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;


        }

        public function deleteSetting($type)
        {

            $sql = "DELETE FROM zp_settings WHERE `key` = :key LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindvalue(':key', $type, PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();


        }

        /**
         * checkIfInstalled checks if zp user table exists (and assumes that leantime is installed)
         *
         * @access public
         * @return bool
         */
        public function checkIfInstalled()
        {

            try {

                $stmn = $this->db->database->prepare("SELECT COUNT(*) FROM zp_user");

                $stmn->execute();
                $values = $stmn->fetchAll();

                $stmn->closeCursor();

                return true;

            } catch (PDOException $e) {

                return false;

            }
        }

    }
}
