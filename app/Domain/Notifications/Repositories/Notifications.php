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

    /**
     * @param  false  $showNewOnly
     */
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

        if (is_array($filterOptions) && count($filterOptions) > 0) {
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

    public function markNotificationRead(int $id): bool
    {
        return $this->db->table('zp_notifications')
            ->where('id', $id)
            ->update(['read' => 1]) > 0;
    }

    public function markAllNotificationRead(int $userId): bool
    {
        return $this->db->table('zp_notifications')
            ->where('userId', $userId)
            ->update(['read' => 1]) >= 0;
    }
}
