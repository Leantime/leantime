<?php

namespace Leantime\Domain\Api\Repositories {

    use PDO;
    use Leantime\Core\Db as DbCore;

    class Api
    {
        private DbCore $db;

        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }

        public function getAPIKeyUser($apiKeyUser)
        {

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
