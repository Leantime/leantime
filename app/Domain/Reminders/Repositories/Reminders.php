<?php

namespace Leantime\Domain\Reminders\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Leantime\Core\Db\Db as DbCore;

/**
 * Data access for reminders. Reminders add no tables of their own:
 *
 *  - Whether today's summary went out is read from the inbox (zp_notifications): the summary
 *    writes its inbox row before anything else.
 *  - A person's choice for one to-do is a user setting in zp_settings,
 *    usersettings.{userId}.reminders.task.{ticketId}, read here with one prefix query.
 *
 * Every date in and out is a UTC `Y-m-d H:i:s` string, like the rest of the database. Turning
 * them into someone's local days is the services' job.
 */
class Reminders
{
    /** Dates before this are Leantime's "no date" sentinels (0000-00-00 …, 1969-12-31 …). */
    /** Earlier dates are Leantime's "no date" sentinels (0000-00-00, 1969-12-31, 1970-01-01). */
    public const NO_DATE_BEFORE = '1971-01-01 00:00:00';

    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * Which of these people already have a morning summary in their inbox since the given time
     * (their local midnight, in UTC). Uses the inbox's (userId, datetime) index.
     *
     * @param  array<int, string>  $sinceByUser  userId → UTC `Y-m-d H:i:s`
     * @return array<int, true> userId → true when today's summary is already there
     */
    public function summariesSentSince(array $sinceByUser): array
    {
        if ($sinceByUser === []) {
            return [];
        }

        $rows = $this->db->table('zp_notifications')
            ->select(['userId', 'datetime'])
            ->whereIn('userId', array_keys($sinceByUser))
            ->where('datetime', '>=', min($sinceByUser))
            ->where('type', 'morning_summary')
            ->where('module', 'reminders')
            ->get();

        $sent = [];
        foreach ($rows as $row) {
            $userId = (int) $row->userId;
            if (isset($sinceByUser[$userId]) && (string) $row->datetime >= $sinceByUser[$userId]) {
                $sent[$userId] = true;
            }
        }

        return $sent;
    }

    /**
     * A person's choices for individual to-dos, from their user settings
     * (usersettings.{userId}.reminders.task.{ticketId}), in one prefix query.
     *
     * @return array<int, string> ticketId → preset
     */
    public function taskChoices(int $userId): array
    {
        $prefix = 'usersettings.'.$userId.'.reminders.task.';

        $choices = [];
        foreach ($this->db->table('zp_settings')->where('key', 'like', $prefix.'%')->pluck('value', 'key') as $key => $value) {
            $ticketId = substr((string) $key, strlen($prefix));
            if (ctype_digit($ticketId)) {
                $choices[(int) $ticketId] = (string) $value;
            }
        }

        return $choices;
    }

    /**
     * Active people (not API service users), by id, $limit at a time after $afterId.
     *
     * @return array<int, array{id: int, notifications: int}>
     */
    public function activeUsers(int $afterId, int $limit): array
    {
        return $this->db->table('zp_user')
            ->select(['id', 'notifications'])
            ->whereIn('status', ['a', 'A'])
            ->where(fn ($q) => $q->whereNull('source')->orWhere('source', '<>', 'api'))
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->id, 'notifications' => (int) ($row->notifications ?? 0)])
            ->all();
    }

    /**
     * One active person, or null.
     *
     * @return array{id: int, notifications: int, username: string, firstname: string}|null
     */
    public function activeUser(int $userId): ?array
    {
        $row = $this->db->table('zp_user')
            ->select(['id', 'notifications', 'username', 'firstname'])
            ->where('id', $userId)
            ->whereIn('status', ['a', 'A'])
            ->first();

        return $row === null ? null : [
            'id' => (int) $row->id,
            'notifications' => (int) ($row->notifications ?? 0),
            'username' => (string) $row->username,
            'firstname' => (string) ($row->firstname ?? ''),
        ];
    }

    /**
     * To-dos (not milestones) that are this person's: assigned to them, or unassigned and created
     * by them. Only open projects, only to-dos with a due date, and only those that can matter for
     * the summary: due by $windowEndUtc (that includes everything overdue), or started by
     * $todayEndUtc and not due before $todayStartUtc (the "every morning from the start date"
     * preset). Done is decided later, per project, from its status labels.
     *
     * $alsoIds adds to-dos that aren't theirs but that they asked to be reminded about (their own
     * choice on someone else's to-do); the same filters apply. Each row says whether it is
     * theirs (isYours).
     *
     * @param  array<int, int>  $alsoIds
     * @return array<int, array<string, mixed>>
     */
    public function tasksFor(int $userId, string $todayStartUtc, string $todayEndUtc, string $windowEndUtc, array $alsoIds = []): array
    {
        return $this->db->table('zp_tickets as t')
            ->join('zp_projects as p', 'p.id', '=', 't.projectId')
            ->select([
                't.id', 't.headline', 't.projectId', 't.status', 't.priority', 't.dateToFinish', 't.editFrom',
                't.editorId', 't.userId', 'p.name as projectName',
            ])
            ->where(fn ($q) => $q->whereNull('t.type')->orWhere('t.type', '<>', 'milestone'))
            ->where(fn ($q) => $this->openProject($q))
            ->where(function ($q) use ($userId, $alsoIds) {
                $q->where(fn ($q) => $this->owned($q, $userId));
                if ($alsoIds !== []) {
                    $q->orWhereIn('t.id', $alsoIds);
                }
            })
            ->where('t.dateToFinish', '>=', self::NO_DATE_BEFORE)
            ->where(function ($q) use ($todayStartUtc, $todayEndUtc, $windowEndUtc) {
                $q->where('t.dateToFinish', '<=', $windowEndUtc)
                    ->orWhere(function ($q) use ($todayStartUtc, $todayEndUtc) {
                        $q->where('t.editFrom', '>=', self::NO_DATE_BEFORE)
                            ->where('t.editFrom', '<=', $todayEndUtc)
                            ->where('t.dateToFinish', '>=', $todayStartUtc);
                    });
            })
            ->orderBy('t.dateToFinish')
            ->orderBy('t.id')
            ->get()
            ->map(fn ($row) => ['isYours' => self::isOwner($userId, $row->editorId ?? null, $row->userId ?? null)] + (array) $row)
            ->all();
    }

    /** A real date: not empty and not one of Leantime's "no date" sentinels. */
    public static function isRealDate(mixed $value): bool
    {
        return is_string($value) && $value !== '' && $value >= self::NO_DATE_BEFORE;
    }

    /** A person's company role, read without a session; null when they don't exist. */
    public function userRole(int $userId): ?string
    {
        $role = $this->db->table('zp_user')->where('id', $userId)->value('role');

        return $role === null ? null : (string) $role;
    }

    /** A person's role on a project's team, '' when they aren't on it or have none. */
    public function projectRole(int $userId, int $projectId): string
    {
        return (string) ($this->db->table('zp_relationuserproject')
            ->where('userId', $userId)
            ->where('projectId', $projectId)
            ->value('projectRole') ?? '');
    }

    /** Assigned to the person, or unassigned and created by them (the rule {@see owned()} queries). */
    public static function isOwner(int $userId, mixed $editorId, mixed $creatorId): bool
    {
        $editor = trim((string) ($editorId ?? ''));
        if ($editor !== '' && $editor !== '0') {
            return $editor === (string) $userId;
        }

        return (int) ($creatorId ?? 0) === $userId;
    }

    /**
     * Milestones this person owns (assigned, or unassigned and created by them) that end between
     * the two times, in open projects. A milestone's end is its editTo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function milestonesFor(int $userId, string $fromUtc, string $toUtc): array
    {
        return $this->db->table('zp_tickets as t')
            ->join('zp_projects as p', 'p.id', '=', 't.projectId')
            ->select(['t.id', 't.headline', 't.projectId', 't.status', 't.editTo', 'p.name as projectName'])
            ->where('t.type', 'milestone')
            ->where(fn ($q) => $this->openProject($q))
            ->where(fn ($q) => $this->owned($q, $userId))
            ->whereBetween('t.editTo', [$fromUtc, $toUtc])
            ->orderBy('t.editTo')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /**
     * Open projects this person is on the team of that end between the two times.
     *
     * @return array<int, array{id: int, name: string, end: string}>
     */
    public function projectsEndingFor(int $userId, string $fromUtc, string $toUtc): array
    {
        return $this->db->table('zp_projects as p')
            ->join('zp_relationuserproject as r', 'r.projectId', '=', 'p.id')
            ->select(['p.id', 'p.name', 'p.end'])
            ->where('r.userId', $userId)
            ->where(fn ($q) => $this->openProject($q))
            ->whereBetween('p.end', [$fromUtc, $toUtc])
            ->distinct()
            ->orderBy('p.end')
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->id, 'name' => (string) $row->name, 'end' => (string) $row->end])
            ->all();
    }

    /**
     * A project's team: active members with their company role and their role in this project.
     *
     * @return array<int, array{userId: int, role: string, projectRole: string}>
     */
    public function projectTeam(int $projectId): array
    {
        return $this->db->table('zp_relationuserproject as r')
            ->join('zp_user as u', 'u.id', '=', 'r.userId')
            ->select(['r.userId', 'u.role', 'r.projectRole'])
            ->where('r.projectId', $projectId)
            ->whereIn('u.status', ['a', 'A'])
            // API service accounts aren't people: they never get summaries, so they mustn't count
            // as the team's manager either.
            ->where(fn ($q) => $q->whereNull('u.source')->orWhere('u.source', '<>', 'api'))
            ->get()
            ->map(fn ($row) => ['userId' => (int) $row->userId, 'role' => (string) $row->role, 'projectRole' => (string) ($row->projectRole ?? '')])
            ->all();
    }

    /**
     * The to-dos among $ticketIds that repeat at least every weekday (a daily pattern, or a weekly
     * one on five or more days), from the recurring-tasks plugin's table. Empty when the table is
     * missing.
     *
     * @param  array<int, int>  $ticketIds
     * @return array<int, true>
     */
    public function frequentRecurring(array $ticketIds): array
    {
        if ($ticketIds === []) {
            return [];
        }

        try {
            $rows = $this->db->table('zp_recurring_patterns')
                ->select(['entityId', 'type', 'interval', 'weekDays'])
                ->where('module', 'tickets')
                ->where('enabled', 1)
                ->whereIn('entityId', $ticketIds)
                ->get();
        } catch (QueryException) {
            return [];
        }

        $frequent = [];
        foreach ($rows as $row) {
            $interval = (int) ($row->interval ?? 1);
            if ($interval > 1) {
                continue;
            }
            $days = json_decode((string) ($row->weekDays ?? ''), true);
            if ($row->type === 'daily' || ($row->type === 'weekly' && is_array($days) && count($days) >= 5)) {
                $frequent[(int) $row->entityId] = true;
            }
        }

        return $frequent;
    }

    /** Projects that are neither archived (-1) nor closed (1). */
    private function openProject($query): void
    {
        $query->whereNull('p.state')->orWhereNotIn('p.state', [-1, 1]);
    }

    /** Assigned to the person, or unassigned and created by them. */
    private function owned($query, int $userId): void
    {
        $query->where('t.editorId', (string) $userId)
            ->orWhere(function ($q) use ($userId) {
                $q->where(fn ($q) => $q->whereNull('t.editorId')->orWhereIn('t.editorId', ['', '0']))
                    ->where('t.userId', $userId);
            });
    }
}
