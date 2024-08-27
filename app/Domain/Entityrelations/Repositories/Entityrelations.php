<?php

namespace Leantime\Domain\Entityrelations\Repositories {

    use Exception;
    use Leantime\Core\Db\Db as DbCore;
    use PDO;

    /**
     *
     */
    class Entityrelations
    {
        private DbCore $db;

        public array $applications = array(
            'general' => 'General',
        );

        /**
         * __construct - neu db connection
         *
         * @access public
         * @param DbCore $db
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }

        /**
         * @param $type
         * @return false|mixed
         */
        /**
         * @param $type
         * @return false|mixed
         */
        public function getSetting($type): mixed
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
            } catch (Exception $e) {
                report($e);
                return false;
            }

            if ($values !== false && isset($values['value'])) {
                return $values['value'];
            }

                //TODO: This needs to return null or throw an exception if the setting doesn't exist.
                return false;
        }

        /**
         * @param $type
         * @param $value
         * @return bool
         */
        /**
         * @param $type
         * @param $value
         * @return bool
         */
        public function saveSetting($type, $value): bool
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

        /**
         * @param $type
         * @return void
         */
        /**
         * @param $type
         * @return void
         */
        public function deleteSetting($type): void
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
        public function checkIfInstalled(): bool
        {

            if (session()->exists("isInstalled") && session("isInstalled")) {
                return true;
            }

            try {
                $stmn = $this->db->database->prepare("SHOW TABLES LIKE 'zp_user'");

                $stmn->execute();
                $values = $stmn->fetchAll();
                $stmn->closeCursor();

                if (!count($values)) {
                    session(["isInstalled" => false]);
                    return false;
                }

                $stmn = $this->db->database->prepare("SELECT COUNT(*) FROM zp_user");

                $stmn->execute();
                $values = $stmn->fetchAll();
                $stmn->closeCursor();

                session(["isInstalled" => true]);
                return true;
            } catch (Exception $e) {
                report($e);
                session(["isInstalled" => false]);
                return false;
            }
        }
    }
}
