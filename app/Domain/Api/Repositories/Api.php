<?php

namespace Leantime\Domain\Api\Repositories {

    use Leantime\Core\Db as DbCore;
    use PDO;

    /**
     *
     */
    class Api
    {
        private DbCore $db;

        /**
         * @param DbCore $db
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }

        /**
         * @param $apiKeyUser
         * @return mixed
         */
        public function getAPIKeyUser($apiKeyUser): mixed
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
