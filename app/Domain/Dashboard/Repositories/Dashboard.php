<?php

namespace Leantime\Domain\Dashboard\Repositories;

use Leantime\Core\Db\Db as DbCore;

class Dashboard
{
    public ?DbCore $db;

    /**
     * __construct - neu db connection
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db;
    }
}
