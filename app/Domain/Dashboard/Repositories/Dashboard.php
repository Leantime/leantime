<?php

namespace Leantime\Domain\Dashboard\Repositories;

use Leantime\Core\Db\Db as DbCore;

class Dashboard
{
    /**
     * @var ?DbCore
     */
    public ?DbCore $db;

    /**
     * @var array
     */
    private array $defaultWidgets = [1, 3, 9];

    /**
     * __construct - neu db connection.
     *
     * @param DbCore $db
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db;
    }
}
