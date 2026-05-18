<?php

namespace Leantime\Domain\Notepad\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

/**
 * Personal notepad repository. Every method is scoped by userId — there is no
 * "list all" operation by design. Other users' rows are strictly inaccessible.
 */
class Notepad
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * Get the last $days days of tasks for the given user, grouped by date (desc).
     * Only dates that have at least one task are returned.
     *
     * @return array<string, array<int, array<string,mixed>>>  Keyed by Y-m-d.
     */
    public function getRecentTasksByDay(int $userId, int $days = 7): array
    {
        $cutoff = (new \DateTime('-' . ($days - 1) . ' days'))->format('Y-m-d');

        $rows = $this->db->table('zp_personal_notepad')
            ->where('userId', $userId)
            ->where('taskDate', '>=', $cutoff)
            ->orderBy('taskDate', 'desc')
            ->orderBy('sortOrder', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $arr = (array) $row;
            $grouped[$arr['taskDate']][] = $arr;
        }

        return $grouped;
    }

    /**
     * Find a single task — only if it belongs to the given user.
     */
    public function find(int $id, int $userId): ?array
    {
        $row = $this->db->table('zp_personal_notepad')
            ->where('id', $id)
            ->where('userId', $userId)
            ->first();

        return $row ? (array) $row : null;
    }

    public function create(int $userId, string $taskDate, string $content): int
    {
        $now = date('Y-m-d H:i:s');

        $maxSort = (int) $this->db->table('zp_personal_notepad')
            ->where('userId', $userId)
            ->where('taskDate', $taskDate)
            ->max('sortOrder');

        return (int) $this->db->table('zp_personal_notepad')->insertGetId([
            'userId' => $userId,
            'taskDate' => $taskDate,
            'content' => $content,
            'done' => 0,
            'sortOrder' => $maxSort + 1,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
    }

    public function updateContent(int $id, int $userId, string $content): bool
    {
        return $this->db->table('zp_personal_notepad')
            ->where('id', $id)
            ->where('userId', $userId)
            ->update([
                'content' => $content,
                'updatedAt' => date('Y-m-d H:i:s'),
            ]) > 0;
    }

    public function toggleDone(int $id, int $userId, bool $done): bool
    {
        return $this->db->table('zp_personal_notepad')
            ->where('id', $id)
            ->where('userId', $userId)
            ->update([
                'done' => $done ? 1 : 0,
                'updatedAt' => date('Y-m-d H:i:s'),
            ]) > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        return $this->db->table('zp_personal_notepad')
            ->where('id', $id)
            ->where('userId', $userId)
            ->delete() > 0;
    }
}
