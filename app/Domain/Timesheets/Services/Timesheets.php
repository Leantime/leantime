<?php

namespace Leantime\Domain\Timesheets\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;

/**
 *
 */
class Timesheets
{
    private TimesheetRepository $timesheetsRepo;
    private LanguageCore $language;

    /**
     * @param TimesheetRepository $timesheetsRepo
     * @param LanguageCore        $language
     */
    public function __construct(TimesheetRepository $timesheetsRepo, LanguageCore $language)
    {
        $this->timesheetsRepo = $timesheetsRepo;
        $this->language = $language;
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
     * @param int   $ticketId
     * @param array $params
     *
     * @return array|bool
     *
     * @throws BindingResolutionException
     */
    public function logTime(int $ticketId, array $params): array|bool
    {
        $values = array(
            'userId' => $_SESSION['userdata']['id'],
            'ticket' => $ticketId,
            'date' => '',
            'kind' => '',
            'hours' => '',
            'rate' => '',
            'description' => '',
            'invoicedEmpl' => '',
            'invoicedComp' => '',
            'invoicedEmplDate' => '',
            'invoicedCompDate' => '',
            'paid' => '',
            'paidDate' => '',
        );

        if (isset($params['kind']) && $params['kind'] != '') {
            $values['kind'] = $params['kind'];
        }

        if (isset($params['date']) && $params['date'] != '') {
            $values['date'] = format($params['date'])->isoDate();
        }

        if (isset($params['hours']) && $params['hours'] != '') {
            $values['hours'] = $params['hours'];
        }

        if (isset($params['description']) && $params['description'] != '') {
            $values['description'] = $params['description'];
        }

        if ($values['kind'] != '') {
            if ($values['date'] != '') {
                if ($values['hours'] != '' && $values['hours'] > 0) {
                    $this->timesheetsRepo->addTime($values);

                    return true;
                } else {
                    return array("msg" => "notifications.time_logged_error_no_hours", "type" => "error");
                }
            } else {
                return array("msg" => "time_logged_error_no_date", "type" => "error");
            }
        } else {
            return array("msg" => "time_logged_error_no_kind", "type" => "error");
        }
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
     * @param $ticket
     *
     * @return int|mixed
     */
    public function getRemainingHours($ticket): mixed
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
     * @param int      $projectId
     * @param string   $kind
     * @param string   $dateFrom
     * @param string   $dateTo
     * @param int|null $userId
     * @param string   $invEmpl
     * @param string   $invComp
     * @param string   $ticketFilter
     * @param string   $paid
     * @param string   $clientId
     *
     * @return array|false
     */
    public function getAll(int $projectId = -1, string $kind = 'all', string $dateFrom = '0000-01-01 00:00:00', string $dateTo = '9999-12-24 00:00:00', ?int $userId = null, string $invEmpl = '1', string $invComp = '1', string $ticketFilter = '-1', string $paid = '1', string $clientId = '-1'): array|false
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
     * @param array $values
     *
     * @return void
     */
    public function export(array $values): void
    {
        $this->timesheetsRepo->export($values);
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
