<?php

namespace leantime\domain\repositories {

    use Exception;
    use leantime\domain\services\ldap;
    use PDO;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use PDOException;
    use leantime\core;
    use RobThree\Auth\TwoFactorAuth;

    class api
    {
        /**
         * @access private
         * @var    int user id from DB
         */
        private $userId = null;

        private $config;

        public function __construct()
        {

            $this->db = core\db::getInstance();
            $this->config = \leantime\core\environment::getInstance();
            $this->userRepo = new repositories\users();
        }

        public function getAPIKeyUser($apiKeyUser) {

            $sql = "SELECT * FROM `zp_user` WHERE username = :apiKeyUsername AND source = 'api' LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':apiKeyUsername', $apiKeyUser, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;

        }

    }



}
