<?php

namespace Leantime\Domain\Timesheets\Repositories;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Db\Repository;
use PDO;

class Timesheets extends Repository
{
    private ConnectionInterface $db;

    private DatabaseHelper $dbHelper;

    public array $kind = [
        'GENERAL_BILLABLE' => 'label.general_billable',
        'GENERAL_NOT_BILLABLE' => 'label.general_not_billable',
        'PROJECTMANAGEMENT' => 'label.projectmanagement',
        'DEVELOPMENT' => 'label.development',
        'BUGFIXING_NOT_BILLABLE' => 'label.bugfixing_not_billable',
        'TESTING' => 'label.testing',
    ];

    /**
     * Get database connection
     */
    public function __construct(DbCore $db, DatabaseHelper $dbHelper)
    {
        $this->db = $db->getConnection();
        $this->dbHelper = $dbHelper;
    }

    /**
     * Retrieves all timesheets based on the provided filters.
     *
     * @return array|false An array of timesheets or false if there was an error
     */
    public function getAll(?int $id, ?string $kind, ?CarbonInterface $dateFrom, ?CarbonInterface $dateTo, ?int $userId, ?string $invEmpl, ?string $invComp, ?string $paid, ?int $clientId, ?int $ticketFilter): array|false
    {
        $query = $this->db->table('zp_timesheets')
            ->select(
                'zp_timesheets.id',
                'zp_timesheets.userId',
                'zp_timesheets.ticketId',
                'zp_timesheets.workDate',
                'zp_timesheets.hours',
                'zp_timesheets.description',
                'zp_timesheets.kind',
                'zp_projects.name',
                'zp_projects.id AS projectId',
                'zp_clients.name AS clientName',
                'zp_clients.id AS clientId',
                'zp_timesheets.invoicedEmpl',
                'zp_timesheets.invoicedComp',
                'zp_timesheets.invoicedEmplDate',
                'zp_timesheets.invoicedCompDate',
                'zp_timesheets.paid',
                'zp_timesheets.paidDate',
                'zp_user.firstname',
                'zp_user.lastname',
                'zp_tickets.id as ticketId',
                'zp_tickets.headline',
                'zp_tickets.planHours',
                'zp_tickets.tags',
                'zp_tickets.modified',
                'milestone.headline as milestone'
            )
            ->leftJoin('zp_user', 'zp_timesheets.userId', '=', 'zp_user.id')
            ->leftJoin('zp_tickets', 'zp_timesheets.ticketId', '=', 'zp_tickets.id')
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->leftJoin('zp_tickets as milestone', 'zp_tickets.milestoneid', '=', 'milestone.id')
            ->whereBetween('zp_timesheets.workDate', [$dateFrom, $dateTo]);

        if ($id > 0) {
            $query->where('zp_tickets.projectId', $id);
        }

        if ($clientId > 0) {
            $query->where('zp_projects.clientId', $clientId);
        }

        if ($ticketFilter > 0) {
            $query->where('zp_tickets.id', $ticketFilter);
        }

        if ($kind != 'all') {
            $query->where('zp_timesheets.kind', $kind);
        }

        if ($userId != 'all' && $userId != null) {
            $query->where('zp_timesheets.userId', $userId);
        }

        if ($invComp == '1') {
            $query->where('zp_timesheets.invoicedComp', 1);
        }

        if ($invEmpl == '1') {
            $query->where('zp_timesheets.invoicedEmpl', 1);
        } elseif ($invEmpl == '0') {
            $query->where('zp_timesheets.invoicedEmpl', 0);
        }

        if ($paid == '1') {
            $query->where('zp_timesheets.paid', 1);
        }

        $query->groupBy(
            'zp_timesheets.id',
            'zp_timesheets.userId',
            'zp_timesheets.ticketId',
            'zp_timesheets.workDate',
            'zp_timesheets.hours',
            'zp_timesheets.description',
            'zp_timesheets.kind'
        );

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @TODO: Function is currently not used by core.
     */
    public function getUsersHours(int $id): mixed
    {
        $results = $this->db->table('zp_timesheets')
            ->select('id', 'hours', 'description')
            ->where('userId', $id)
            ->orderBy('id', 'desc')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getAllAccountTimesheets(?int $projectId): array|false
    {
        $query = $this->db->table('zp_timesheets')
            ->select(
                'zp_timesheets.id',
                'zp_timesheets.userId',
                'zp_timesheets.ticketId',
                'zp_timesheets.workDate',
                'zp_timesheets.hours',
                'zp_timesheets.description',
                'zp_timesheets.kind',
                'zp_projects.name',
                'zp_projects.id AS projectId',
                'zp_clients.name AS clientName',
                'zp_clients.id AS clientId',
                'zp_timesheets.invoicedEmpl',
                'zp_timesheets.invoicedComp',
                'zp_timesheets.invoicedEmplDate',
                'zp_timesheets.invoicedCompDate',
                'zp_timesheets.paid',
                'zp_timesheets.paidDate',
                'zp_timesheets.modified',
                'zp_user.firstname',
                'zp_user.lastname',
                'zp_tickets.id as ticketId',
                'zp_tickets.headline',
                'zp_tickets.planHours',
                'zp_tickets.tags',
                'milestone.headline as milestone'
            )
            ->leftJoin('zp_user', 'zp_timesheets.userId', '=', 'zp_user.id')
            ->leftJoin('zp_tickets', 'zp_timesheets.ticketId', '=', 'zp_tickets.id')
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->leftJoin('zp_tickets as milestone', 'zp_tickets.milestoneid', '=', 'milestone.id')
            ->where(function ($q) {
                $userId = session('userdata.id') ?? '-1';
                $clientId = session('userdata.clientId') ?? '-1';
                $requesterRole = session()->exists('userdata') ? session('userdata.role') : -1;

                $q->whereIn('zp_tickets.projectId', function ($subquery) use ($userId) {
                    $subquery->select('projectId')
                        ->from('zp_relationuserproject')
                        ->where('userId', $userId);
                })
                    ->orWhere('zp_projects.psettings', 'all')
                    ->orWhere(function ($q2) use ($clientId) {
                        $q2->where('zp_projects.psettings', 'clients')
                            ->where('zp_projects.clientId', $clientId);
                    })
                    ->orWhere(function ($q3) use ($requesterRole) {
                        if ($requesterRole === 'admin' || $requesterRole === 'manager') {
                            $q3->whereRaw('1=1');
                        }
                    });
            });

        // If user is not a manager, only pull their own timesheet entries
        if (session('userdata.role') !== 'admin' && session('userdata.role') !== 'manager') {
            $query->where('zp_timesheets.userId', session('userdata.id') ?? '-1');
        }

        if (isset($projectId) && $projectId > 0) {
            $query->where('zp_projects.id', $projectId);
        }

        $query->groupBy(
            'zp_timesheets.id',
            'zp_timesheets.userId',
            'zp_timesheets.ticketId',
            'zp_timesheets.workDate',
            'zp_timesheets.hours',
            'zp_timesheets.description',
            'zp_timesheets.kind'
        );

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * Retrieves the total number of hours booked from the timesheets table.
     *
     * @return mixed The total number of hours booked, or 0 if no hours are booked.
     */
    public function getHoursBooked(): mixed
    {
        $result = $this->db->table('zp_timesheets')
            ->selectRaw('SUM(hours) AS hoursBooked')
            ->first();

        return $result->hoursBooked ?? 0;
    }

    public function getWeeklyTimesheets(int $projectId, CarbonInterface $fromDate, int $userId = 0): mixed
    {
        if (! $fromDate->isUtc()) {
            $fromDate = $fromDate->copy()->setTimezone('UTC');
        }

        $endDate = $fromDate->copy()->addDays(7);

        $query = $this->db->table('zp_timesheets')
            ->select(
                'zp_timesheets.id',
                'zp_timesheets.userId',
                'zp_timesheets.ticketId',
                'zp_timesheets.workDate as workDate',
                'zp_timesheets.hours',
                'zp_timesheets.description',
                'zp_timesheets.kind',
                'zp_timesheets.invoicedEmpl',
                'zp_timesheets.invoicedComp',
                'zp_timesheets.invoicedEmplDate',
                'zp_timesheets.invoicedCompDate',
                'zp_timesheets.paid',
                'zp_timesheets.paidDate',
                'zp_timesheets.kind',
                'zp_timesheets.modified',
                'zp_tickets.headline',
                'zp_tickets.planHours',
                'zp_projects.name',
                'zp_projects.id AS projectId',
                'zp_projects.clientId AS clientId',
                'zp_clients.name AS clientName'
            )
            ->leftJoin('zp_tickets', 'zp_tickets.id', '=', 'zp_timesheets.ticketId')
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_clients', 'zp_clients.id', '=', 'zp_projects.clientId')
            ->where('zp_timesheets.workDate', '>=', $fromDate->format('Y-m-d H:i:s'))
            ->where('zp_timesheets.workDate', '<', $endDate->format('Y-m-d H:i:s'))
            ->where('zp_timesheets.userId', $userId)
            ->where('hours', '>', 0);

        if ($projectId > 0) {
            $query->where('zp_tickets.projectId', $projectId);
        }

        $query->orderBy('zp_timesheets.ticketId')
            ->orderBy('zp_timesheets.kind')
            ->orderBy('zp_timesheets.workDate', 'desc');

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * getUsersTicketHours - get the total hours
     *
     * @return int|mixed
     */
    public function getUsersTicketHours(int $ticketId, int $userId): mixed
    {
        // Use raw SQL for DATE_FORMAT as it's MySQL/PostgreSQL specific
        $dateFormatSql = match ($this->dbHelper->getDriverName()) {
            'mysql' => "DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')",
            'pgsql' => "TO_CHAR(zp_timesheets.workDate, 'YYYY-MM-DD')",
            default => "DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')",
        };

        $result = $this->db->table('zp_timesheets')
            ->selectRaw('SUM(hours) AS sumHours')
            ->where('ticketId', $ticketId)
            ->where('userId', $userId)
            ->groupByRaw($dateFormatSql)
            ->first();

        return $result->sumHours ?? 0;
    }

    /**
     * getTime - get a specific time entry
     */
    public function getTimesheet(int $id): mixed
    {
        $result = $this->db->table('zp_timesheets')
            ->select(
                'zp_timesheets.id',
                'zp_timesheets.userId',
                'zp_timesheets.ticketId',
                'zp_timesheets.workDate',
                'zp_timesheets.hours',
                'zp_timesheets.description',
                'zp_timesheets.kind',
                'zp_projects.id AS projectId',
                'zp_timesheets.invoicedEmpl',
                'zp_timesheets.invoicedComp',
                'zp_timesheets.invoicedEmplDate',
                'zp_timesheets.invoicedCompDate',
                'zp_timesheets.paid',
                'zp_timesheets.paidDate',
                'zp_timesheets.modified'
            )
            ->leftJoin('zp_tickets', 'zp_timesheets.ticketId', '=', 'zp_tickets.id')
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->where('zp_timesheets.id', $id)
            ->first();

        return $result ? (array) $result : false;
    }

    /**
     * getProjectHours - get the Project hours for a specific project
     *
     * @TODO: Function is currently not used by core.
     *
     * @return mixed
     */
    public function getProjectHours(int $projectId)
    {
        // Note: WITH ROLLUP is MySQL-specific and not supported in PostgreSQL
        // This would need a different approach for PostgreSQL if this method is used
        $monthSql = match ($this->dbHelper->getDriverName()) {
            'mysql' => 'MONTH(zp_timesheets.workDate)',
            'pgsql' => 'EXTRACT(MONTH FROM zp_timesheets.workDate)::integer',
            default => 'MONTH(zp_timesheets.workDate)',
        };

        $results = $this->db->table('zp_timesheets')
            ->selectRaw("{$monthSql} AS month")
            ->selectRaw('SUM(zp_timesheets.hours) AS summe')
            ->leftJoin('zp_tickets', 'zp_timesheets.ticketId', '=', 'zp_tickets.id')
            ->where('zp_tickets.projectId', $projectId)
            ->groupByRaw($monthSql)
            ->limit(12)
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * getLoggedHoursForTicket - get the Ticket hours for a specific ticket
     *
     * @throws BindingResolutionException
     */
    public function getLoggedHoursForTicket(int $ticketId): array
    {
        $dateFormatYearSql = match ($this->dbHelper->getDriverName()) {
            'mysql' => "YEAR(zp_timesheets.workDate) AS year,
                        DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') AS utc,
                        DATE_FORMAT(zp_timesheets.workDate, '%M') AS monthName,
                        DATE_FORMAT(zp_timesheets.workDate, '%m') AS month",
            'pgsql' => "EXTRACT(YEAR FROM zp_timesheets.workDate)::integer AS year,
                        TO_CHAR(zp_timesheets.workDate, 'YYYY-MM-DD') AS utc,
                        TO_CHAR(zp_timesheets.workDate, 'Month') AS monthName,
                        TO_CHAR(zp_timesheets.workDate, 'MM') AS month",
            default => "YEAR(zp_timesheets.workDate) AS year,
                        DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') AS utc,
                        DATE_FORMAT(zp_timesheets.workDate, '%M') AS monthName,
                        DATE_FORMAT(zp_timesheets.workDate, '%m') AS month",
        };

        $groupBySql = match ($this->dbHelper->getDriverName()) {
            'mysql' => "DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')",
            'pgsql' => "TO_CHAR(zp_timesheets.workDate, 'YYYY-MM-DD')",
            default => "DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')",
        };

        $results = $this->db->table('zp_timesheets')
            ->selectRaw($dateFormatYearSql)
            ->addSelect('zp_timesheets.workdate')
            ->selectRaw('SUM(ROUND(zp_timesheets.hours, 2)) AS summe')
            ->where('zp_timesheets.ticketId', $ticketId)
            ->where('workDate', '<>', '0000-00-00 00:00:00')
            ->where('workDate', '<>', '1969-12-31 00:00:00')
            ->groupByRaw($groupBySql)
            ->orderBy('utc')
            ->get();

        $values = array_map(fn ($item) => (array) $item, $results->toArray());
        $returnValues = [];

        if (count($values) > 0) {
            try {
                $startDate = dtHelper()->parseDbDateTime($values[0]['workdate'])->startOfMonth();
                $endDate = dtHelper()->parseDbDateTime(last($values)['workdate'])->lastOfMonth();

                $range = CarbonPeriod::since($startDate)->days(1)->until($endDate);
                foreach ($range as $key => $date) {
                    $utc = $date->format('Y-m-d');
                    $returnValues[$utc] = [
                        'utc' => $utc,
                        'summe' => 0,
                    ];
                }

                foreach ($values as $row) {
                    $returnValues[$row['utc']]['summe'] = $row['summe'];
                }
            } catch (\Exception $e) {
                // Some broken date formats in the db. Log error and return empty results.
                report($e);

                $utc = dtHelper()->dbNow()->format('Y-m-d H:i:s');
                $returnValues[$utc] = [
                    'utc' => $utc,
                    'summe' => 0,
                ];
            }
        } else {
            $utc = dtHelper()->dbNow()->format('Y-m-d H:i:s');
            $returnValues[$utc] = [
                'utc' => $utc,
                'summe' => 0,
            ];
        }

        return $returnValues;
    }

    public function getTimesheetsByTicket($id)
    {
        $dateFormatSql = match ($this->dbHelper->getDriverName()) {
            'mysql' => "YEAR(zp_timesheets.workDate) AS year,
                        DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') AS utc,
                        DATE_FORMAT(zp_timesheets.workDate, '%M') AS monthName,
                        DATE_FORMAT(zp_timesheets.workDate, '%m') AS month",
            'pgsql' => "EXTRACT(YEAR FROM zp_timesheets.workDate)::integer AS year,
                        TO_CHAR(zp_timesheets.workDate, 'YYYY-MM-DD') AS utc,
                        TO_CHAR(zp_timesheets.workDate, 'Month') AS monthName,
                        TO_CHAR(zp_timesheets.workDate, 'MM') AS month",
            default => "YEAR(zp_timesheets.workDate) AS year,
                        DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') AS utc,
                        DATE_FORMAT(zp_timesheets.workDate, '%M') AS monthName,
                        DATE_FORMAT(zp_timesheets.workDate, '%m') AS month",
        };

        $groupBySql = match ($this->dbHelper->getDriverName()) {
            'mysql' => "DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')",
            'pgsql' => "TO_CHAR(zp_timesheets.workDate, 'YYYY-MM-DD')",
            default => "DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')",
        };

        $results = $this->db->table('zp_timesheets')
            ->selectRaw($dateFormatSql)
            ->addSelect('zp_timesheets.workdate')
            ->selectRaw('SUM(ROUND(zp_timesheets.hours, 2)) AS sum')
            ->where('zp_timesheets.ticketId', $id)
            ->where('workDate', '<>', '0000-00-00 00:00:00')
            ->where('workDate', '<>', '1969-12-31 00:00:00')
            ->groupByRaw($groupBySql)
            ->orderBy('utc')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * isClocked - Checks to see whether a user is clocked in
     *
     * @param  int  $id  $id
     */
    public function isClocked(int $id): false|array
    {
        if (! session()->exists('userdata')) {
            return false;
        }

        $result = $this->db->table('zp_punch_clock')
            ->select(
                'zp_punch_clock.id',
                'zp_punch_clock.userId',
                'zp_punch_clock.minutes',
                'zp_punch_clock.hours',
                'zp_punch_clock.punchIn',
                'zp_tickets.headline',
                'zp_tickets.id as ticketId'
            )
            ->leftJoin('zp_tickets', 'zp_punch_clock.id', '=', 'zp_tickets.id')
            ->where('zp_punch_clock.userId', session('userdata.id'))
            ->limit(1)
            ->first();

        if (! $result) {
            return false;
        }

        $onTheClock = [];
        $onTheClock['id'] = $result->id;
        $onTheClock['since'] = $result->punchIn;
        $onTheClock['headline'] = $result->headline;
        $start_date = new Carbon($result->punchIn, 'UTC');
        $since_start = $start_date->diff(Carbon::now(session('usersettings.timezone'))->setTimezone('UTC'));

        $r = $since_start->format('%H:%I');

        $onTheClock['totalTime'] = $r;

        return $onTheClock;
    }

    /**
     * addTime - add user-specific time entry
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function addTime(array $values): void
    {
        // Laravel Query Builder doesn't support ON DUPLICATE KEY UPDATE well
        // Use raw query for this MySQL-specific feature
        $query = "INSERT INTO zp_timesheets (
            userId,
            ticketId,
            workDate,
            hours,
            kind,
            description,
            invoicedEmpl,
            invoicedComp,
            invoicedEmplDate,
            invoicedCompDate,
            rate,
            paid,
            paidDate,
            modified
        ) VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        ) ON DUPLICATE KEY UPDATE
             hours = hours + ?,
             description = CONCAT(?, '\n', ?, '\n', '--', '\n\n', description)";

        $query = self::dispatch_filter('sql', $query);

        $this->db->insert($query, [
            $values['userId'],
            $values['ticket'],
            $values['date'],
            $values['hours'],
            $values['kind'],
            $values['description'] ?? '',
            $values['invoicedEmpl'] ?? '',
            $values['invoicedComp'] ?? '',
            $values['invoicedEmplDate'] ?? '',
            $values['invoicedCompDate'] ?? '',
            $values['rate'] ?? '',
            $values['paid'] ?? '',
            $values['paidDate'] ?? '',
            date('Y-m-d H:i:s'),
            $values['hours'],
            $values['date'],
            $values['description'] ?? '',
        ]);

        $this->cleanUpEmptyTimesheets();
    }

    /**
     * punchIn - clock in on a specified ticket
     */
    public function punchIn(int $ticketId): bool
    {
        $userId = session('userdata.id');

        if (empty($userId)) {
            Log::warning('punchIn: No userId in session');

            return false;
        }

        try {
            return $this->db->table('zp_punch_clock')->insert([
                'id' => $ticketId,
                'userId' => $userId,
                'punchIn' => time(),
            ]);
        } catch (QueryException $e) {
            Log::error('punchIn failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * punchOut - clock out on whatever ticket is open for the user
     *
     * @throws BindingResolutionException
     */
    public function punchOut(int $ticketId): float|false|int
    {
        $result = $this->db->table('zp_punch_clock')
            ->select('*')
            ->where('userId', session('userdata.id'))
            ->where('id', $ticketId)
            ->limit(1)
            ->first();

        if (! $result) {
            return false;
        }

        $inTimestamp = $result->punchIn;
        $outTimestamp = time();

        $seconds = ($outTimestamp - $inTimestamp);
        $totalMinutesWorked = $seconds / 60;
        $hoursWorked = round(($totalMinutesWorked / 60), 2);

        $this->db->table('zp_punch_clock')
            ->where('userId', session('userdata.id'))
            ->where('id', $ticketId)
            ->limit(1)
            ->delete();

        // At least 1 minutes
        if ($hoursWorked < 0.016) {
            return 0;
        }

        $userStartOfDay = dtHelper()::createFromTimestamp($inTimestamp, 'UTC')->setToUserTimezone()->startOfDay();

        // Use raw query for ON DUPLICATE KEY UPDATE (MySQL specific)
        $query = "INSERT INTO `zp_timesheets` (userId, ticketId, workDate, hours, kind, modified)
                  VALUES (?, ?, ?, ?, 'GENERAL_BILLABLE', ?)
                  ON DUPLICATE KEY UPDATE hours = hours + ?";

        $this->db->insert($query, [
            session('userdata.id'),
            $ticketId,
            $userStartOfDay->formatDateTimeForDb(),
            $hoursWorked,
            date('Y-m-d H:i:s'),
            $hoursWorked,
        ]);

        return $hoursWorked;
    }

    /**
     * addTime - add user-specific time entry
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function upsertTimesheetEntry(array $values): void
    {
        // Use raw query for ON DUPLICATE KEY UPDATE (MySQL specific)
        $query = 'INSERT INTO zp_timesheets (
                userId,
                ticketId,
                workDate,
                hours,
                kind,
                invoicedEmpl,
                invoicedComp,
                invoicedEmplDate,
                invoicedCompDate,
                rate,
                paid,
                paidDate,
                modified
            ) VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            ) ON DUPLICATE KEY UPDATE
                 hours = ?';

        $query = self::dispatch_filter('sql', $query);

        $this->db->insert($query, [
            $values['userId'],
            $values['ticket'],
            $values['date'],
            $values['hours'],
            $values['kind'],
            $values['invoicedEmpl'] ?? '',
            $values['invoicedComp'] ?? '',
            $values['invoicedEmplDate'] ?? '',
            $values['invoicedCompDate'] ?? '',
            $values['rate'] ?? '',
            $values['paid'] ?? '',
            $values['paidDate'] ?? '',
            date('Y-m-d H:i:s'),
            $values['hours'],
        ]);

        $this->cleanUpEmptyTimesheets();
    }

    /**
     * updatTime - update specific time entry
     */
    public function updateTime(array $values): void
    {
        $this->db->table('zp_timesheets')
            ->where('id', $values['id'])
            ->update([
                'ticketId' => $values['ticket'],
                'workDate' => $values['date'],
                'hours' => $values['hours'],
                'kind' => $values['kind'],
                'description' => $values['description'],
                'invoicedEmpl' => $values['invoicedEmpl'],
                'invoicedComp' => $values['invoicedComp'],
                'invoicedEmplDate' => $values['invoicedEmplDate'],
                'invoicedCompDate' => $values['invoicedCompDate'],
                'paid' => $values['paid'],
                'paidDate' => $values['paidDate'],
                'modified' => date('Y-m-d H:i:s'),
            ]);

        $this->cleanUpEmptyTimesheets();
    }

    /**
     * updatTime - update specific time entry
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function updateHours(array $values): void
    {
        // TO_DAYS is MySQL-specific, use a workaround for PostgreSQL
        $toDaysSql = match ($this->dbHelper->getDriverName()) {
            'mysql' => 'TO_DAYS(workDate) = TO_DAYS(?)',
            'pgsql' => 'DATE(workDate) = DATE(?)',
            default => 'TO_DAYS(workDate) = TO_DAYS(?)',
        };

        $query = "UPDATE zp_timesheets
            SET
                hours = ?,
                modified = ?
            WHERE
                userId = ?
                AND ticketId = ?
                AND kind = ?
                AND {$toDaysSql}
                LIMIT 1";

        $query = self::dispatch_filter('sql', $query);

        $this->db->update($query, [
            $values['hours'],
            date('Y-m-d H:i:s'),
            $values['userId'],
            $values['ticket'],
            $values['kind'],
            $values['date'],
        ]);

        $this->cleanUpEmptyTimesheets();
    }

    /**
     * updateInvoices
     */
    public function updateInvoices(array $invEmpl, array $invComp = [], array $paid = []): bool
    {
        $now = Carbon::now(session('usersettings.timezone'))->setTimezone('UTC')->format('Y-m-d H:i:s');
        $modified = date('Y-m-d H:i:s');

        foreach ($invEmpl as $row1) {
            $this->db->table('zp_timesheets')
                ->where('id', $row1)
                ->update([
                    'invoicedEmpl' => 1,
                    'invoicedEmplDate' => $now,
                    'modified' => $modified,
                ]);
        }

        foreach ($invComp as $row2) {
            $this->db->table('zp_timesheets')
                ->where('id', $row2)
                ->update([
                    'invoicedComp' => 1,
                    'invoicedCompDate' => $now,
                    'modified' => $modified,
                ]);
        }

        foreach ($paid as $row3) {
            $this->db->table('zp_timesheets')
                ->where('id', $row3)
                ->update([
                    'paid' => 1,
                    'paidDate' => $now,
                    'modified' => $modified,
                ]);
        }

        return true;
    }

    public function deleteTime(int $id): void
    {
        $this->db->table('zp_timesheets')
            ->where('id', $id)
            ->limit(1)
            ->delete();
    }

    /**
     * Get planned hours for a ticket
     */
    public function getTicketPlanHours(int $ticketId): float
    {
        $query = 'SELECT planHours FROM zp_tickets WHERE id = :ticketId LIMIT 1';

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);
        $call->bindValue(':ticketId', $ticketId);

        $call->execute();

        $result = $call->fetch(PDO::FETCH_ASSOC);

        return (float) ($result['planHours'] ?? 0);
    }

    /**
     * Clean up empty timesheets.
     *
     * This function deletes all timesheets from the "zp_timesheets" table
     * where the hours value is equal to 0.
     */
    public function cleanUpEmptyTimesheets(): void
    {
        $this->db->table('zp_timesheets')
            ->where('hours', 0)
            ->delete();
    }
}
