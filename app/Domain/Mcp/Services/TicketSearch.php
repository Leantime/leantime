<?php

namespace Leantime\Domain\Mcp\Services;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

class TicketSearch
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    public function searchProjectTickets(int $projectId, array $filters = []): array
    {
        $query = $this->db->table('zp_tickets')
            ->select([
                'id',
                'headline',
                'description',
                'projectId',
                'status',
                'type',
                'priority',
                'editorId',
                'userId',
                'milestoneid',
                'storypoints',
                'modified',
                'date',
                'dateToFinish',
            ])
            ->where('projectId', $projectId)
            ->where('status', '<>', -1);

        if (! empty($filters['status'])) {
            $statuses = array_map('intval', (array) $filters['status']);
            $query->whereIn('status', $statuses);
        }

        if (! empty($filters['editorId'])) {
            $query->where('editorId', (int) $filters['editorId']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', (string) $filters['type']);
        }

        if (! empty($filters['term'])) {
            $term = '%'.trim((string) $filters['term']).'%';
            $query->where(function ($builder) use ($term) {
                $builder->where('headline', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('id', 'like', $term);
            });
        }

        $limit = max(1, min((int) ($filters['limit'] ?? 25), 100));

        $results = $query->orderByDesc('date')->limit($limit)->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }
}
