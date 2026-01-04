<?php

namespace Leantime\Domain\Api\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

class Api
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    public function getAPIKeyUser(string $apiKeyUser): mixed
    {
        $result = $this->db->table('zp_user')
            ->where('username', $apiKeyUser)
            ->where('source', 'api')
            ->limit(1)
            ->first();

        return $result ? (array) $result : false;
    }
}
