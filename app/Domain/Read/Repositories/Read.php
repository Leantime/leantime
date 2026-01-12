<?php

namespace Leantime\Domain\Read\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

class Read
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    public function markAsRead(string $module, int|string $moduleId, int|string $userId): void
    {
        $this->db->table('zp_read')->insert([
            'module' => $module,
            'moduleId' => $moduleId,
            'userId' => $userId,
        ]);
    }

    public function isRead(string $module, int|string $moduleId, int|string $userId): bool
    {
        return $this->db->table('zp_read')
            ->where('module', $module)
            ->where('moduleId', $moduleId)
            ->where('userId', $userId)
            ->exists();
    }
}
