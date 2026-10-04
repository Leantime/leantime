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
            ->where('dateModified', '>=', $startingFrom->format('Y-m-d'))
            ->where('ticketId', $ticketId);

        $results = $query->orderBy('dateModified', 'desc')->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * All recorded field changes of a ticket, oldest first, with the name of the user who made them.
     *
     * Access-agnostic: callers must authorize the ticket before calling this.
     *
     * @param  int  $ticketId  The ticket id
     * @param  int  $limit  Maximum number of rows (the most recent ones are kept)
     * @return array<int, array<string, mixed>> Rows with id, userId, changeType, changeValue,
     *                                          dateModified, firstname, lastname
     */
    public function getTicketChanges(int $ticketId, int $limit): array
    {
        $results = $this->db->table('zp_tickethistory')
            ->leftJoin('zp_user', 'zp_user.id', '=', 'zp_tickethistory.userId')
            ->select(
                'zp_tickethistory.id',
                'zp_tickethistory.userId',
                'zp_tickethistory.changeType',
                'zp_tickethistory.changeValue',
                'zp_tickethistory.dateModified',
                'zp_user.firstname',
                'zp_user.lastname'
            )
            ->where('zp_tickethistory.ticketId', $ticketId)
            ->orderBy('zp_tickethistory.dateModified', 'desc')
            ->orderBy('zp_tickethistory.id', 'desc')
            ->limit($limit)
            ->get();

        $rows = array_map(fn ($item) => (array) $item, $results->toArray());

        return array_reverse($rows);
    }

    /**
     * The latest change of one field recorded before a given history row (ordered like
     * getTicketChanges(): by dateModified, then id).
     *
     * Access-agnostic: callers must authorize the ticket before calling this.
     *
     * @param  int  $ticketId  The ticket id
     * @param  string  $changeType  The field (changeType)
     * @param  string  $beforeDate  dateModified of the reference row
     * @param  int  $beforeId  id of the reference row
     * @return array<string, mixed>|null The row (same columns as getTicketChanges()), null if none
     */
    public function getLatestChangeBefore(int $ticketId, string $changeType, string $beforeDate, int $beforeId): ?array
    {
        $row = $this->db->table('zp_tickethistory')
            ->leftJoin('zp_user', 'zp_user.id', '=', 'zp_tickethistory.userId')
            ->select(
                'zp_tickethistory.id',
                'zp_tickethistory.userId',
                'zp_tickethistory.changeType',
                'zp_tickethistory.changeValue',
                'zp_tickethistory.dateModified',
                'zp_user.firstname',
                'zp_user.lastname'
            )
            ->where('zp_tickethistory.ticketId', $ticketId)
            ->where('zp_tickethistory.changeType', $changeType)
            ->where(function ($query) use ($beforeDate, $beforeId) {
                $query->where('zp_tickethistory.dateModified', '<', $beforeDate)
                    ->orWhere(function ($sameTime) use ($beforeDate, $beforeId) {
                        $sameTime->where('zp_tickethistory.dateModified', '=', $beforeDate)
                            ->where('zp_tickethistory.id', '<', $beforeId);
                    });
            })
            ->orderBy('zp_tickethistory.dateModified', 'desc')
            ->orderBy('zp_tickethistory.id', 'desc')
            ->first();

        return $row === null ? null : (array) $row;
    }

    /**
     * Display names for a set of user ids, keyed by user id.
     *
     * @param  array<int, int>  $userIds  The user ids to look up
     * @return array<int, string> "Firstname Lastname" keyed by user id
     */
    public function getUserNames(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $users = $this->db->table('zp_user')
            ->select('id', 'firstname', 'lastname')
            ->whereIn('id', $userIds)
            ->get();

        $names = [];
        foreach ($users as $user) {
            $names[(int) $user->id] = trim($user->firstname.' '.$user->lastname);
        }

        return $names;
    }
}
