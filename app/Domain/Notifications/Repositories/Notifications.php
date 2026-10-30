<?php

namespace Leantime\Domain\Notifications\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

class Notifications
{
    private ConnectionInterface $db;

    /**
     * __construct - get database connection
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    public function getAllNotifications(int $userId, bool $showNewOnly = false, int $limitStart = 0, int $limitEnd = 100, array $filterOptions = []): false|array
    {
        $query = $this->db->table('zp_notifications')
            ->select(
                'zp_notifications.id',
                'userId',
                'read',
                'type',
                'module',
                'moduleId',
                'datetime',
                'url',
                'message',
                'authorId',
                'zp_user.firstname',
                'zp_user.lastname'
            )
            ->leftJoin('zp_user', 'zp_notifications.authorId', '=', 'zp_user.id')
            ->where('userId', $userId)
            ->where('zp_notifications.type', '!=', 'ainotification');

        if ($showNewOnly === true) {
            $query->where('read', 0);
        }

        if (count($filterOptions) > 0) {
            foreach ($filterOptions as $key => $value) {
                $query->where($key, $value);
            }
        }

        $results = $query->orderBy('datetime', 'desc')
            ->offset($limitStart)
            ->limit($limitEnd)
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @return bool|void
     */
    public function addNotifications(array $notifications)
    {
        if (count($notifications) === 0) {
            return;
        }

        $insertData = [];
        foreach ($notifications as $notif) {
            $insertData[] = [
                'userId' => $notif['userId'],
                'read' => 0,
                'type' => $notif['type'],
                'module' => $notif['module'],
                'moduleId' => $notif['moduleId'],
                'message' => $notif['message'],
                'datetime' => $notif['datetime'],
                'url' => $notif['url'],
                'authorId' => $notif['authorId'],
            ];
        }

        return $this->db->table('zp_notifications')->insert($insertData);
    }

    /**
     * When the user last read an inbox notification about each of the given entities.
     *
     * @param  int  $userId  The inbox owner.
     * @param  array<int, array{module: string, moduleId: int}>  $entities  Entities to look up.
     * @return array<string, string> "module:moduleId" => the newest `datetime` (UTC, Y-m-d H:i:s)
     *                               among that entity's READ notifications. Entities with no read
     *                               notification are absent.
     */
    public function lastReadByEntity(int $userId, array $entities): array
    {
        $pairs = [];
        foreach ($entities as $entity) {
            $pairs[$entity['module'].':'.(int) $entity['moduleId']] = ['module' => $entity['module'], 'moduleId' => (int) $entity['moduleId']];
        }

        if ($pairs === []) {
            return [];
        }

        $rows = $this->db->table('zp_notifications')
            ->selectRaw('module, moduleId, MAX(datetime) as lastRead')
            ->where('userId', $userId)
            ->where('read', 1)
            ->where(function ($query) use ($pairs) {
                foreach ($pairs as $pair) {
                    $query->orWhere(function ($entity) use ($pair) {
                        $entity->where('module', $pair['module'])->where('moduleId', $pair['moduleId']);
                    });
                }
            })
            ->groupBy('module', 'moduleId')
            ->get();

        $lastRead = [];
        foreach ($rows as $row) {
            if ($row->lastRead !== null) {
                $lastRead[$row->module.':'.(int) $row->moduleId] = (string) $row->lastRead;
            }
        }

        return $lastRead;
    }

    public function markNotificationRead(int $id, int $userId): bool
    {
        return $this->db->table('zp_notifications')
            ->where('id', $id)
            ->where('userId', $userId)
            ->update(['read' => 1]) > 0;
    }

    public function markNotificationUnread(int $id, int $userId): bool
    {
        return $this->db->table('zp_notifications')
            ->where('id', $id)
            ->where('userId', $userId)
            ->update(['read' => 0]) > 0;
    }

    public function markAllNotificationRead(int $userId): bool
    {
        $this->db->table('zp_notifications')
            ->where('userId', $userId)
            ->update(['read' => 1]);

        return true;
    }

    /**
     * Unread notification count for the given user. Excludes the
     * legacy ainotification type to match the inbox query's scope —
     * counts and inbox rows should be the same set. Uses the
     * (userId, read) composite index, so single fast lookup.
     */
    public function getUnreadCount(int $userId): int
    {
        return (int) $this->db->table('zp_notifications')
            ->where('userId', $userId)
            ->where('type', '!=', 'ainotification')
            ->where(function ($q) {
                $q->where('read', 0)->orWhereNull('read');
            })
            ->count();
    }
}
