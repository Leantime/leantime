<?php

namespace Leantime\Domain\Timesheets\Repositories;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Db\Repository;
use PDO;
use PHPUnit\Exception;

/**
 *
 */
class Timesheets extends Repository
{
    private DbCore $db;

    public array $kind = array(
        'GENERAL_BILLABLE' => 'label.general_billable',
        'GENERAL_NOT_BILLABLE' => 'label.general_not_billable',
        'PROJECTMANAGEMENT' => 'label.projectmanagement',
        'DEVELOPMENT' => 'label.development',
        'BUGFIXING_NOT_BILLABLE' => 'label.bugfixing_not_billable',
        'TESTING' => 'label.testing',
    );

    /**
     * Get database connection
     *
     * @access public
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieves all timesheets based on the provided filters.
     *
     * @param int|null $id
     * @param string|null $kind
     * @param CarbonInterface|null $dateFrom
     * @param CarbonInterface|null $dateTo
     * @param int|null $userId
     * @param string|null $invEmpl
     * @param string|null $invComp
     * @param string|null $paid
     * @param int|null $clientId
     * @param int|null $ticketFilter
     *
     * @return array|false An array of timesheets or false if there was an error
     */
    public function getAll(?int $id, ?string $kind, ?CarbonInterface $dateFrom, ?CarbonInterface $dateTo, ?int $userId, ?string $invEmpl, ?string $invComp, ?string $paid, ?int $clientId, ?int $ticketFilter): array|false
    {
        $query = "SELECT
                    zp_timesheets.id,
                    zp_timesheets.userId,
                    zp_timesheets.ticketId,
                    zp_timesheets.workDate,
                    zp_timesheets.hours,
                    zp_timesheets.description,
                    zp_timesheets.kind,
                    zp_projects.name,
                    zp_projects.id AS projectId,
                    zp_clients.name AS clientName,
                    zp_clients.id AS clientId,
                    zp_timesheets.invoicedEmpl,
                    zp_timesheets.invoicedComp,
                    zp_timesheets.invoicedEmplDate,
                    zp_timesheets.invoicedCompDate,
                    zp_timesheets.paid,
                    zp_timesheets.paidDate,
                    zp_user.firstname,
                    zp_user.lastname,
                    zp_tickets.id as ticketId,
                    zp_tickets.headline,
                    zp_tickets.planHours,
                    zp_tickets.tags,
                    zp_tickets.modified,
                    milestone.headline as milestone
                FROM
                    zp_timesheets
                LEFT JOIN zp_user ON zp_timesheets.userId = zp_user.id
                LEFT JOIN zp_tickets ON zp_timesheets.ticketId = zp_tickets.id
                LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
                LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
                LEFT JOIN zp_tickets milestone ON zp_tickets.milestoneid = milestone.id
                WHERE
                    ((TO_SECONDS(zp_timesheets.workDate) >= TO_SECONDS(:dateFrom)) AND (TO_SECONDS(zp_timesheets.workDate) <= (TO_SECONDS(:dateTo))))";

        if ($id > 0) {
            $query .= " AND (zp_tickets.projectId = :projectId)";
        }

        if ($clientId > 0) {
            $query .= " AND (zp_projects.clientId = :clientId)";
        }

        if ($ticketFilter > 0) {
            $query .= " AND (zp_tickets.id = :ticketFilter)";
        }

        if ($kind != 'all') {
            $query .= " AND (zp_timesheets.kind = :kind)";
        }

        if ($userId != 'all' && $userId != null) {
            $query .= " AND (zp_timesheets.userId = :userId)";
        }

        if ($invComp == '1') {
            $query .= " AND (zp_timesheets.invoicedComp = 1)";
        }

        if ($invEmpl == '1') {
            $query .= " AND (zp_timesheets.invoicedEmpl = 1)";
        }

        if ($paid == '1') {
            $query .= " AND (zp_timesheets.paid = 1)";
        }

        $query .= " GROUP BY
            zp_timesheets.id,
            zp_timesheets.userId,
            zp_timesheets.ticketId,
            zp_timesheets.workDate,
            zp_timesheets.hours,
            zp_timesheets.description,
            zp_timesheets.kind";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);

        $call->bindValue(':dateFrom', $dateFrom);
        $call->bindValue(':dateTo', $dateTo);

        if ($clientId > 0) {
            $call->bindValue(':clientId', $clientId);
        }

        if ($id > 0) {
            $call->bindValue(':projectId', $id);
        }

        if ($ticketFilter > 0) {
            $call->bindValue(':ticketFilter', $ticketFilter);
        }

        if ($kind != 'all') {
            $call->bindValue(':kind', $kind);
        }

        if ($userId != 'all' && $userId != null) {
            $call->bindValue(':userId', $userId);
        }

        return $call->fetchAll();
    }

    /**
     * @TODO: Function is currently not used by core.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getUsersHours(int $id): mixed
    {
        $sql = "SELECT id, hours, description FROM zp_timesheets WHERE userId=:userId ORDER BY id DESC";

        $call = $this->dbcall(func_get_args());

        $call->prepare($sql);
        $call->bindValue(':userId', $id, PDO::PARAM_INT);

        return $call->fetchAll();
    }

    public function getAllAccountTimesheets(?int $projectId): array|false
    {
        $query = "SELECT
                        zp_timesheets.id,
                        zp_timesheets.userId,
                        zp_timesheets.ticketId,
                        zp_timesheets.workDate,
                        zp_timesheets.hours,
                        zp_timesheets.description,
                        zp_timesheets.kind,
                        zp_projects.name,
                        zp_projects.id AS projectId,
                        zp_clients.name AS clientName,
                        zp_clients.id AS clientId,
                        zp_timesheets.invoicedEmpl,
                        zp_timesheets.invoicedComp,
                        zp_timesheets.invoicedEmplDate,
                        zp_timesheets.invoicedCompDate,
                        zp_timesheets.paid,
                        zp_timesheets.paidDate,
                        zp_timesheets.modified,
                        zp_user.firstname,
                        zp_user.lastname,
                        zp_tickets.id as ticketId,
                        zp_tickets.headline,
                        zp_tickets.planHours,
                        zp_tickets.tags,
                        milestone.headline as milestone
                    FROM
                        zp_timesheets
                    LEFT JOIN zp_user ON zp_timesheets.userId = zp_user.id
                    LEFT JOIN zp_tickets ON zp_timesheets.ticketId = zp_tickets.id
                    LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
                    LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
                    LEFT JOIN zp_tickets milestone ON zp_tickets.milestoneid = milestone.id
                    WHERE (
                        zp_tickets.projectId IN (SELECT projectId FROM zp_relationuserproject WHERE zp_relationuserproject.userId = :userId)
                        OR zp_projects.psettings = 'all'
                        OR (zp_projects.psettings = 'client' AND zp_projects.clientId = :clientId)
                        OR (:requesterRole = 'admin' OR :requesterRole = 'manager')
                    )";

        //If user is not a manager, only pull their own timesheet entries
        if( session("userdata.role") !== 'admin' && session("userdata.role") !== 'manager') {
            $query .= " AND zp_timesheets.userId = :userId";
        }

        if (isset($projectId) && $projectId  > 0) {
            $query .= " AND (zp_projects.id = :projectId)";
        }

        $query .= " GROUP BY
                zp_timesheets.id,
                zp_timesheets.userId,
                zp_timesheets.ticketId,
                zp_timesheets.workDate,
                zp_timesheets.hours,
                zp_timesheets.description,
                zp_timesheets.kind";

        $stmn = $this->db->database->prepare($query);

        if (session()->exists("userdata")) {
            $stmn->bindValue(':requesterRole', session("userdata.role"), PDO::PARAM_INT);
        } else {
            $stmn->bindValue(':requesterRole', -1, PDO::PARAM_INT);
        }

        $stmn->bindValue(':userId', session("userdata.id") ?? '-1', PDO::PARAM_INT);
        $stmn->bindValue(':clientId', session("userdata.clientId") ?? '-1', PDO::PARAM_INT);
        if (isset($projectId) && $projectId  > 0) {
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
        }

        $stmn->execute();
        $values = $stmn->fetchAll();

        $stmn->closeCursor();
        return $values;
    }

    /**
     * Retrieves the total number of hours booked from the timesheets table.
     *
     * @return mixed The total number of hours booked, or 0 if no hours are booked.
     */
    public function getHoursBooked(): mixed
    {
        $sql = "SELECT SUM(hours) AS hoursBooked
                FROM zp_timesheets;";

        $call = $this->dbcall(func_get_args());

        $call->prepare($sql);

        $values = $call->fetchAll();

        if (isset($values['hoursBooked']) === true) {
            return $values['hoursBooked'];
        }

        return 0;
    }

    /**
     * @param int             $projectId
     * @param CarbonInterface $fromDate
     * @param int             $userId
     *
     * @return mixed
     */
    public function getWeeklyTimesheets(int $projectId, CarbonInterface $fromDate, int $userId = 0): mixed
    {
        $query = "SELECT
            zp_timesheets.id,
            zp_timesheets.userId,
            zp_timesheets.ticketId,
            zp_timesheets.workDate as workDate,
            zp_timesheets.hours,
            zp_timesheets.description,
            zp_timesheets.kind,
            zp_timesheets.invoicedEmpl,
            zp_timesheets.invoicedComp,
            zp_timesheets.invoicedEmplDate,
            zp_timesheets.invoicedCompDate,
            zp_timesheets.paid,
            zp_timesheets.paidDate,
            zp_timesheets.kind,
            zp_timesheets.modified,
            zp_tickets.headline,
            zp_tickets.planHours,
            zp_projects.name,
            zp_projects.id AS projectId,
            zp_projects.clientId AS clientId,
            zp_clients.name AS clientName
        FROM
            zp_timesheets
        LEFT JOIN zp_tickets ON zp_tickets.id = zp_timesheets.ticketId
        LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
        LEFT JOIN zp_clients ON zp_clients.id = zp_projects.clientId
        WHERE
            (zp_timesheets.workDate >= :dateStart1 AND zp_timesheets.workDate < :dateEnd)
            AND (zp_timesheets.userId = :userId)
            AND hours > 0
        ";

        if ($projectId > 0) {
            $query .= " AND zp_tickets.projectId = :projectId";
        }

        $query .= " ORDER BY zp_timesheets.ticketId, zp_timesheets.kind, zp_timesheets.workDate DESC";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);

        if (!$fromDate->isUtc()) {
            $fromDate->setTimezone("UTC");
        }

        $call->bindValue(':dateStart1', $fromDate);

        $endDate = $fromDate->addDays(7);
        $call->bindValue(':dateEnd', $endDate);
        $call->bindValue(':userId', $userId, PDO::PARAM_INT);

        if ($projectId > 0) {
            $call->bindValue(':projectId', $projectId, PDO::PARAM_INT);
        }

        return $call->fetchAll();
    }

    /**
     * getUsersTicketHours - get the total hours
     *
     * @param int $ticketId
     * @param int $userId
     *
     * @return int|mixed
     */
    public function getUsersTicketHours(int $ticketId, int $userId): mixed
    {
        $sql = "SELECT SUM(hours) AS sumHours
                FROM `zp_timesheets`
                WHERE zp_timesheets.ticketId =:ticketId AND zp_timesheets.userId=:userId
                GROUP BY DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')";

        $call = $this->dbcall(func_get_args());

        $call->prepare($sql);
        $call->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
        $call->bindValue(':userId', $userId, PDO::PARAM_INT);

        $values = $call->fetchAll();

        if (count($values) > 0) {
            return $values[0]['sumHours'];
        } else {
            return 0;
        }
    }

    /**
     * getTime - get a specific time entry
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getTimesheet(int $id): mixed
    {
        $query = "SELECT
            zp_timesheets.id,
            zp_timesheets.userId,
            zp_timesheets.ticketId,
            zp_timesheets.workDate,
            zp_timesheets.hours,
            zp_timesheets.description,
            zp_timesheets.kind,
            zp_projects.id AS projectId,
            zp_timesheets.invoicedEmpl,
            zp_timesheets.invoicedComp,
            zp_timesheets.invoicedEmplDate,
            zp_timesheets.invoicedCompDate,
            zp_timesheets.paid,
            zp_timesheets.paidDate,
            zp_timesheets.modified

        FROM zp_timesheets
        LEFT JOIN zp_tickets ON zp_timesheets.ticketId = zp_tickets.id
        LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
        WHERE zp_timesheets.id = :id";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);

        $call->bindValue(':id', $id);

        return $call->fetch();
    }

    /**
     * getProjectHours - get the Project hours for a specific project
     *
     * @TODO: Function is currently not used by core.
     *
     * @param int $projectId
     *
     * @return mixed
     */
    public function getProjectHours(int $projectId)
    {
        $query = "SELECT
            MONTH(zp_timesheets.workDate) AS month,
            SUM(zp_timesheets.hours) AS summe
        FROM
            zp_timesheets LEFT JOIN zp_tickets ON zp_timesheets.ticketId = zp_tickets.id
        WHERE
            zp_tickets.projectId = :projectId
        GROUP BY
            MONTH(zp_timesheets.workDate)
            WITH ROLLUP
        LIMIT 12";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);
        $call->bindValue(':projectId', $projectId);

        return $call->fetchAll();
    }

    /**
     * getLoggedHoursForTicket - get the Ticket hours for a specific ticket
     *
     * @access public
     *
     * @param int $ticketId
     *
     * @return array
     *
     * @throws BindingResolutionException
     */
    public function getLoggedHoursForTicket(int $ticketId): array
    {
        $query = "SELECT
                YEAR(zp_timesheets.workDate) AS year,
                zp_timesheets.workdate,
                DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') AS utc,
                DATE_FORMAT(zp_timesheets.workDate, '%M') AS monthName,
                DATE_FORMAT(zp_timesheets.workDate, '%m') AS month,
                SUM(ROUND(zp_timesheets.hours, 2)) AS summe
            FROM
                zp_timesheets
            WHERE
                zp_timesheets.ticketId = :ticketId
                AND workDate <> '0000-00-00 00:00:00' AND workDate <> '1969-12-31 00:00:00'
            GROUP BY DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')
            ORDER BY utc";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);
        $call->bindValue(':ticketId', $ticketId);

        $values = $call->fetchAll();
        $returnValues = array();

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
                    $returnValues[$row['utc']]["summe"] = $row['summe'];
                }
            } catch (\Exception $e) {
                // Some broken date formats in the db. Log error and return empty results.
                report($e);

                $utc = dtHelper()->dbNow()->format("Y-m-d");
                $returnValues[$utc] = [
                    'utc' => $utc,
                    'summe' => 0,
                ];
            }
        } else {
            $utc = dtHelper()->dbNow()->format("Y-m-d");
            $returnValues[$utc] = [
                'utc' => $utc,
                'summe' => 0,
            ];
        }

        return $returnValues;
    }

    public function getTimesheetsByTicket($id) {

        $query = "SELECT
                YEAR(zp_timesheets.workDate) AS year,
                zp_timesheets.workdate,
                DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') AS utc,
                DATE_FORMAT(zp_timesheets.workDate, '%M') AS monthName,
                DATE_FORMAT(zp_timesheets.workDate, '%m') AS month,
                SUM(ROUND(zp_timesheets.hours, 2)) AS sum
            FROM
                zp_timesheets
            WHERE
                zp_timesheets.ticketId = :ticketId
                AND workDate <> '0000-00-00 00:00:00' AND workDate <> '1969-12-31 00:00:00'
            GROUP BY DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')
            ORDER BY utc";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);
        $call->bindValue(':ticketId', $id);

        $values = $call->fetchAll();

        return $values;
    }

    /**
     * isClocked - Checks to see whether a user is clocked in
     *
     * @param int $id $id
     *
     * @return array|false
     */
    public function isClocked(int $id): false|array
    {
        if (!session()->exists("userdata")) {
            return false;
        }

        $query = "SELECT
                 zp_punch_clock.id,
                 zp_punch_clock.userId,
                 zp_punch_clock.minutes,
                 zp_punch_clock.hours,
                 zp_punch_clock.punchIn,
                 zp_tickets.headline,
                 zp_tickets.id as ticketId
              FROM `zp_punch_clock`
              LEFT JOIN zp_tickets ON zp_punch_clock.id = zp_tickets.id WHERE zp_punch_clock.userId=:sessionId LIMIT 1";

        $onTheClock = false;

        $call = $this->dbcall(func_get_args());
        $call->prepare($query);
        $call->bindValue(':sessionId', session("userdata.id"));

        $results = $call->fetchAll();

        if (count($results) > 0) {
            $onTheClock = array();
            $onTheClock["id"] = $results[0]["id"];
            $onTheClock["since"] = $results[0]["punchIn"];
            $onTheClock["headline"] = $results[0]["headline"];
            $start_date = new Carbon($results[0]["punchIn"], 'UTC');
            $since_start = $start_date->diff(Carbon::now(session("usersettings.timezone"))->setTimezone('UTC'));

            $r = $since_start->format('%H:%I');

            $onTheClock["totalTime"] = $r;
        }

        return $onTheClock;
    }

    /**
     * addTime - add user-specific time entry
     *
     * @param array $values
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function addTime(array $values): void
    {
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
            :userId,
            :ticket,
            :date,
            :hours,
            :kind,
            :description,
            :invoicedEmpl,
            :invoicedComp,
            :invoicedEmplDate,
            :invoicedCompDate,
            :rate,
            :paid,
            :paidDate,
            :modified
        ) ON DUPLICATE KEY UPDATE
             hours = hours + :hours,
             description = CONCAT(:date, '\n', :description, '\n', '--', '\n\n', description)";

        $query = self::dispatch_filter('sql', $query);

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);

        $call->bindValue(':userId', $values['userId']);
        $call->bindValue(':ticket', $values['ticket']);
        $call->bindValue(':date', $values['date']);
        $call->bindValue(':kind', $values['kind']);
        $call->bindValue(':description', $values['description'] ?? '');
        $call->bindValue(':invoicedEmpl', $values['invoicedEmpl'] ?? '');
        $call->bindValue(':invoicedComp', $values['invoicedComp'] ?? '');
        $call->bindValue(':invoicedEmplDate', $values['invoicedEmplDate'] ?? '');
        $call->bindValue(':invoicedCompDate', $values['invoicedCompDate'] ?? '');
        $call->bindValue(':rate', $values['rate'] ?? '');
        $call->bindValue(':hours', $values['hours']);
        $call->bindValue(':paid', $values['paid'] ?? '');
        $call->bindValue(':paidDate', $values['paidDate'] ?? '');
        $call->bindValue(':modified', date("Y-m-d H:i:s"), PDO::PARAM_STR);

        $call->execute();

        $this->cleanUpEmptyTimesheets();
    }

    /**
     * punchIn - clock in on a specified ticket
     *
     * @param int $ticketId
     *
     * @return mixed
     */
    public function punchIn(int $ticketId): mixed
    {
        $query = "INSERT INTO `zp_punch_clock` (id, userId, punchIn) VALUES (:ticketId, :sessionId, :time)";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);

        $call->bindValue(':ticketId', $ticketId);
        $call->bindValue(':sessionId', session("userdata.id"));
        // Unix timestamp is by default UTC.
        $call->bindValue(':time', time());

        $value = $call->execute();

        return $value;
    }

    /**
     * punchOut - clock out on whatever ticket is open for the user
     *
     * @param int $ticketId
     *
     * @return float|false|int
     *
     * @throws BindingResolutionException
     */
    public function punchOut(int $ticketId): float|false|int
    {
        $query = "SELECT * FROM `zp_punch_clock` WHERE userId=:sessionId AND id = :ticketId LIMIT 1";

        $call = $this->dbcall(func_get_args(), ['dbcall_key' => 'select']);

        $call->prepare($query);

        $call->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
        $call->bindValue(':sessionId', session("userdata.id"), PDO::PARAM_INT);

        $result = $call->fetch();
        unset($call);

        if (!$result) {
            return false;
        }

        $inTimestamp = $result['punchIn'];
        $outTimestamp = time();

        $seconds =  ($outTimestamp - $inTimestamp);
        $totalMinutesWorked = $seconds / 60;
        $hoursWorked = round(($totalMinutesWorked / 60), 2);

        $query = "DELETE FROM `zp_punch_clock` WHERE userId=:sessionId AND id = :ticketId LIMIT 1 ";

        $call = $this->dbcall(func_get_args(), ['dbcall_key' => 'delete']);

        $call->prepare($query);

        $call->bindValue(':ticketId', $ticketId);
        $call->bindValue(':sessionId', session("userdata.id"));

        $call->execute();

        unset($call);

        // At least 1 minutes
        if ($hoursWorked < 0.016) {
            return 0;
        }

        $query = "INSERT INTO `zp_timesheets` (userId, ticketId, workDate, hours, kind, modified)
                  VALUES (:sessionId, :ticketId, :workDate, :hoursWorked, 'GENERAL_BILLABLE', :modified)
                  ON DUPLICATE KEY UPDATE hours = hours + :hoursWorked";


        $userStartOfDay = dtHelper()::createFromTimestamp($inTimestamp, "UTC")->setToUserTimezone()->startOfDay();

        $call = $this->dbcall(func_get_args(), ['dbcall_key' => 'insert']);
        $call->prepare($query);
        $call->bindValue(':ticketId', $ticketId);
        $call->bindValue(':sessionId', session("userdata.id"));
        $call->bindValue(':hoursWorked', $hoursWorked);
        $call->bindValue(':workDate', $userStartOfDay->formatDateTimeForDb());
        $call->bindValue(':modified',  date('Y-m-d H:i:s'));

        $call->execute();

        return $hoursWorked;
    }

    /**
     * addTime - add user-specific time entry
     *
     * @param array $values
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function upsertTimesheetEntry(array $values): void
    {
        $query = "INSERT INTO zp_timesheets (
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
                :userId,
                :ticket,
                :date,
                :hours,
                :kind,
                :invoicedEmpl,
                :invoicedComp,
                :invoicedEmplDate,
                :invoicedCompDate,
                :rate,
                :paid,
                :paidDate,
                :modified
            ) ON DUPLICATE KEY UPDATE
                 hours = :hours";

        $query = self::dispatch_filter('sql', $query);

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);

        $call->bindValue(':userId', $values['userId']);
        $call->bindValue(':ticket', $values['ticket']);
        $call->bindValue(':date', $values['date']);
        $call->bindValue(':kind', $values['kind']);
        $call->bindValue(':invoicedEmpl', $values['invoicedEmpl'] ?? '');
        $call->bindValue(':invoicedComp', $values['invoicedComp'] ?? '');
        $call->bindValue(':invoicedEmplDate', $values['invoicedEmplDate'] ?? '');
        $call->bindValue(':invoicedCompDate', $values['invoicedCompDate'] ?? '');
        $call->bindValue(':rate', $values['rate'] ?? '');
        $call->bindValue(':hours', $values['hours']);
        $call->bindValue(':paid', $values['paid'] ?? '');
        $call->bindValue(':paidDate', $values['paidDate'] ?? '');
        $call->bindValue(':modified', date("Y-m-d H:i:s"), PDO::PARAM_STR);

        $call->execute();

        $this->cleanUpEmptyTimesheets();
    }

    /**
     * updatTime - update specific time entry
     *
     * @param array $values
     *
     * @return void
     */
    public function updateTime(array $values): void
    {
        $query = "UPDATE
                zp_timesheets
            SET
                ticketId = :ticket,
                workDate = :date,
                hours = :hours,
                kind = :kind,
                description =:description,
                invoicedEmpl =:invoicedEmpl,
                invoicedComp =:invoicedComp,
                invoicedEmplDate =:invoicedEmplDate,
                invoicedCompDate =:invoicedCompDate,
                paid =:paid,
                paidDate =:paidDate,
                modified =:modified
            WHERE
                id = :id";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);
        $call->bindValue(':ticket', $values['ticket']);
        $call->bindValue(':date', $values['date']);
        $call->bindValue(':hours', $values['hours']);
        $call->bindValue(':kind', $values['kind']);
        $call->bindValue(':description', $values['description']);
        $call->bindValue(':invoicedEmpl', $values['invoicedEmpl']);
        $call->bindValue(':invoicedComp', $values['invoicedComp']);
        $call->bindValue(':invoicedEmplDate', $values['invoicedEmplDate']);
        $call->bindValue(':invoicedCompDate', $values['invoicedCompDate']);
        $call->bindValue(':paid', $values['paid']);
        $call->bindValue(':paidDate', $values['paidDate']);
        $call->bindValue(':id', $values['id']);
        $call->bindValue(':modified', date("Y-m-d H:i:s"), PDO::PARAM_STR);

        $call->execute();

        $this->cleanUpEmptyTimesheets();
    }

    /**
     * updatTime - update specific time entry
     *
     * @param array $values
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function updateHours(array $values): void
    {
        $query = "UPDATE
                zp_timesheets
            SET
                hours = :hours,
                modified =:modified
            WHERE
                userId = :userId
                AND ticketId = :ticketId
                AND kind = :kind
                AND TO_DAYS(workDate) = TO_DAYS(:date)
                LIMIT 1";

        $query = self::dispatch_filter('sql', $query);

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);
        $call->bindValue(':date', $values['date']);
        $call->bindValue(':hours', $values['hours']);
        $call->bindValue(':userId', $values['userId']);
        $call->bindValue(':ticketId', $values['ticket']);
        $call->bindValue(':kind', $values['kind']);
        $call->bindValue(':modified', date("Y-m-d H:i:s"), PDO::PARAM_STR);

        $call->execute();

        $this->cleanUpEmptyTimesheets();
    }

    /**
     * updateInvoices
     *
     * @param array $invEmpl
     * @param array $invComp
     * @param array $paid
     *
     * @return bool
     */
    public function updateInvoices(array $invEmpl, array $invComp = [], array $paid = []): bool
    {
        foreach ($invEmpl as $row1) {
            $query = "UPDATE zp_timesheets
                      SET invoicedEmpl = 1,
                          invoicedEmplDate = :date,
                          modified = :modified
                      WHERE id = :id ";

            $invEmplCall = $this->dbcall(func_get_args(), ['dbcall_key' => 'inv_empl']);
            $invEmplCall->prepare($query);
            $invEmplCall->bindValue(':id', $row1);
            $invEmplCall->bindValue(':date', Carbon::now(session("usersettings.timezone"))->setTimezone('UTC'));
            $invEmplCall->bindValue(':modified', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $invEmplCall->execute();

            unset($invEmplCall);
        }

        foreach ($invComp as $row2) {
            $query2 = "UPDATE zp_timesheets
                       SET invoicedComp = 1,
                           invoicedCompDate = :date,
                           modified = :modified
                       WHERE id = :id ";

            $invCompCall = $this->dbcall(func_get_args(), ['dbcall_key' => 'inv_comp']);
            $invCompCall->prepare($query2);
            $invCompCall->bindValue(':id', $row2);
            $invCompCall->bindValue(':date', Carbon::now(session("usersettings.timezone"))->setTimezone('UTC'));
            $invCompCall->bindValue(':modified', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $invCompCall->execute();

            unset($invCompCall);
        }

        foreach ($paid as $row3) {
            $query3 = "UPDATE zp_timesheets
                       SET paid = 1,
                           paidDate = :date,
                           modified = :modified
                       WHERE id = :id ";

            $paidCol = $this->dbcall(func_get_args(), ['dbcall_key' => 'paid']);
            $paidCol->prepare($query3);
            $paidCol->bindValue(':id', $row3);
            $paidCol->bindValue(':date', Carbon::now(session("usersettings.timezone"))->setTimezone('UTC'));
            $paidCol->bindValue(':modified', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $paidCol->execute();

            unset($paidCol);
        }

        return true;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteTime(int $id): void
    {
        $query = "DELETE FROM zp_timesheets WHERE id = :id LIMIT 1";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);
        $call->bindValue(':id', $id);

        $call->execute();
    }

    /**
     * Clean up empty timesheets.
     *
     * This function deletes all timesheets from the "zp_timesheets" table
     * where the hours value is equal to 0.
     *
     * @return void
     */
    public function cleanUpEmptyTimesheets(): void
    {
        $query = "DELETE FROM zp_timesheets WHERE hours = 0";

        $call = $this->dbcall(func_get_args());

        $call->prepare($query);

        $call->execute();
    }

}
