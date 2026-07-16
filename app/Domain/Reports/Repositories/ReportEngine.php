<?php

declare(strict_types=1);

namespace Leantime\Domain\Reports\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;

/**
 * Bulk, session-free queries backing the report engine.
 *
 * Every method takes an explicit set of project ids plus the requesting user's context
 * (user id, client id) so the engine can serve project, plan, strategy and — later —
 * company-level reports without depending on session('currentProject'). The access
 * predicate mirrors Tickets\Repositories\Tickets::getAllMilestones: assigned via
 * zp_relationuserproject, open to all, open to the user's client, or requestor role >= 40.
 */
class ReportEngine
{
    private ConnectionInterface $connection;

    private DatabaseHelper $dbHelper;

    public function __construct(DbCore $db, DatabaseHelper $dbHelper)
    {
        $this->connection = $db->getConnection();
        $this->dbHelper = $dbHelper;
    }

    /**
     * All milestones of the given projects the requesting user may see. No date filtering —
     * the service buckets rows into completed/in-progress/overdue/upcoming.
     *
     * @param  int[]  $projectIds
     * @return array<int, object>
     */
    public function getMilestonesForProjects(array $projectIds, int $userId, int $clientId): array
    {
        if ($projectIds === []) {
            return [];
        }

        $query = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.outcomeImpact',
                'zp_tickets.date',
                'zp_tickets.projectId',
                'zp_tickets.status',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.modified',
                'zp_projects.name as projectName',
            ])
            ->selectRaw("'milestone' AS ".$this->dbHelper->wrapColumn('type'))
            ->selectRaw("CASE WHEN (zp_tickets.tags IS NULL OR zp_tickets.tags = '') THEN 'var(--grey)' ELSE zp_tickets.tags END AS tags")
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->where('zp_tickets.type', '=', 'milestone')
            ->whereIn('zp_tickets.projectId', $projectIds)
            ->orderBy('zp_tickets.editTo');

        $this->applyAccessPredicate($query, $userId, $clientId);

        return $query->get()->all();
    }

    /**
     * Status-change history rows for the given tickets, oldest first. The service derives
     * completion dates from the latest transition into a DONE-type status.
     *
     * @param  int[]  $ticketIds
     * @return array<int, object> Rows with ticketId, changeValue (new status id), dateModified
     */
    public function getStatusHistoryForTickets(array $ticketIds): array
    {
        if ($ticketIds === []) {
            return [];
        }

        return $this->connection->table('zp_tickethistory')
            ->select(['ticketId', 'changeValue', 'dateModified'])
            ->whereIn('ticketId', $ticketIds)
            ->where('changeType', '=', 'status')
            ->orderBy('dateModified')
            ->get()
            ->all();
    }

    /**
     * Due-date change history ("toDate") for the given tickets within a window, oldest first.
     * Feeds the commitment-integrity view (milestones pushed out of the period).
     *
     * @param  int[]  $ticketIds
     * @return array<int, object> Rows with ticketId, changeValue (new due date), dateModified
     */
    public function getDueDateChangesForTickets(array $ticketIds, string $fromDb, string $toDb): array
    {
        if ($ticketIds === []) {
            return [];
        }

        return $this->connection->table('zp_tickethistory')
            ->select(['ticketId', 'changeValue', 'dateModified'])
            ->whereIn('ticketId', $ticketIds)
            ->where('changeType', '=', 'toDate')
            ->whereBetween('dateModified', [$fromDb, $toDb])
            ->orderBy('dateModified')
            ->get()
            ->all();
    }

    /**
     * Non-milestone tickets belonging to the given milestones (for key-task lists and
     * milestone progress computation).
     *
     * @param  int[]  $milestoneIds
     * @return array<int, object>
     */
    public function getTasksForMilestones(array $milestoneIds): array
    {
        if ($milestoneIds === []) {
            return [];
        }

        return $this->connection->table('zp_tickets')
            ->select([
                'id',
                'headline',
                'status',
                'projectId',
                'milestoneid',
                'storypoints',
                'priority',
                'editTo',
                'dateToFinish',
            ])
            ->whereIn('milestoneid', $milestoneIds)
            ->where('type', '<>', 'milestone')
            ->orderBy('milestoneid')
            ->get()
            ->all();
    }

    /**
     * Project status updates (comments on module "project" carrying a green/yellow/red status)
     * within the given window, newest first.
     *
     * @param  int[]  $projectIds
     * @return array<int, object>
     */
    public function getStatusUpdatesForProjects(array $projectIds, string $fromDb, string $toDb, int $userId, int $clientId): array
    {
        if ($projectIds === []) {
            return [];
        }

        $query = $this->connection->table('zp_comment')
            ->select([
                'zp_comment.id',
                'zp_comment.moduleId as projectId',
                'zp_comment.text',
                'zp_comment.date',
                'zp_comment.status',
                'zp_user.firstname as authorFirstname',
                'zp_user.lastname as authorLastname',
                'zp_user.profileId as authorProfileId',
            ])
            ->leftJoin('zp_user', 'zp_comment.userId', '=', 'zp_user.id')
            ->leftJoin('zp_projects', 'zp_comment.moduleId', '=', 'zp_projects.id')
            ->where('zp_comment.module', '=', 'project')
            ->whereIn('zp_comment.moduleId', $projectIds)
            ->whereBetween('zp_comment.date', [$fromDb, $toDb])
            ->orderByDesc('zp_comment.date');

        $this->applyAccessPredicate($query, $userId, $clientId);

        return $query->get()->all();
    }

    /**
     * The most recent status update per project regardless of period (feeds the status pill).
     *
     * @param  int[]  $projectIds
     * @return array<int, object> Keyed by projectId
     */
    public function getLatestStatusUpdateForProjects(array $projectIds): array
    {
        if ($projectIds === []) {
            return [];
        }

        $latest = $this->connection->table('zp_comment')
            ->selectRaw('moduleId, MAX(date) as maxDate')
            ->where('module', '=', 'project')
            ->whereIn('moduleId', $projectIds)
            ->groupBy('moduleId');

        $rows = $this->connection->table('zp_comment')
            ->select([
                'zp_comment.moduleId as projectId',
                'zp_comment.text',
                'zp_comment.date',
                'zp_comment.status',
                'zp_user.firstname as authorFirstname',
                'zp_user.lastname as authorLastname',
            ])
            ->joinSub($latest, 'latest', function ($join) {
                $join->on('zp_comment.moduleId', '=', 'latest.moduleId')
                    ->on('zp_comment.date', '=', 'latest.maxDate');
            })
            ->leftJoin('zp_user', 'zp_comment.userId', '=', 'zp_user.id')
            ->where('zp_comment.module', '=', 'project')
            ->get()
            ->all();

        $byProject = [];
        foreach ($rows as $row) {
            $byProject[(int) $row->projectId] = $row;
        }

        return $byProject;
    }

    /**
     * Goals (goal-board canvas items) of the given projects.
     *
     * @param  int[]  $projectIds
     * @return array<int, object>
     */
    public function getGoalsForProjects(array $projectIds, int $userId, int $clientId): array
    {
        if ($projectIds === []) {
            return [];
        }

        $query = $this->connection->table('zp_canvas_items')
            ->select([
                'zp_canvas_items.id',
                'zp_canvas_items.title',
                'zp_canvas_items.description',
                'zp_canvas_items.status',
                'zp_canvas_items.metricType',
                'zp_canvas_items.startValue',
                'zp_canvas_items.currentValue',
                'zp_canvas_items.endValue',
                'zp_canvas_items.setting',
                'zp_canvas_items.milestoneId',
                'zp_canvas_items.kpi',
                'zp_canvas_items.startDate',
                'zp_canvas_items.endDate',
                'zp_canvas_items.canvasId',
                'zp_canvas.projectId',
                'zp_canvas.title as boardTitle',
                'milestone.headline as milestoneHeadline',
            ])
            ->join('zp_canvas', 'zp_canvas_items.canvasId', '=', 'zp_canvas.id')
            ->leftJoin('zp_projects', 'zp_canvas.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_tickets as milestone', function ($join) {
                $join->on('zp_canvas_items.milestoneId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('milestone.id'), 'text')));
            })
            ->where('zp_canvas.type', '=', 'goalcanvas')
            ->where('zp_canvas_items.box', '=', 'goal')
            ->whereIn('zp_canvas.projectId', $projectIds)
            ->orderBy('zp_canvas.projectId')
            ->orderBy('zp_canvas_items.sortindex');

        $this->applyAccessPredicate($query, $userId, $clientId);

        return $query->get()->all();
    }

    /**
     * Hours logged against the given projects within a window, grouped by project and
     * milestone (tasks roll up to their milestone; unassigned work groups under 0).
     *
     * @param  int[]  $projectIds
     * @return array<int, object> Rows with projectId, milestoneId, loggedHours
     */
    public function getHoursLoggedForProjects(array $projectIds, string $fromDb, string $toDb): array
    {
        if ($projectIds === []) {
            return [];
        }

        return $this->connection->table('zp_timesheets')
            ->selectRaw('zp_tickets.projectId AS '.$this->dbHelper->wrapColumn('projectId'))
            ->selectRaw('COALESCE(zp_tickets.milestoneid, 0) AS '.$this->dbHelper->wrapColumn('milestoneId'))
            ->selectRaw('SUM(zp_timesheets.hours) AS '.$this->dbHelper->wrapColumn('loggedHours'))
            ->join('zp_tickets', 'zp_timesheets.ticketId', '=', 'zp_tickets.id')
            ->whereIn('zp_tickets.projectId', $projectIds)
            ->whereBetween('zp_timesheets.workDate', [$fromDb, $toDb])
            ->groupBy('zp_tickets.projectId')
            ->groupByRaw('COALESCE(zp_tickets.milestoneid, 0)')
            ->get()
            ->all();
    }

    /**
     * Core metadata of the given projects the requesting user may see.
     *
     * @param  int[]  $projectIds
     * @return array<int, object> Keyed by project id
     */
    public function getProjectsMeta(array $projectIds, int $userId, int $clientId): array
    {
        if ($projectIds === []) {
            return [];
        }

        $query = $this->connection->table('zp_projects')
            ->select([
                'zp_projects.id',
                'zp_projects.name',
                'zp_projects.details',
                'zp_projects.clientId',
                'zp_projects.state',
                'zp_projects.start',
                'zp_projects.end',
                'zp_projects.type',
                'zp_projects.parent',
                'zp_clients.name as clientName',
            ])
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->whereIn('zp_projects.id', $projectIds);

        $this->applyAccessPredicate($query, $userId, $clientId);

        $byId = [];
        foreach ($query->get()->all() as $row) {
            $byId[(int) $row->id] = $row;
        }

        return $byId;
    }

    /**
     * Applies the standard project access predicate: project assigned to the user, open to
     * everyone, open to the user's client, or the requesting user is admin/owner (role >= 40).
     * The query must already join zp_projects.
     */
    private function applyAccessPredicate(Builder $query, int $userId, int $clientId): void
    {
        $query->leftJoin('zp_user as requestor', function ($join) use ($userId) {
            $join->on('requestor.id', '=', $this->connection->raw((string) $userId));
        });

        $query->where(function ($q) use ($userId, $clientId) {
            $q->whereIn('zp_projects.id', function ($subquery) use ($userId) {
                $subquery->select('projectId')
                    ->from('zp_relationuserproject')
                    ->where('zp_relationuserproject.userId', $userId);
            })
                ->orWhere('zp_projects.psettings', 'all')
                ->orWhere(function ($q2) use ($clientId) {
                    $q2->where('zp_projects.psettings', 'clients')
                        ->where('zp_projects.clientId', $clientId);
                })
                ->orWhere('requestor.role', '>=', 40);
        });
    }
}
