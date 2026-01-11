<?php

namespace Leantime\Domain\Tickets\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

class TicketHistory
{
    private ConnectionInterface $db;

    /**
     * __construct - get database connection
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    public function getRecentTicketHistory(\DateTime $startingFrom, int $ticketId): array
    {
        $query = $this->db->table('zp_tickethistory')
            ->where('dateModified', '>=', $startingFrom->format('Y-m-d'));

        if ($ticketId !== null) {
            $query->where('ticketId', $ticketId);
        }

        $results = $query->orderBy('dateModified', 'desc')->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }
}
