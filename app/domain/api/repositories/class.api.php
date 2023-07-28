<?php

namespace leantime\domain\repositories {

    use PDO;
    use leantime\domain\repositories;
    use leantime\core;

    class api
    {
        private core\db $db;

        public function __construct(core\db $db)
        {
            $this->db = $db;
        }

        public function getAPIKeyUser($apiKeyUser) {

            $sql = "SELECT * FROM `zp_user` WHERE username = :apiKeyUsername AND source <=> 'api' LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':apiKeyUsername', $apiKeyUser, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;

        }

    }



}
