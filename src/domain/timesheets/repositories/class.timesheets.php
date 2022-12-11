<?php

namespace leantime\domain\repositories {

    use DateTime;
    use leantime\core;
    use leantime\core\repository;
    use pdo;

    class timesheets extends repository
    {

        /**
         * @access public
         * @var    object
         */
        private $db='';

        /**
         * @access public
         * @var    array
         */
        public $kind = array(
            'GENERAL_BILLABLE' => 'label.general_billable',
            'GENERAL_NOT_BILLABLE' => 'label.general_not_billable',
            'PROJECTMANAGEMENT' => 'label.projectmanagement',
            'DEVELOPMENT' => 'label.development',
            'BUGFIXING_NOT_BILLABLE' => 'label.bugfixing_not_billable',
            'TESTING' => 'label.testing',
        );

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();

        }

        /**
         * getAll - get all timesheet entries
         *
         * @access public
         */
        public function getAll($projectId=-1, $kind='all', $dateFrom='0000-01-01 00:00:00', $dateTo='9999-12-24 00:00:00', $userId = 'all', $invEmpl = '1', $invComp = '1', $ticketFilter = '-1', $paid = '1')
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
                        zp_tickets.planHours
                    FROM
                        zp_timesheets
                    LEFT JOIN zp_user ON zp_timesheets.userId = zp_user.id
                    LEFT JOIN zp_tickets ON zp_timesheets.ticketId = zp_tickets.id
                    LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
                    WHERE
                        ((TO_DAYS(zp_timesheets.workDate) >= TO_DAYS(:dateFrom)) AND (TO_DAYS(zp_timesheets.workDate) <= (TO_DAYS(:dateTo))))";

            if ($projectId > 0) {
                $query.= " AND (zp_tickets.projectId = :projectId)";
            }

            if ($ticketFilter > 0) {
                $query.= " AND (zp_tickets.id = :ticketFilter)";
            }

            if ($kind != 'all') {
                $query.= " AND (zp_timesheets.kind = :kind)";
            }

            if ($userId != 'all') {
                $query.= " AND (zp_timesheets.userId = :userId)";
            }

            if($invComp == '1') {

                $query.= " AND (zp_timesheets.invoicedComp = 1)";

            }

            if($invEmpl == '1') {

                $query.= " AND (zp_timesheets.invoicedEmpl = 1)";

            }

            if($paid == '1') {

                $query.= " AND (zp_timesheets.paid = 1)";

            }

            $query.= " GROUP BY
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

            if ($projectId > 0) {
                $call->bindValue(':projectId', $projectId);
            }

            if ($ticketFilter > 0) {
                $call->bindValue(':ticketFilter', $ticketFilter);
            }

            if ($kind != 'all') {
                $call->bindValue(':kind', $kind);
            }

            if ($userId != 'all') {
                $call->bindValue(':userId', $userId);
            }

            return $call->fetchAll();
        }

        public function export($values)
        {

            /*zp_timesheets.id,
                zp_timesheets.userId,
                zp_timesheets.ticketId,
                zp_timesheets.workDate,
                zp_timesheets.hours,
                zp_timesheets.description,
                zp_timesheets.kind,
                zp_projects.name,
                zp_projects.id AS projectId,
                zp_timesheets.invoicedEmpl,
                zp_timesheets.invoicedComp,
                zp_timesheets.invoicedEmplDate,
                zp_timesheets.invoicedCompDate,
                zp_user.firstname,
                zp_user.lastname,
                zp_tickets.id as ticketId,
                zp_tickets.headline,
                zp_tickets.planHours*/

            //  $this->getAll($projectFilter, $kind, $dateFrom, $dateTo, $userId, $invEmplCheck, $invCompCheck)
            $values = $this->getAll($values['project'], $values['kind'], $values['dateFrom'], $values['dateTo'], $values['userId'], $values['invEmplCheck'], $values['invCompCheck']);

            $filename = "export_".date('m-d_h:m');
            $hash = md5(time().$_SESSION['userdata']['id']);
            $path = $_SERVER['DOCUMENT_ROOT'].'/userdata/export/';
            $ext = 'xls';
            $file = $path.$hash.'.'.$ext;
            header('Content-type: application/ms-excel');
            header('Content-Disposition: attachment; filename='.$filename);

            $sql = "INSERT INTO zp_file (module, userId, extension, encName, realName, date)
					VALUES (:module,:userId,:extension,:encName,:realName,NOW())";

            $call = $this->dbcall(func_get_args());

            $call->prepare($sql, ['values' => $values]);

            $call->bindValue(':module', 'export');
            $call->bindValue(':userId', $_SESSION['userdata']['id']);
            $call->bindValue(':extension', $ext);
            $call->bindValue(':encName', $hash);
            $call->bindValue(':realName', $filename);

            $call->execute();

            $content = 'ID: \t NAME: \t HEADLINE: \t HOURS: \t DESCRIPTION: \t KIND: \t NAME: \t \n';

            foreach ($values as $value) {
                $content .= $value['id']. '\t' . $value['firstname'].' '.$value['lastname']. '\t' . $value['headline']. '\t' . $value['hours']. '\t'
                    . $value['description']. '\t' . $value['kind']. '\t' . $value['name'] . '\t \n';
            }

            file_put_contents($file, $content);
        }

        public function getUsersHours($id)
        {
            $sql = "SELECT id, hours, description FROM zp_timesheets WHERE userId=:userId ORDER BY id DESC";

            $call = $this->dbcall(func_get_args());

            $call->prepare($sql);
            $call->bindValue(':userId', $id, PDO::PARAM_INT);

            return $call->fetchAll();
        }

        public function getHoursBooked()
        {
            $sql = "SELECT SUM(hours) AS hoursBooked
                    FROM zp_timesheets;";

            $call = $this->dbcall(func_get_args());

            $call->prepare($sql);

            $values = $call->fetchAll();

            if (isset($values['hoursBooked']) === true) {
                return $values['hoursBooked'];
            } else {
                return 0;
            }

            return $values;
        }

        public function getWeeklyTimesheets($projectId=-1, $dateStart='0000-01-01 00:00:00', $userId=0)
        {

            $query = "SELECT
			zp_timesheets.id,
			zp_timesheets.userId,
			zp_timesheets.ticketId,
			DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') as workDate,
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
			zp_tickets.headline,
			zp_tickets.planHours,
			zp_projects.name,
			zp_projects.id AS projectId,
			GROUP_CONCAT(DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') SEPARATOR ',') as workDates
			, ROUND(sum(case when DAYOFWEEK(zp_timesheets.workDate) = 2 then zp_timesheets.hours else 0 end),2) as hoursMonday
			, ROUND(sum(case when DAYOFWEEK(zp_timesheets.workDate) = 3 then zp_timesheets.hours else 0 end),2) as hoursTuesday
			, ROUND(sum(case when DAYOFWEEK(zp_timesheets.workDate) = 4 then zp_timesheets.hours else 0 end),2) as hoursWednesday
			, ROUND(sum(case when DAYOFWEEK(zp_timesheets.workDate) = 5 then zp_timesheets.hours else 0 end),2) as hoursThursday
			, ROUND(sum(case when DAYOFWEEK(zp_timesheets.workDate) = 6 then zp_timesheets.hours else 0 end),2) as hoursFriday
			, ROUND(sum(case when DAYOFWEEK(zp_timesheets.workDate) = 7 then zp_timesheets.hours else 0 end),2) as hoursSaturday
			, ROUND(sum(case when DAYOFWEEK(zp_timesheets.workDate) = 1 then zp_timesheets.hours else 0 end),2) as hoursSunday
		FROM
			zp_timesheets
		LEFT JOIN zp_tickets ON zp_tickets.id = zp_timesheets.ticketId
		LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
		WHERE
			((TO_DAYS(zp_timesheets.workDate) >= TO_DAYS(:dateStart1)) AND (TO_DAYS(zp_timesheets.workDate) < (TO_DAYS(:dateStart2) + 7)))
			AND (zp_timesheets.userId = :userId)
		";

            if ($projectId > 0) {
                $query.=" AND zp_tickets.projectId = :projectId";
            }

            $query.="GROUP BY ticketId, kind";

            $call = $this->dbcall(func_get_args());

            $call->prepare($query);

            $call->bindValue(':dateStart1', $dateStart);
            $call->bindValue(':dateStart2', $dateStart);
            $call->bindValue(':userId', $userId, PDO::PARAM_INT);

            if ($projectId > 0) {
                $call->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            return $call->fetchAll();
        }

        /**
         * getUsersTicketHours - get the total hours
         *
         * @access public
         */
        public function getUsersTicketHours($ticketId, $userId)
        {

            $sql = "SELECT SUM(hours) AS sumHours FROM `zp_timesheets` WHERE zp_timesheets.ticketId =:ticketId AND zp_timesheets.userId=:userId GROUP BY DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')";

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
         * addTime - add user specific time entry
         *
         * @access public
         */
        public function addTime($values)
        {

            $query = "INSERT INTO zp_timesheets
			  (userId, ticketId, workDate, hours, kind, description, invoicedEmpl, invoicedComp, invoicedEmplDate, invoicedCompDate, rate, paid, paidDate)
			VALUES
                (:userId,
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
                :paidDate)
			 ON DUPLICATE KEY UPDATE hours = hours + :hours";

            $query = self::dispatch_filter('sql', $query);

            $call = $this->dbcall(func_get_args());

            $call->prepare($query);

            $call->bindValue(':userId', $values['userId']);
            $call->bindValue(':ticket', $values['ticket']);
            $call->bindValue(':date', $values['date']);
            $call->bindValue(':hours', $values['hours']);
            $call->bindValue(':kind', $values['kind']);
            $call->bindValue(':description', $values['description']);
            $call->bindValue(':invoicedEmpl', $values['invoicedEmpl']);
            $call->bindValue(':invoicedComp', $values['invoicedComp']);
            $call->bindValue(':invoicedEmplDate', $values['invoicedEmplDate']);
            $call->bindValue(':invoicedCompDate', $values['invoicedCompDate']);
            $call->bindValue(':rate', $values['rate']);
            $call->bindValue(':hours', $values['hours']);
            $call->bindValue(':paid', $values['paid']);
            $call->bindValue(':paidDate', $values['paidDate']);

            $call->execute();
        }

        public function simpleInsert($values)
        {

            $query = "INSERT INTO zp_timesheets
			(userId,
			ticketId,
			workDate,
			hours,
			kind,
			rate)
			VALUES
			(:userId,
             :ticket,
             :date,
             :hours,
             :kind,
             :rate)
			 ON DUPLICATE KEY UPDATE hours = hours + :hoursB";

            $call = $this->dbcall(func_get_args());

            $call->prepare($query);

            $call->bindValue(':userId', $values['userId']);
            $call->bindValue(':ticket', $values['ticket']);
            $call->bindValue(':date', $values['date']);
            $call->bindValue(':hours', $values['hours']);
            $call->bindValue(':hoursB', $values['hours']);
            $call->bindValue(':kind', $values['kind']);
            $call->bindValue(':rate', $values['rate']);

            $call->execute();
        }


        /**
         * getTime - get a specific time entry
         *
         * @access public
         */
        public function getTimesheet($id)
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
			zp_timesheets.paidDate

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
         * updatTime - update specific time entry
         *
         * @access public
         */
        public function updateTime($values)
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
			paidDate =:paidDate
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

            $call->execute();
        }

        /**
         * updatTime - update specific time entry
         *
         * @access public
         */
        public function UpdateHours($values)
        {

            $query = "UPDATE
                zp_timesheets
            SET
			    hours = :hours
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

            $call->execute();

        }

        /**
         * getProjectHours - get the Project hours for a specific project
         *
         * @access public
         */
        public function getProjectHours($projectId)
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
         * @param $ticketId
         * @return array
         */
        public function getLoggedHoursForTicket($ticketId)
        {

            $query = "SELECT
				YEAR(zp_timesheets.workDate) AS year,
				DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') AS utc,
				DATE_FORMAT(zp_timesheets.workDate, '%M') AS monthName,
				DATE_FORMAT(zp_timesheets.workDate, '%m') AS month,
				SUM(ROUND(zp_timesheets.hours, 2)) AS summe
			FROM
				zp_timesheets
			WHERE
				zp_timesheets.ticketId = :ticketId
			GROUP BY DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')
			ORDER BY utc
			";

            $call = $this->dbcall(func_get_args());

            $call->prepare($query);

            $call->bindValue(':ticketId', $ticketId);

            $values = $call->fetchAll();

            $returnValues = array();

            if (count($values) >0) {

                $startDate = "".$values[0]['year']."-".$values[0]['month']."-01";
                $endDate = "".$values[(count($values)-1)]['utc']."";

                $returnValues = $this->dateRange($startDate, $endDate);

                foreach($values as $row) {

                    $returnValues[$row['utc']]["summe"] = $row['summe'];

                }

            } else {

                $returnValues[date("Y-m-d")]["utc"] = date("Y-m-d");
                $returnValues[date("Y-m-d")]["summe"] = 0;

            }

            return $returnValues;
        }

        /**
         * dateRange - returns every single day between two dates
         *
         * @access private
         * @param $first first date
         * @param $last last date
         * @param string $step default 1 day, can be changed to get every other day, week etc.
         * @param string $format date format
         * @return array
         */
        private function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d' )
        {

            $dates = array();
            $current = strtotime($first);
            $last = strtotime($last);

            while( $current <= $last ) {

                $dates[date($format, $current)]['utc'] = date($format, $current);
                $dates[date($format, $current)]['summe'] = 0;
                $current = strtotime($step, $current);
            }

            return $dates;
        }

        public function deleteTime($id)
        {

            $query = "DELETE FROM zp_timesheets WHERE id = :id LIMIT 1";

            $call = $this->dbcall(func_get_args());

            $call->prepare($query);

            $call->bindValue(':id', $id);

            $call->execute();

        }


        /**
         * updateInvoices
         *
         * @access public
         */
        public function updateInvoices($invEmpl, $invComp = '', $paid ='')
        {

            if ($invEmpl != '' && is_array($invEmpl) === true) {

                foreach ($invEmpl as $row1){

                    $query = "UPDATE zp_timesheets SET invoicedEmpl = 1, invoicedEmplDate = DATE(NOW())
					WHERE id = :id ";

                    $invEmplCall = $this->dbcall(func_get_args(), ['dbcall_key' => 'inv_empl']);

                    $invEmplCall->prepare($query);

                    $invEmplCall->bindValue(':id',  $row1);

                    $invEmplCall->execute();

                    unset($invEmplCall);

                }
            }

            if ($invComp != '' && is_array($invComp) === true) {

                foreach ($invComp as $row2){

                    $query2 = "UPDATE zp_timesheets SET invoicedComp = 1, invoicedCompDate = DATE(NOW())
				    WHERE id = :id ";

                    $invCompCall = $this->dbcall(func_get_args(), ['dbcall_key' => 'inv_comp']);

                    $invCompCall->prepare($query2);

                    $invCompCall->bindValue(':id',  $row2);

                    $invCompCall->execute();

                    unset($invCompCall);

                }

            }

            if ($paid != '' && is_array($paid) === true) {

                foreach($paid as $row3){

                    $query3 = "UPDATE zp_timesheets SET paid = 1, paidDate = DATE(NOW())
				    WHERE id = :id ";

                    $paidCol = $this->dbcall(func_get_args(), ['dbcall_key' => 'paid']);

                    $paidCol->prepare($query3);

                    $paidCol->bindValue(':id', $row3);

                    $paidCol->execute();

                    unset($paidCol);

                }

            }

        }


        /**
         * punchIn - clock in on a specified ticket
         *
         * @access public
         * @param  $ticketId
         */
        public function punchIn($ticketId)
        {

            $query = "INSERT INTO `zp_punch_clock` (id,userId,punchIn) VALUES (:ticketId,:sessionId,:time)";

            $call = $this->dbcall(func_get_args());

            $call->prepare($query);

            $call->bindValue(':ticketId', $ticketId);
            $call->bindValue(':sessionId', $_SESSION['userdata']['id']);
            $call->bindValue(':time', time());

            $value = $call->execute();

            return $value;

        }

        /**
         * punchOut - clock out on whatever ticket is open for the user
         *
         * @access public
         */
        public function punchOut($ticketId)
        {

            $query = "SELECT * FROM `zp_punch_clock` WHERE userId=:sessionId AND id = :ticketId LIMIT 1";

            $call = $this->dbcall(func_get_args(), ['dbcall_key' => 'select']);

            $call->prepare($query);

            $call->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
            $call->bindValue(':sessionId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            $result = $call->fetch();

            unset($call);

            if (!$result) {
                return false;
            }

            $inTimestamp = $result['punchIn'];
            $outTimestamp = time();

            $seconds =  ( $outTimestamp - $inTimestamp );

            $totalMinutesWorked = $seconds / 60;

            $hoursWorked = round(($totalMinutesWorked / 60), 2);

            $query = "DELETE FROM `zp_punch_clock` WHERE userId=:sessionId AND id = :ticketId LIMIT 1 ";

            $call = $this->dbcall(func_get_args(), ['dbcall_key' => 'delete']);

            $call->prepare($query);

            $call->bindValue(':ticketId', $ticketId);
            $call->bindValue(':sessionId', $_SESSION['userdata']['id']);

            $call->execute();

            unset($call);

            //At least 1 minutes
            if ($hoursWorked < 0.016) {
                return 0;
            }

            $date = date("Y-m-d", $inTimestamp)." 00:00:00";

            $query = "INSERT INTO `zp_timesheets` (userId,ticketId,workDate,hours,kind)
            VALUES
            (:sessionId,:ticketId,:workDate,:hoursWorked,'GENERAL_BILLABLE')

                ON DUPLICATE KEY UPDATE hours = hours + :hoursWorked";

            $call = $this->dbcall(func_get_args(), ['dbcall_key' => 'insert']);

            $call->prepare($query);

            $call->bindValue(':ticketId', $ticketId);
            $call->bindValue(':sessionId', $_SESSION['userdata']['id']);
            $call->bindValue(':hoursWorked', $hoursWorked);
            $call->bindValue(':workDate', date("Y-m-d", $inTimestamp)." 00:00:00");

            $call->execute();

            return $hoursWorked;

        }

        /**
         * isClocked - Checks to see whether a user is clocked in
         *
         * @access public
         * @param  id
         */
        public function isClocked($id)
        {

            $query = "SELECT
                     zp_punch_clock.id,
                     zp_punch_clock.userId,
                     zp_punch_clock.minutes,
                     zp_punch_clock.hours,
                     zp_punch_clock.punchIn,
                     zp_tickets.headline
                  FROM `zp_punch_clock`
                  LEFT JOIN zp_tickets ON zp_punch_clock.id = zp_tickets.id WHERE zp_punch_clock.userId=:sessionId LIMIT 1";

            $onTheClock = false;

            $call = $this->dbcall(func_get_args());

            $call->prepare($query);

            $call->bindValue(':sessionId', $_SESSION['userdata']['id']);

            $results = $call->fetchAll();

            if (count($results) > 0) {
                $onTheClock = array();
                $onTheClock["id"] = $results[0]["id"];
                $onTheClock["since"] = $results[0]["punchIn"];
                $onTheClock["headline"] = $results[0]["headline"];
                $start_date = new DateTime();
                $start_date->setTimestamp($results[0]["punchIn"]);
                $since_start = $start_date->diff(new DateTime('NOW'));

                $r = $since_start->format('%H:%I');

                $onTheClock["totalTime"] = $r;
            }

            return $onTheClock;
        }

        /**
         * getTicketHours - get the Ticket hours for a specific ticket
         *
         * @access public
         */
        public function getTicketHours($ticketId)
        {

            $query = "SELECT
				YEAR(zp_timesheets.workDate) AS year,
				DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d') AS utc,
				DATE_FORMAT(zp_timesheets.workDate, '%M') AS monthName,
				DATE_FORMAT(zp_timesheets.workDate, '%m') AS month,
				(zp_timesheets.hours) AS summe
			FROM
				zp_timesheets
			WHERE
				zp_timesheets.ticketId = :ticketId
			ORDER BY utc
			";

            $call = $this->dbcall(func_get_args());

            $call->prepare($query);

            $call->bindValue(':ticketId', $ticketId);

            $values = $call->fetchAll();

            $returnValues = array();

            if (count($values) > 0) {
                $startDate = "".$values[0]['year']."-".$values[0]['month']."-01";
                $endDate = "".$values[(count($values)-1)]['utc']."";


                $returnValues = $this->dateRange($startDate, $endDate);

                foreach($values as $row) {

                    $returnValues[$row['utc']]["summe"] = $row['summe'];

                }
            } else {
                $returnValues[date("%Y-%m-%d")]["utc"] = date("%Y-%m-%d");
                $returnValues[date("%Y-%m-%d")]["summe"] = 0;
            }

            return $returnValues;
        }

    }

}
