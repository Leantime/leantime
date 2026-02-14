<?php

namespace Leantime\Domain\Reports\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Reports\Models\Reports as ReportsModel;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

class Reports
{
    private ConnectionInterface $db;

    private DatabaseHelper $dbHelper;

    /**
     * __construct - get database connection
     */
    public function __construct(DbCore $db, DatabaseHelper $dbHelper)
    {
        $this->db = $db->getConnection();
        $this->dbHelper = $dbHelper;
    }

    /**
     * Run ticket report for a project and optionally a sprint
     *
     * @throws BindingResolutionException
     */
    public function runTicketReport(int $projectId, string $sprintId): array|bool
    {
        $ticketRepo = app()->make(TicketRepository::class);
        $statusGroupsSQL = $ticketRepo->getStatusListGroupedByType($projectId);

        // Parse status groups from SQL format to arrays for cross-database compatibility
        $statusGroups = $this->dbHelper->parseStatusGroups($statusGroupsSQL);

        // Build cross-database compatible date expression
        $yesterdayDate = $this->dbHelper->yesterdayDate();

        // Build cross-database compatible string aggregation
        $ticketIds = $this->dbHelper->stringAggregate('zp_tickets.id');

        // Build query with query builder
        $query = $this->db->table('zp_tickets');

        // Select sprint column or -1 based on whether sprintId is provided
        if ($sprintId !== '') {
            $query->selectRaw('sprint AS "sprintId"');
        } else {
            $query->selectRaw('-1 AS "sprintId"');
        }

        // Build the select statement with cross-database functions
        $query->selectRaw('"projectId"')
            ->selectRaw("{$yesterdayDate} AS date")
            ->selectRaw('COUNT(DISTINCT zp_tickets.id) AS sum_todos')
            ->selectRaw(
                'SUM(CASE WHEN status IN ('.implode(',', $statusGroups['NEW'] ?: [0]).') THEN 1 ELSE 0 END) AS sum_open_todos'
            )
            ->selectRaw(
                'SUM(CASE WHEN status IN ('.implode(',', $statusGroups['INPROGRESS'] ?: [0]).') THEN 1 ELSE 0 END) AS sum_progres_todos'
            )
            ->selectRaw(
                'SUM(CASE WHEN status IN ('.implode(',', $statusGroups['DONE'] ?: [0]).') THEN 1 ELSE 0 END) AS sum_closed_todos'
            )
            ->selectRaw('SUM("planHours") AS sum_planned_hours')
            ->selectRaw('SUM("hourRemaining") AS sum_estremaining_hours')
            ->selectRaw('SUM(zp_tickets.storypoints) AS sum_points')
            ->selectRaw(
                'SUM(CASE WHEN status IN ('.implode(',', $statusGroups['NEW'] ?: [0]).') THEN zp_tickets.storypoints ELSE 0 END) AS sum_points_open'
            )
            ->selectRaw(
                'SUM(CASE WHEN status IN ('.implode(',', $statusGroups['INPROGRESS'] ?: [0]).') THEN zp_tickets.storypoints ELSE 0 END) AS sum_points_progress'
            )
            ->selectRaw(
                'SUM(CASE WHEN status IN ('.implode(',', $statusGroups['DONE'] ?: [0]).') THEN zp_tickets.storypoints ELSE 0 END) AS sum_points_done'
            )
            ->selectRaw('SUM(CASE WHEN zp_tickets.storypoints = 1 THEN 1 ELSE 0 END) AS sum_todos_xs')
            ->selectRaw('SUM(CASE WHEN zp_tickets.storypoints = 2 THEN 1 ELSE 0 END) AS sum_todos_s')
            ->selectRaw('SUM(CASE WHEN zp_tickets.storypoints = 3 THEN 1 ELSE 0 END) AS sum_todos_m')
            ->selectRaw('SUM(CASE WHEN zp_tickets.storypoints = 5 THEN 1 ELSE 0 END) AS sum_todos_l')
            ->selectRaw('SUM(CASE WHEN zp_tickets.storypoints = 8 THEN 1 ELSE 0 END) AS sum_todos_xl')
            ->selectRaw('SUM(CASE WHEN zp_tickets.storypoints = 13 THEN 1 ELSE 0 END) AS sum_todos_xxl')
            ->selectRaw('SUM(CASE WHEN zp_tickets.storypoints IS NULL OR zp_tickets.storypoints = 0 THEN 1 ELSE 0 END) AS sum_todos_none')
            ->selectRaw("{$ticketIds} AS tickets")
            ->selectRaw('SUM("planHours") / COUNT(zp_tickets.id) AS daily_avg_hours_planned_todo')
            ->selectRaw('SUM("planHours") / NULLIF(SUM(zp_tickets.storypoints), 0) AS daily_avg_hours_planned_point')
            ->selectRaw('SUM("hourRemaining") / COUNT(zp_tickets.id) AS daily_avg_hours_remaining_todo')
            ->selectRaw('SUM("hourRemaining") / NULLIF(SUM(zp_tickets.storypoints), 0) AS daily_avg_hours_remaining_point')
            ->where('projectId', $projectId)
            ->where('zp_tickets.type', '<>', 'subtask')
            ->where('zp_tickets.type', '<>', 'milestone');

        // Add sprint filter if provided
        if ($sprintId !== '') {
            $query->where('sprint', $sprintId)
                ->groupBy('projectId', 'sprint');
        } else {
            $query->groupBy('projectId');
        }

        $result = $query->first();
        $valuesTickets = $result ? (array) $result : false;

        $storyPoints = isset($valuesTickets['sum_points']) && $valuesTickets['sum_points'] > 0
            ? $valuesTickets['sum_points']
            : 1;

        // Timesheet Reports using query builder
        $timesheetQuery = $this->db->table('zp_tickets')
            ->selectRaw('ROUND(CAST(SUM(zp_timesheets.hours) AS DECIMAL(10,2)), 2) AS sum_logged_hours')
            ->selectRaw('ROUND(CAST(SUM(zp_timesheets.hours) / NULLIF(COUNT(DISTINCT zp_tickets.id), 0) AS DECIMAL(10,2)), 2) AS daily_avg_hours_booked_todo')
            ->selectRaw('ROUND(CAST(SUM(zp_timesheets.hours) / ? AS DECIMAL(10,2)), 2) AS daily_avg_hours_booked_point', [$storyPoints])
            ->leftJoin('zp_timesheets', 'zp_tickets.id', '=', 'zp_timesheets.ticketId')
            ->where('projectId', $projectId)
            ->where('zp_tickets.type', '<>', 'subtask')
            ->where('zp_tickets.type', '<>', 'milestone');

        if ($sprintId !== '') {
            $timesheetQuery->where('sprint', $sprintId)
                ->groupBy('projectId', 'sprint');
        } else {
            $timesheetQuery->groupBy('projectId');
        }

        $timesheetResult = $timesheetQuery->first();
        $valueTimesheets = $timesheetResult ? (array) $timesheetResult : false;

        // Number of users
        $projectService = app()->make(ProjectRepository::class);
        $users = $projectService->getUsersAssignedToProject($projectId);

        $numberOfUsers = is_array($users) ? count($users) : 0;

        if (is_array($valuesTickets) && is_array($valueTimesheets)) {
            $values = array_merge($valuesTickets, $valueTimesheets);
            $values['sum_teammembers'] = $numberOfUsers;
        } else {
            $values = false;
        }

        return $values;
    }

    public function checkLastReportEntries(int $projectId): false|array
    {
        $results = $this->db->table('zp_stats')
            ->whereRaw($this->dbHelper->isYesterday('date'))
            ->where('projectId', $projectId)
            ->limit(2)
            ->get();

        return $results->map(function ($row) {
            $report = new ReportsModel;
            foreach ((array) $row as $key => $value) {
                if (property_exists($report, $key)) {
                    $report->$key = $value;
                }
            }

            return $report;
        })->toArray();
    }

    public function addReport(array|object $report): void
    {
        $report = (object) $report;

        $this->db->table('zp_stats')->insert([
            'sprintId' => $report->sprintId,
            'projectId' => $report->projectId,
            'date' => $report->date,
            'sum_todos' => $report->sum_todos,
            'sum_open_todos' => $report->sum_open_todos,
            'sum_progres_todos' => $report->sum_progres_todos,
            'sum_closed_todos' => $report->sum_closed_todos,
            'sum_planned_hours' => $report->sum_planned_hours,
            'sum_estremaining_hours' => $report->sum_estremaining_hours,
            'sum_logged_hours' => $report->sum_logged_hours,
            'sum_points' => $report->sum_points,
            'sum_points_done' => $report->sum_points_done,
            'sum_points_progress' => $report->sum_points_progress,
            'sum_points_open' => $report->sum_points_open,
            'sum_todos_xs' => $report->sum_todos_xs,
            'sum_todos_s' => $report->sum_todos_s,
            'sum_todos_m' => $report->sum_todos_m,
            'sum_todos_l' => $report->sum_todos_l,
            'sum_todos_xl' => $report->sum_todos_xl,
            'sum_todos_xxl' => $report->sum_todos_xxl,
            'sum_todos_none' => $report->sum_todos_none,
            'tickets' => $report->tickets,
            'daily_avg_hours_booked_todo' => $report->daily_avg_hours_booked_todo,
            'daily_avg_hours_booked_point' => $report->daily_avg_hours_booked_point,
            'daily_avg_hours_planned_todo' => $report->daily_avg_hours_planned_todo,
            'daily_avg_hours_planned_point' => $report->daily_avg_hours_planned_point,
            'daily_avg_hours_remaining_point' => $report->daily_avg_hours_remaining_point,
            'daily_avg_hours_remaining_todo' => $report->daily_avg_hours_remaining_todo,
            'sum_teammembers' => $report->sum_teammembers,
        ]);
    }

    /**
     * @return array
     */
    public function getSprintReport(int $sprint): array|false
    {
        $results = $this->db->table('zp_stats')
            ->where('sprintId', $sprint)
            ->orderBy('date', 'asc')
            ->get();

        return $results->map(function ($row) {
            $report = new ReportsModel;
            foreach ((array) $row as $key => $value) {
                if (property_exists($report, $key)) {
                    $report->$key = $value;
                }
            }

            return $report;
        })->toArray();
    }

    public function getBacklogReport(int $project): false|array
    {
        $results = $this->db->table('zp_stats')
            ->where('projectId', $project)
            ->where('sprintId', 0)
            ->orderBy('date', 'asc')
            ->limit(95)
            ->get();

        return $results->map(function ($row) {
            $report = new ReportsModel;
            foreach ((array) $row as $key => $value) {
                if (property_exists($report, $key)) {
                    $report->$key = $value;
                }
            }

            return $report;
        })->toArray();
    }

    public function getFullReport(int $project): false|array
    {
        $results = $this->db->table('zp_stats')
            ->select(
                'date',
                $this->db->raw('SUM(sum_todos) AS sum_todos'),
                $this->db->raw('SUM(sum_open_todos) AS sum_open_todos'),
                $this->db->raw('SUM(sum_progres_todos) AS sum_progres_todos'),
                $this->db->raw('SUM(sum_closed_todos) AS sum_closed_todos'),
                $this->db->raw('SUM(sum_planned_hours) AS sum_planned_hours'),
                $this->db->raw('SUM(sum_estremaining_hours) AS sum_estremaining_hours'),
                $this->db->raw('ROUND(CAST(SUM(sum_logged_hours) AS DECIMAL(10,2)), 2) AS sum_logged_hours'),
                $this->db->raw('SUM(sum_points) AS sum_points'),
                $this->db->raw('SUM(sum_points_done) AS sum_points_done'),
                $this->db->raw('SUM(sum_points_progress) AS sum_points_progress'),
                $this->db->raw('SUM(sum_points_open) AS sum_points_open'),
                $this->db->raw('SUM(sum_todos_xs) AS sum_todos_xs'),
                $this->db->raw('SUM(sum_todos_s) AS sum_todos_s'),
                $this->db->raw('SUM(sum_todos_m) AS sum_todos_m'),
                $this->db->raw('SUM(sum_todos_l) AS sum_todos_l'),
                $this->db->raw('SUM(sum_todos_xl) AS sum_todos_xl'),
                $this->db->raw('SUM(sum_todos_xxl) AS sum_todos_xxl'),
                $this->db->raw('SUM(sum_todos_none) AS sum_todos_none'),
                $this->db->raw('SUM(CAST(tickets AS SIGNED)) AS tickets'),
                $this->db->raw('SUM(daily_avg_hours_booked_todo) AS daily_avg_hours_booked_todo'),
                $this->db->raw('SUM(daily_avg_hours_booked_point) AS daily_avg_hours_booked_point'),
                $this->db->raw('SUM(daily_avg_hours_planned_todo) AS daily_avg_hours_planned_todo'),
                $this->db->raw('SUM(daily_avg_hours_planned_point) AS daily_avg_hours_planned_point'),
                $this->db->raw('SUM(daily_avg_hours_remaining_point) AS daily_avg_hours_remaining_point'),
                $this->db->raw('SUM(daily_avg_hours_remaining_todo) AS daily_avg_hours_remaining_todo')
            )
            ->where('projectId', $project)
            ->where(function ($query) {
                $query->where('sprintId', '<', 1)
                    ->orWhereNull('sprintId');
            })
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(120)
            ->get();

        return $results->map(function ($row) {
            $report = new ReportsModel;
            foreach ((array) $row as $key => $value) {
                if (property_exists($report, $key)) {
                    $report->$key = $value;
                }
            }

            return $report;
        })->toArray();
    }
}
