<?php

namespace Leantime\Domain\Timesheets\Services {

    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;

    /**
     *
     */

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

        /*
        * isClocked - Checks to see whether a user is clocked in
        */
        /**
         * @param $sessionId
         * @return array|false
         */
        /**
         * @param $sessionId
         * @return array|false
         */
        public function isClocked($sessionId)
        {

            return $this->timesheetsRepo->isClocked($sessionId);
        }

        /**
         * @param $ticketId
         * @return mixed
         */
        /**
         * @param $ticketId
         * @return mixed
         */
        public function punchIn($ticketId)
        {

            return $this->timesheetsRepo->punchIn($ticketId);
        }

        /**
         * @param $ticketId
         * @return false|float|int
         */
        /**
         * @param $ticketId
         * @return false|float|integer
         */
        public function punchOut($ticketId)
        {
            return $this->timesheetsRepo->punchOut($ticketId);
        }

        /**
         * @param $ticketId
         * @param $params
         * @return string[]|true
         */
        /**
         * @param $ticketId
         * @param $params
         * @return string[]|true
         */
        public function logTime($ticketId, $params)
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
                $values['date'] = $this->language->getISODateString($params['date']);
            }

            if (isset($_POST['hours']) && $_POST['hours'] != '') {
                $values['hours'] = $params['hours'];
            }

            if (isset($_POST['description']) && $_POST['description'] != '') {
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
         * @param $ticketId
         * @return array
         */
        /**
         * @param $ticketId
         * @return array
         */
        public function getLoggedHoursForTicketByDate($ticketId)
        {

            return $this->timesheetsRepo->getLoggedHoursForTicket($ticketId);
        }

        /**
         * @param $ticketId
         * @return int|mixed
         */
        /**
         * @param $ticketId
         * @return integer|mixed
         */
        public function getSumLoggedHoursForTicket($ticketId)
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
         * @return int|mixed
         */
        /**
         * @param $ticket
         * @return integer|mixed
         */
        public function getRemainingHours($ticket)
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
         * @param $ticketId
         * @param $userId
         * @return int|mixed
         */
        /**
         * @param $ticketId
         * @param $userId
         * @return integer|mixed
         */
        public function getUsersTicketHours($ticketId, $userId)
        {
            return  $this->timesheetsRepo->getUsersTicketHours($ticketId, $userId);
        }

        /**
         * @return array|string[]
         */
        /**
         * @return array|string[]
         */
        public function getLoggableHourTypes()
        {
            return $this->timesheetsRepo->kind;
        }

        /**
         * @param $projectId
         * @param $kind
         * @param $dateFrom
         * @param $dateTo
         * @param $userId
         * @param $invEmpl
         * @param $invComp
         * @param $ticketFilter
         * @param $paid
         * @param $clientId
         * @return void
         */
        /**
         * @param $projectId
         * @param $kind
         * @param $dateFrom
         * @param $dateTo
         * @param $userId
         * @param $invEmpl
         * @param $invComp
         * @param $ticketFilter
         * @param $paid
         * @param $clientId
         * @return void
         */
        public function getAll($projectId = -1, $kind = 'all', $dateFrom = '0000-01-01 00:00:00', $dateTo = '9999-12-24 00:00:00', $userId = 'all', $invEmpl = '1', $invComp = '1', $ticketFilter = '-1', $paid = '1', $clientId = '-1')
        {
            return $this->timesheetsRepo->getAll($projectId, $kind, $dateFrom, $dateTo, $userId, $invEmpl, $invComp, $ticketFilter, $paid, $clientId);
        }

        /**
         * @param $values
         * @return null
         */
        /**
         * @param $values
         * @return null
         */
        public function export($values)
        {
            return $this->timesheetsRepo->export($values);
        }

        /**
         * @param $invEmpl
         * @param $invComp
         * @param $paid
         * @return null
         */
        /**
         * @param $invEmpl
         * @param $invComp
         * @param $paid
         * @return null
         */
        public function updateInvoices($invEmpl, $invComp = '', $paid = '')
        {
            return $this->timesheetsRepo->updateInvoices($invEmpl, $invComp, $paid);
        }

        /**
         * @return array|string[]
         */
        /**
         * @return array|string[]
         */
        public function getBookedHourTypes()
        {
            return $this->timesheetsRepo->kind;
        }
    }
}
