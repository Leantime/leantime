<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use PDO;

    class setting
    {
        private core\db $db;

        public $applications = array(
            'general' => 'General'
        );

        /**
         * __construct - neu db connection
         *
         * @access public
         * @return
         */
        public function __construct(core\db $db)
        {
            $this->db = $db;
        }

        public function getSetting($type)
        {
            if ($this->checkIfInstalled() === false) {
                return false;
            }

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
            } catch (\Exception $e) {
                error_log($e);
                return false;
            }

            if ($values !== false && isset($values['value'])) {
                return $values['value'];
            }

                //TODO: This needs to return null or throw an exception if the setting doesn't exist.
                return false;
        }

        public function saveSetting($type, $value)
        {

            if ($this->checkIfInstalled() === false) {
                return false;
            }

            $sql = "INSERT INTO zp_settings (`key`, `value`)
				VALUES (:key, :value) ON DUPLICATE KEY UPDATE
				  `value` = :valueUpdate";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindvalue(':key', $type, PDO::PARAM_STR);
            $stmn->bindvalue(':value', $value, PDO::PARAM_STR);
            $stmn->bindvalue(':valueUpdate', $value, PDO::PARAM_STR);

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

            if (isset($_SESSION['isInstalled']) && $_SESSION['isInstalled'] == true) {
                return true;
            }

            try {
                $stmn = $this->db->database->prepare("SHOW TABLES LIKE 'zp_user'");

                $stmn->execute();
                $values = $stmn->fetchAll();
                $stmn->closeCursor();

                if (!count($values)) {
                    $_SESSION['isInstalled'] = false;
                    return false;
                }

                $stmn = $this->db->database->prepare("SELECT COUNT(*) FROM zp_user");

                $stmn->execute();
                $values = $stmn->fetchAll();
                $stmn->closeCursor();

                $_SESSION['isInstalled'] = true;
                return true;
            } catch (\Exception $e) {
                error_log($e);
                $_SESSION['isInstalled'] = false;
                return false;
            }
        }
    }
}
