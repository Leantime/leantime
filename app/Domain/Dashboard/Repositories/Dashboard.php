<?php

namespace Leantime\Domain\Dashboard\Repositories;

use Leantime\Infrastructure\Database\Db as DbCore;

class Dashboard
{
    public ?DbCore $db;

    private array $defaultWidgets = [1, 3, 9];

    /**
     * __construct - neu db connection
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db;
    }
}
