<?php

namespace Leantime\Domain\Oneonone\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

/**
 * Oneonone repository - data access for 1:1 sessions and items.
 *
 * Tables: zp_oneonone_sessions, zp_oneonone_items.
 * Uses the Laravel Query Builder to stay database-agnostic
 * (MySQL, MariaDB, and PostgreSQL all supported).
 */
class Oneonone
{
    private ConnectionInterface $db;

    /** Allowed item types. */
    public array $itemTypes = [
        'talking_point' => 'oneonone.type.talking_point',
        'action_item' => 'oneonone.type.action_item',
        'feedback' => 'oneonone.type.feedback',
        'goal' => 'oneonone.type.goal',
        'blocker' => 'oneonone.type.blocker',
    ];

    /** Allowed session statuses. */
    public array $sessionStatuses = [
        'scheduled' => 'oneonone.status.scheduled',
        'in_progress' => 'oneonone.status.in_progress',
        'completed' => 'oneonone.status.completed',
        'cancelled' => 'oneonone.status.cancelled',
    ];

    /** Allowed mood values. */
    public array $moodValues = [
        'great' => 'oneonone.mood.great',
        'good' => 'oneonone.mood.good',
        'neutral' => 'oneonone.mood.neutral',
        'concerned' => 'oneonone.mood.concerned',
        'struggling' => 'oneonone.mood.struggling',
    ];

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    // -- Sessions -----------------------------------------------------------

    /**
     * Get all sessions for a single employee, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSessionsForEmployee(int $employeeId): array
    {
        $results = $this->db->table('zp_oneonone_sessions as s')
            ->select(
                's.*',
                'm.firstname as managerFirstname',
                'm.lastname as managerLastname',
                'm.profileId as managerProfileId',
                'e.firstname as employeeFirstname',
                'e.lastname as employeeLastname',
                'e.profileId as employeeProfileId'
            )
            ->leftJoin('zp_user as m', 'm.id', '=', 's.managerId')
            ->leftJoin('zp_user as e', 'e.id', '=', 's.employeeId')
            ->where('s.employeeId', $employeeId)
            ->orderByDesc('s.meetingDate')
            ->get();

        return array_map(fn ($r) => (array) $r, $results->toArray());
    }

    /**
     * Get all sessions managed by a specific manager, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSessionsForManager(int $managerId): array
    {
        $results = $this->db->table('zp_oneonone_sessions as s')
            ->select(
                's.*',
                'e.firstname as employeeFirstname',
                'e.lastname as employeeLastname',
                'e.profileId as employeeProfileId',
                'e.jobTitle as employeeJobTitle'
            )
            ->leftJoin('zp_user as e', 'e.id', '=', 's.employeeId')
            ->where('s.managerId', $managerId)
            ->orderByDesc('s.meetingDate')
            ->get();

        return array_map(fn ($r) => (array) $r, $results->toArray());
    }

    /**
     * Get the latest session per employee, for the manager team dashboard.
     *
     * @return array<int, array<string, mixed>> keyed by employee id
     */
    public function getLatestSessionsByEmployeeForManager(int $managerId): array
    {
        $sessions = $this->getSessionsForManager($managerId);
        $latest = [];

        foreach ($sessions as $session) {
            $employeeId = (int) ($session['employeeId'] ?? 0);
            if ($employeeId === 0) {
                continue;
            }
            if (! isset($latest[$employeeId])) {
                $latest[$employeeId] = $session;
            }
        }

        return $latest;
    }

    /** Fetch a single session by id with manager + employee info. */
    public function getSession(int $id): ?array
    {
        $row = $this->db->table('zp_oneonone_sessions as s')
            ->select(
                's.*',
                'm.firstname as managerFirstname',
                'm.lastname as managerLastname',
                'm.profileId as managerProfileId',
                'e.firstname as employeeFirstname',
                'e.lastname as employeeLastname',
                'e.profileId as employeeProfileId',
                'e.jobTitle as employeeJobTitle'
            )
            ->leftJoin('zp_user as m', 'm.id', '=', 's.managerId')
            ->leftJoin('zp_user as e', 'e.id', '=', 's.employeeId')
            ->where('s.id', $id)
            ->first();

        return $row ? (array) $row : null;
    }

    /**
     * Create a new session and return its id.
     *
     * @param  array<string, mixed>  $values
     */
    public function addSession(array $values): int
    {
        $now = date('Y-m-d H:i:s');

        return (int) $this->db->table('zp_oneonone_sessions')->insertGetId([
            'employeeId' => (int) ($values['employeeId'] ?? 0),
            'managerId' => (int) ($values['managerId'] ?? 0),
            'meetingDate' => $values['meetingDate'] ?? $now,
            'title' => $values['title'] ?? null,
            'mood' => $values['mood'] ?? null,
            'status' => $values['status'] ?? 'scheduled',
            'notes' => $values['notes'] ?? null,
            'summary' => $values['summary'] ?? null,
            'created' => $now,
            'modified' => $now,
        ]);
    }

    /**
     * Update an existing session.
     *
     * @param  array<string, mixed>  $values
     */
    public function updateSession(int $id, array $values): bool
    {
        $allowed = ['meetingDate', 'title', 'mood', 'status', 'notes', 'summary'];
        $update = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $values)) {
                $update[$key] = $values[$key];
            }
        }

        if ($update === []) {
            return false;
        }

        $update['modified'] = date('Y-m-d H:i:s');

        return $this->db->table('zp_oneonone_sessions')
            ->where('id', $id)
            ->update($update) >= 0;
    }

    /** Delete a session and cascade-delete its items. */
    public function deleteSession(int $id): bool
    {
        $this->db->table('zp_oneonone_items')->where('sessionId', $id)->delete();
        $this->db->table('zp_oneonone_sessions')->where('id', $id)->delete();

        return true;
    }

    // -- Items --------------------------------------------------------------

    /**
     * Get all items for a session, ordered by type then sortIndex.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getItemsForSession(int $sessionId): array
    {
        $results = $this->db->table('zp_oneonone_items as i')
            ->select(
                'i.*',
                'a.firstname as authorFirstname',
                'a.lastname as authorLastname',
                'u.firstname as assigneeFirstname',
                'u.lastname as assigneeLastname',
                't.headline as ticketHeadline'
            )
            ->leftJoin('zp_user as a', 'a.id', '=', 'i.author')
            ->leftJoin('zp_user as u', 'u.id', '=', 'i.assignedTo')
            ->leftJoin('zp_tickets as t', 't.id', '=', 'i.linkedTicketId')
            ->where('i.sessionId', $sessionId)
            ->orderBy('i.type')
            ->orderBy('i.sortIndex')
            ->orderBy('i.id')
            ->get();

        return array_map(fn ($r) => (array) $r, $results->toArray());
    }

    /** Fetch a single item. */
    public function getItem(int $id): ?array
    {
        $row = $this->db->table('zp_oneonone_items')->where('id', $id)->first();

        return $row ? (array) $row : null;
    }

    /**
     * Add an item.
     *
     * @param  array<string, mixed>  $values
     */
    public function addItem(array $values): int
    {
        $now = date('Y-m-d H:i:s');

        return (int) $this->db->table('zp_oneonone_items')->insertGetId([
            'sessionId' => (int) ($values['sessionId'] ?? 0),
            'type' => $values['type'] ?? 'talking_point',
            'author' => (int) ($values['author'] ?? 0),
            'assignedTo' => isset($values['assignedTo']) && $values['assignedTo'] !== '' ? (int) $values['assignedTo'] : null,
            'content' => $values['content'] ?? '',
            'status' => $values['status'] ?? 'open',
            'dueDate' => $values['dueDate'] ?? null,
            'sortIndex' => (int) ($values['sortIndex'] ?? 0),
            'linkedTicketId' => isset($values['linkedTicketId']) && $values['linkedTicketId'] !== '' ? (int) $values['linkedTicketId'] : null,
            'created' => $now,
            'modified' => $now,
        ]);
    }

    /**
     * Update an item.
     *
     * @param  array<string, mixed>  $values
     */
    public function updateItem(int $id, array $values): bool
    {
        $allowed = ['type', 'assignedTo', 'content', 'status', 'dueDate', 'sortIndex', 'linkedTicketId'];
        $update = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $values)) {
                $update[$key] = $values[$key];
            }
        }

        if ($update === []) {
            return false;
        }

        $update['modified'] = date('Y-m-d H:i:s');

        return $this->db->table('zp_oneonone_items')
            ->where('id', $id)
            ->update($update) >= 0;
    }

    /** Delete an item. */
    public function deleteItem(int $id): bool
    {
        $this->db->table('zp_oneonone_items')->where('id', $id)->delete();

        return true;
    }

    /**
     * Get all open action items assigned to a user across sessions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getOpenActionItemsForUser(int $userId): array
    {
        $results = $this->db->table('zp_oneonone_items as i')
            ->select(
                'i.*',
                's.meetingDate',
                's.employeeId',
                's.managerId'
            )
            ->leftJoin('zp_oneonone_sessions as s', 's.id', '=', 'i.sessionId')
            ->where('i.type', 'action_item')
            ->where('i.status', 'open')
            ->where('i.assignedTo', $userId)
            ->orderBy('i.dueDate')
            ->orderByDesc('s.meetingDate')
            ->get();

        return array_map(fn ($r) => (array) $r, $results->toArray());
    }

    /** Count items per session, returned as [sessionId => count]. */
    public function getItemCountsForSessions(array $sessionIds): array
    {
        if ($sessionIds === []) {
            return [];
        }

        $results = $this->db->table('zp_oneonone_items')
            ->selectRaw('sessionId, COUNT(id) as itemCount')
            ->whereIn('sessionId', $sessionIds)
            ->groupBy('sessionId')
            ->get();

        $counts = [];
        foreach ($results as $row) {
            $counts[(int) $row->sessionId] = (int) $row->itemCount;
        }

        return $counts;
    }
}
