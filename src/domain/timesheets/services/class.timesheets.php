<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;
    use \DatePeriod;
    use \DateTime;
    use \DateInterval;

    class timesheets
    {

        private $timesheetsRepo;


        public function __construct()
        {

            $this->timesheetsRepo = new repositories\timesheets();
            $this->language = new core\language();

        }

        /*
        * isClocked - Checks to see whether a user is clocked in
        */
        public function isClocked($sessionId)
        {

            return $this->timesheetsRepo->isClocked($sessionId);
        }

        public function punchIn($ticketId)
        {

            return $this->timesheetsRepo->punchIn($ticketId);
        }

        public function punchOut($ticketId)
        {
            return $this->timesheetsRepo->punchOut($ticketId);
        }

        public function logTime($ticketId, $params){

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
                'invoicedCompDate' => ''
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

        public function getLoggedHoursForTicketByDate($ticketId)
        {

            return $this->timesheetsRepo->getLoggedHoursForTicket($ticketId);

        }

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

        public function getRemainingHours($ticket) {

            $totalHoursLogged = $this->getSumLoggedHoursForTicket($ticket->id);
            $planHours = $ticket->planHours;

            $remaining = $planHours - $totalHoursLogged;

            if($remaining < 0){
                $remaining = 0;
            }

            return $remaining;

        }

        public function getUsersTicketHours($ticketId, $userId)
        {
            return  $this->timesheetsRepo->getUsersTicketHours($ticketId, $userId);
        }

        public function getLoggableHourTypes()
        {
            return $this->timesheetsRepo->kind;
        }

        public function getAll($projectId=-1, $kind='all', $dateFrom='0000-01-01 00:00:00', $dateTo='9999-12-24 00:00:00', $userId = 'all', $invEmpl = '1', $invComp = '1', $ticketFilter = '-1'){
            return $this->timesheetsRepo->getAll($projectId, $kind, $dateFrom, $dateTo, $userId, $invEmpl, $invComp, $ticketFilter);
        }

    }
}