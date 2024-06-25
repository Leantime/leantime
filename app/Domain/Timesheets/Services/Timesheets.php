<?php

namespace Leantime\Domain\Timesheets\Services;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Tickets\Models\Tickets;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Domain\Users\Repositories\Users;

/**
 *
 */
class Timesheets
{
    private TimesheetRepository $timesheetsRepo;

    private Users $userRepo;

    public array $kind = array(
        'GENERAL_BILLABLE' => 'label.general_billable',
        'GENERAL_NOT_BILLABLE' => 'label.general_not_billable',
        'PROJECTMANAGEMENT' => 'label.projectmanagement',
        'DEVELOPMENT' => 'label.development',
        'BUGFIXING_NOT_BILLABLE' => 'label.bugfixing_not_billable',
        'TESTING' => 'label.testing',
    );

    /**
     * @param TimesheetRepository $timesheetsRepo
     */
    public function __construct(TimesheetRepository $timesheetsRepo, Users $userRepo)
    {
        $this->timesheetsRepo = $timesheetsRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * isClocked - Checks to see whether a user is clocked in
     *
     * @param int $sessionId
     *
     * @return array|false
     */
    public function isClocked(int $sessionId): false|array
    {
        return $this->timesheetsRepo->isClocked($sessionId);
    }

    /**
     * @param int $ticketId
     *
     * @return mixed
     */
    public function punchIn(int $ticketId): mixed
    {
        return $this->timesheetsRepo->punchIn($ticketId);
    }

    /**
     * @param int $ticketId
     *
     * @return false|float|int
     */
    public function punchOut(int $ticketId): float|false|int
    {
        return $this->timesheetsRepo->punchOut($ticketId);
    }

    /**
     * logTime, will always add hours or increment existing values
     *
     * @param int   $ticketId
     * @param array $params
     *
     * @return array|bool
     *
     * @throws BindingResolutionException
     * @throws MissingParameterException
     */
    public function logTime(int $ticketId, array $params): array|bool
    {
        // @TODO: Change to use value objects for more type safeness.
        $values = array(
            'userId' => $params["userId"] ?? session("userdata.id"),
            'ticket' => $ticketId,
            'date' => '',
            'kind' => $params['kind'] ?? '',
            'hours' => '',
            'rate' => '',
            'description' => '',
            'invoicedEmpl' => '',
            'invoicedComp' => '',
            'invoicedEmplDate' => '',
            'invoicedCompDate' => '',
            'paid' => '',
        );

        if (!isset($ticketId)) {
            throw new MissingParameterException("Ticket Id is a required field");
        }

        if (!isset($params['date'])) {
            throw new MissingParameterException("Date is a required field");
        }

        if (!isset($params['hours'])) {
            throw new MissingParameterException("Hours is a required field");
        }

        if (!isset($params['kind'])) {
            throw new MissingParameterException("Timesheet type is a required field");
        }

        if (empty($params['time'])) {
            $values['date'] = format($params['date'], "start", FromFormat::UserDateStartOfDay)->isoDateTime();
        } else {
            $values['date'] = format($params['date'], $params['time'], FromFormat::UserDateTime)->isoDateTime();
        }

        $values['hours'] = $params['hours'];
        $values['description'] = $params['description'] ?? '';

        $loggingUser = $this->userRepo->getUser($values['userId']);
        $values["rate"] = $loggingUser["wage"];

        $this->timesheetsRepo->addTime($values);

        return true;
    }

    /**
     * Upserts a time entry for a ticket. Will update hours based on the values provided, not touching descriptions
     *
     * @param int   $ticketId The ID of the ticket.
     * @param array $params   An associative array of parameters for the time entry.
     *     - userId: The ID of the user creating the time entry. Defaults to the ID of the logged-in user.
     *     - kind: The type of timesheet entry. Required.
     *     - date: The date of the time entry. Required.
     *     - hours: The number of hours for the time entry. Required.
     *
     * @return array|bool Returns true if the time entry was successfully upserted.
     *
     * @throws MissingParameterException If any of the required parameters are missing.
     * @throws BindingResolutionException
     */
    public function upsertTime(int $ticketId, array $params): array|bool
    {
        // @TODO: Change to use value objects for more type safeness.
        $values = array(
            'userId' => $params["userId"] ?? session("userdata.id"),
            'ticket' => $ticketId,
            'date' => '',
            'kind' => $params['kind'] ?? '',
            'hours' => '',
            'rate' => '',
            'invoicedEmpl' => '',
            'invoicedComp' => '',
            'invoicedEmplDate' => '',
            'invoicedCompDate' => '',
            'paid' => '',
        );

        if (!isset($ticketId)) {
            throw new MissingParameterException("Ticket Id is a required field");
        }

        if (!isset($params['date'])) {
            throw new MissingParameterException("Date is a required field");
        }

        if (!isset($params['hours'])) {
            throw new MissingParameterException("Hours is a required field");
        }

        if (!isset($params['kind'])) {
            throw new MissingParameterException("Timesheet type is a required field");
        }

        if (empty($params['timestamp'])) {
            $values['date'] = format($params['date'], "start", FromFormat::UserDateStartOfDay)->isoDateTime();
        } else {
            $values['date'] = dtHelper()->timestamp($params['timestamp'])->formatDateTimeForDb();
        }

        $values['hours'] = $params['hours'];

        $loggingUser = $this->userRepo->getUser($values['userId']);
        $values["rate"] = $loggingUser["wage"];

        $this->timesheetsRepo->upsertTimesheetEntry($values);

        return true;
    }

    /**
     * @param int $ticketId
     *
     * @return array
     */
    public function getLoggedHoursForTicketByDate(int $ticketId): array
    {
        return $this->timesheetsRepo->getLoggedHoursForTicket($ticketId);
    }

    /**
     * @param int $ticketId
     *
     * @return int|mixed
     */
    public function getSumLoggedHoursForTicket(int $ticketId): mixed
    {
        $result = $this->getLoggedHoursForTicketByDate($ticketId);

        $allHours = 0;
        foreach ($result as $row) {
            if ($row['summe']) {
                $allHours += $row['summe'];
            }
        }

        return $allHours;
    }

    /**
     * @param Tickets $ticket
     *
     * @return int|mixed
     */
    public function getRemainingHours(Tickets $ticket): mixed
    {
        $totalHoursLogged = $this->getSumLoggedHoursForTicket($ticket->id);
        $planHours = $ticket->planHours;

        $remaining = $planHours - $totalHoursLogged;

        if ($remaining < 0) {
            $remaining = 0;
        }

        return $remaining;
    }

    /**
     * @param int $ticketId
     * @param int $userId
     *
     * @return int|mixed
     */
    public function getUsersTicketHours(int $ticketId, int $userId): mixed
    {
        return  $this->timesheetsRepo->getUsersTicketHours($ticketId, $userId);
    }

    /**
     * @return array|string[]
     */
    public function getLoggableHourTypes(): array
    {
        return $this->timesheetsRepo->kind;
    }

    /**
     * @param CarbonInterface $dateFrom
     * @param CarbonInterface $dateTo
     * @param int             $projectId
     * @param string          $kind
     * @param int|null        $userId
     * @param string          $invEmpl
     * @param string          $invComp
     * @param string          $ticketFilter
     * @param string          $paid
     * @param string          $clientId
     *
     * @return array|false
     */
    public function getAll(CarbonInterface $dateFrom, CarbonInterface $dateTo, int $projectId = -1, string $kind = 'all', ?int $userId = null, string $invEmpl = '1', string $invComp = '1', string $ticketFilter = '-1', string $paid = '1', string $clientId = '-1'): array|false
    {
        return $this->timesheetsRepo->getAll(
            id: $projectId,
            kind: $kind,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            userId: $userId,
            invEmpl: $invEmpl,
            invComp: $invComp,
            paid: $paid,
            clientId: $clientId,
            ticketFilter: $ticketFilter
        );
    }

    /**
     * @param int $projectId
     * @param CarbonInterface $fromDate
     * @param int $userId
     *
     * @return array
     *
     * @throws BindingResolutionException
     */
    public function getWeeklyTimesheets(int $projectId, CarbonInterface $fromDate, int $userId = 0): array
    {
        // Get timesheet entries and group by day
        $allTimesheets = $this->timesheetsRepo->getWeeklyTimesheets(
            projectId: $projectId,
            fromDate: $fromDate,
            userId: $userId
        );

        // Timesheets are grouped by ticketId + type
        $timesheetGroups = [];
        foreach ($allTimesheets as $timesheet) {
            $currentWorkDate = dtHelper()->parseDbDateTime($timesheet['workDate']);

            // Detect timezone offset

            $workdateOffsetStart = ($currentWorkDate->setToUserTimezone()->secondsSinceMidnight() / 60 / 60);

            // Various Entries can be in different timezones and thus would not be caught by upsert or grouping by
            // default Creating new rows for each timezone adjustment
            //to avoid timezone collisions we disable adding new times to rows that were created in an different timezone
            $timezonedTime = $currentWorkDate->format("H:i:s");

            $groupKey = $timesheet["ticketId"] . "-" . $timesheet["kind"] . "-" . $timezonedTime;
            if (!isset($timesheetGroups[$groupKey])) {
                // Build an array of 7 days for the weekly timesheet. Include the start date of the current users
                // timezone in UTC. That way we can compare the dates coming from the db
                $timesheetGroups[$groupKey] = array(
                    "kind" =>  $timesheet["kind"],
                    "clientName" => $timesheet["clientName"],
                    "name" => $timesheet["name"],
                    "headline" => $timesheet["headline"],
                    "ticketId" => $timesheet["ticketId"],
                    "hasTimesheetOffset" => $workdateOffsetStart !== 0,
                    "day1" => array(
                        "start" =>  $fromDate,
                        "end" => $fromDate->addHours(23)->addMinutes(59),
                        "actualWorkDate" => $workdateOffsetStart === 0 ? $fromDate->setTime($currentWorkDate->hour, $currentWorkDate->minute, $currentWorkDate->second) : "",

                        "hours" => 0,
                        "description" => "",
                    ),
                    "day2" => array(
                        "start" =>  $fromDate->addDays(1),
                        "end" => $fromDate->addDays(1)->addHours(23)->addMinutes(59),
                        "actualWorkDate" => $workdateOffsetStart === 0 ? $fromDate->addDays(1)->setTime($currentWorkDate->hour, $currentWorkDate->minute, $currentWorkDate->second) : "",
                        "hours" => 0,
                        "description" => "",
                    ),
                    "day3" => array(
                        "start" =>  $fromDate->addDays(2),
                        "end" => $fromDate->addDays(2)->addHours(23)->addMinutes(59),
                        "actualWorkDate" => $workdateOffsetStart === 0 ? $fromDate->addDays(2)->setTime($currentWorkDate->hour, $currentWorkDate->minute, $currentWorkDate->second) : "",
                        "hours" => 0,
                        "description" => "",
                    ),
                    "day4" => array(
                        "start" =>  $fromDate->addDays(3),
                        "end" => $fromDate->addDays(3)->addHours(23)->addMinutes(59),
                        "actualWorkDate" => $workdateOffsetStart === 0 ? $fromDate->addDays(3)->setTime($currentWorkDate->hour, $currentWorkDate->minute, $currentWorkDate->second) : "",
                        "hours" => 0,
                        "description" => "",
                    ),
                    "day5" => array(
                        "start" =>  $fromDate->addDays(4),
                        "end" => $fromDate->addDays(4)->addHours(23)->addMinutes(59),
                        "actualWorkDate" => $workdateOffsetStart === 0 ? $fromDate->addDays(4)->setTime($currentWorkDate->hour, $currentWorkDate->minute, $currentWorkDate->second) : "",
                        "hours" => 0,
                        "description" => "",
                    ),
                    "day6" => array(
                        "start" =>  $fromDate->addDays(5),
                        "end" => $fromDate->addDays(5)->addHours(23)->addMinutes(59),
                        "actualWorkDate" => $workdateOffsetStart === 0 ? $fromDate->addDays(5)->setTime($currentWorkDate->hour, $currentWorkDate->minute, $currentWorkDate->second) : "",
                        "hours" => 0,
                        "description" => "",
                    ),
                    "day7" => array(
                        "start" =>  $fromDate->addDays(6),
                        "end" => $fromDate->addDays(6)->addHours(23)->addMinutes(59),
                        "actualWorkDate" => $workdateOffsetStart === 0 ? $fromDate->addDays(6)->setTime($currentWorkDate->hour, $currentWorkDate->minute, $currentWorkDate->second) : "",
                        "hours" => 0,
                        "description" => "",
                    ),
                    "rowSum" => 0,
                );
            }

            // Check if timesheet entry falls within the day range of the weekly grouped timesheets that we are trying
            // to pull up.
            //
            // Why would that be different you might ask?
            //
            // If a user adds time entries and then changes timezones (even just 1 hour) the values in the db
            // will be different since it is based on start of the day 00:00:00 in the current users timezone and then
            // stored as UTC timezone shoifted value in the db.
            // If the value is not exact but falls within the time period we're adding a new row
            for ($i = 1; $i < 8; $i++) {

                $start = $timesheetGroups[$groupKey]["day" . $i]["start"];
                $end = $timesheetGroups[$groupKey]["day" . $i]["end"];

                if ($currentWorkDate->gte($start) && $currentWorkDate->lt($end)) {
                    $timesheetGroups[$groupKey]["day" . $i]['hours'] += $timesheet['hours'];
                    $timesheetGroups[$groupKey]["day" . $i]['actualWorkDate'] = $currentWorkDate;
                    $timesheetGroups[$groupKey]["day" . $i]['description'] = $timesheet['description'];

                    // No need to check further, we found what we came for
                    break;
                }
            }

            /*for ($i = 1; $i < 8; $i++) {
                if ($timesheetGroups[$groupKey]["day" . $i]['actualWorkDate'] == $currentWorkDate) {
                    $timesheetGroups[$groupKey]["day" . $i]['hours'] += $timesheet['hours'];
                    $timesheetGroups[$groupKey]["day" . $i]['description'] = $timesheet['description'];
                    // No need to check further, we found what we came for
                    break;
                }
            }*/




            // Add to rowsum
            $timesheetGroups[$groupKey]["rowSum"] += $timesheet['hours'];
        }


        return $timesheetGroups;
    }

    /**
     * @param array $invEmpl
     * @param array $invComp
     * @param array $paid
     *
     * @return bool
     */
    public function updateInvoices(array $invEmpl, array $invComp = [], array $paid = []): bool
    {
        return $this->timesheetsRepo->updateInvoices($invEmpl, $invComp, $paid);
    }

    /**
     * @return array|string[]
     */
    public function getBookedHourTypes(): array
    {
        return $this->timesheetsRepo->kind;
    }
}
