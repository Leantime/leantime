<?php

namespace Leantime\Domain\Timesheets\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

/**
 * Append-only audit log for zp_timesheets row events ("logged" / "modified").
 *
 * Deliberately exposes only insert + chronological read. There is no update or delete
 * method anywhere in this class — history rows must never be altered or removed once
 * written, and the absence of those methods is the enforcement mechanism.
 */
class TimesheetsHistory
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * Record a single immutable history event for a timesheet row.
     */
    public function addHistory(int $timesheetId, int $userId, string $action, float $hours): void
    {
        $this->db->table('zp_timesheets_history')->insert([
            'timesheetId' => $timesheetId,
            'userId' => $userId,
            'action' => $action,
            'hours' => $hours,
            'dateCreated' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Count of existing history rows for a timesheet entry — used to classify a new event
     * as 'logged' (none yet) vs 'modified' (at least one already recorded).
     */
    public function countHistoryForTimesheet(int $timesheetId): int
    {
        return $this->db->table('zp_timesheets_history')
            ->where('timesheetId', $timesheetId)
            ->count();
    }

    /**
     * Get all history events for a single timesheet entry, oldest first, including the
     * acting user's name for display.
     */
    public function getHistoryForTimesheet(int $timesheetId): array
    {
        $results = $this->db->table('zp_timesheets_history')
            ->select(
                'zp_timesheets_history.id',
                'zp_timesheets_history.timesheetId',
                'zp_timesheets_history.userId',
                'zp_timesheets_history.action',
                'zp_timesheets_history.hours',
                'zp_timesheets_history.dateCreated',
                'zp_user.firstname',
                'zp_user.lastname',
            )
            ->leftJoin('zp_user', 'zp_timesheets_history.userId', '=', 'zp_user.id')
            ->where('zp_timesheets_history.timesheetId', $timesheetId)
            ->orderBy('zp_timesheets_history.dateCreated', 'asc')
            ->orderBy('zp_timesheets_history.id', 'asc')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }
}
