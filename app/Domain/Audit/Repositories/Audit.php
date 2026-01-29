<?php

namespace Leantime\Domain\Audit\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

class Audit
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    public function storeEvent(string $action = 'ping', string $values = '', string $entity = '', int $entityId = 0, int $userId = 0, int $projectId = 0, string $thedate = ''): void
    {
        $eventDate = $thedate === '' ? now() : $thedate;

        $this->db->table('zp_audit')->insert([
            'userId' => $userId,
            'projectId' => $projectId,
            'action' => $action,
            'entity' => $entity,
            'entityId' => $entityId,
            'values' => $values,
            'date' => $eventDate,
        ]);
    }

    /**
     * @return mixed|null
     */
    public function getLastEvent(string $action = ''): mixed
    {
        $query = $this->db->table('zp_audit');

        if ($action !== '') {
            $query->where('action', $action);
        }

        $result = $query->orderBy('date', 'desc')
            ->limit(1)
            ->first();

        return $result ? (array) $result : null;
    }

    /**
     * Get audit events for a specific entity.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEventsForEntity(string $entity, int $entityId, int $limit = 20): array
    {
        return $this->db->table('zp_audit')
            ->select('zp_audit.*', 'zp_user.firstname', 'zp_user.lastname', 'zp_user.profileId')
            ->leftJoin('zp_user', 'zp_audit.userId', '=', 'zp_user.id')
            ->where('entity', $entity)
            ->where('entityId', $entityId)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => (array) $item)
            ->toArray();
    }

    public function pruneEvents(int $ageDays = 30): void
    {
        $cutoffDate = CarbonImmutable::now()->subDays($ageDays)->startOfDay();

        $this->db->table('zp_audit')
            ->whereDate('date', '<', $cutoffDate)
            ->delete();
    }
}
