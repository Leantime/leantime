<?php

namespace Leantime\Domain\Api\Repositories;

use Leantime\Core\Db\Db as DbCore;

class Api
{
    private DbCore $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db;
    }

    public function getAPIKeyUser(string $apiKeyUser): mixed
    {
        $sql = "SELECT * FROM `zp_user` WHERE username = :apiKeyUsername AND source <=> 'api' LIMIT 1";

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':apiKeyUsername', $apiKeyUser);

        $stmn->execute();
        $values = $stmn->fetch();
        $stmn->closeCursor();

        return $values;
    }
}
