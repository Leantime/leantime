<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class timesheets
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
        public function getAll($projectId=-1, $kind='all', $dateFrom='0000-01-01 00:00:00', $dateTo='9999-12-24 00:00:00', $userId = 'all', $invEmpl = '1', $invComp = '1', $ticketFilter = '-1')
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

            if($projectId > 0) {
                $query.=" AND (zp_tickets.projectId = :projectId)";
            }

            if($ticketFilter > 0) {
                $query.=" AND (zp_tickets.id = :ticketFilter)";
            }

            if($kind != 'all') {
                $query.= " AND (zp_timesheets.kind = :kind)";
            }

            if($userId != 'all') {
                $query.= " AND (zp_timesheets.userId= :userId)";
            }

            if($invComp == '1' && $invEmpl == '1') {

                $query.= " AND (zp_timesheets.invoicedComp = 1 AND zp_timesheets.invoicedEmpl = 1)";

            }elseif($invComp == '1' && $invEmpl != '1') {

                $query.= " AND (zp_timesheets.invoicedComp = 1 AND (zp_timesheets.invoicedEmpl <> 1 OR zp_timesheets.invoicedEmpl IS NULL) )";

            }elseif($invComp != '1' && $invEmpl == '1') {

                $query.= " AND ((zp_timesheets.invoicedComp <> 1 OR zp_timesheets.invoicedComp IS NULL)  AND zp_timesheets.invoicedEmpl = 1)";

            }

            $query.= " GROUP BY
		                zp_timesheets.id, 
                        zp_timesheets.userId, 
                        zp_timesheets.ticketId,
                        zp_timesheets.workDate,
                        zp_timesheets.hours,
                        zp_timesheets.description,
                        zp_timesheets.kind";



            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':dateFrom', $dateFrom, PDO::PARAM_STR);
            $stmn->bindValue(':dateTo', $dateTo, PDO::PARAM_STR);

            if($projectId > 0) {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);
            }

            if($ticketFilter > 0) {
                $stmn->bindValue(':ticketFilter', $ticketFilter, PDO::PARAM_STR);
            }

            if($kind != 'all') {
                $stmn->bindValue(':kind', $kind, PDO::PARAM_STR);
            }

            if($userId != 'all') {
                $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();


            return $values;


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

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':module', 'export', PDO::PARAM_STR);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_STR);
            $stmn->bindValue(':extension', $ext, PDO::PARAM_STR);
            $stmn->bindValue(':encName', $hash, PDO::PARAM_STR);
            $stmn->bindValue(':realName', $filename, PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();

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

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':userId', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }







        public function getWeeklyTimesheets($projectId=-1, $dateStart='0000-01-01 00:00:00', $userId)
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
			((TO_DAYS(zp_timesheets.workDate) >= TO_DAYS(:dateStart)) AND (TO_DAYS(zp_timesheets.workDate) < (TO_DAYS(:dateStart) + 7)))
			AND (zp_timesheets.userId = :userId)
		";

            if($projectId > 0) {
                $query.=" AND zp_tickets.projectId = :projectId";
            }

            $query.="GROUP BY ticketId, kind";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':dateStart', $dateStart, PDO::PARAM_STR);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);

            if($projectId > 0) {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }


        /**
         * getUsersTicketHours - get the total hours
         *
         * @access public
         */
        public function getUsersTicketHours($ticketId, $userId)
        {

            $sql = "SELECT SUM(hours) AS sumHours FROM `zp_timesheets` WHERE zp_timesheets.ticketId =:ticketId AND zp_timesheets.userId=:userId GROUP BY DATE_FORMAT(zp_timesheets.workDate, '%Y-%m-%d')";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            if(count($values) > 0) {
                return $values[0]['sumHours'];
            }else{
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
			  (userId, ticketId, workDate, hours, kind, description, invoicedEmpl, invoicedComp, invoicedEmplDate, invoicedCompDate, rate) 
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
                :rate)
			 ON DUPLICATE KEY UPDATE hours = hours + :hours";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':userId', $values['userId'], PDO::PARAM_STR);
            $stmn->bindValue(':ticket', $values['ticket'], PDO::PARAM_STR);
            $stmn->bindValue(':date', $values['date'], PDO::PARAM_STR);
            $stmn->bindValue(':hours', $values['hours'], PDO::PARAM_STR);
            $stmn->bindValue(':kind', $values['kind'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
            $stmn->bindValue(':invoicedEmpl', $values['invoicedEmpl'], PDO::PARAM_STR);
            $stmn->bindValue(':invoicedComp', $values['invoicedComp'], PDO::PARAM_STR);
            $stmn->bindValue(':invoicedEmplDate', $values['invoicedEmplDate'], PDO::PARAM_STR);
            $stmn->bindValue(':invoicedCompDate', $values['invoicedCompDate'], PDO::PARAM_STR);
            $stmn->bindValue(':rate', $values['rate'], PDO::PARAM_STR);
            $stmn->bindValue(':hours', $values['hours'], PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();
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
			 ON DUPLICATE KEY UPDATE hours = hours + :hours";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':userId', $values['userId'], PDO::PARAM_STR);
            $stmn->bindValue(':ticket', $values['ticket'], PDO::PARAM_STR);
            $stmn->bindValue(':date', $values['date'], PDO::PARAM_STR);
            $stmn->bindValue(':hours', $values['hours'], PDO::PARAM_STR);
            $stmn->bindValue(':kind', $values['kind'], PDO::PARAM_STR);
            $stmn->bindValue(':rate', $values['rate'], PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();
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
			zp_timesheets.invoicedCompDate
		FROM zp_timesheets 
		LEFT JOIN zp_tickets ON zp_timesheets.ticketId = zp_tickets.id
		LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
		WHERE zp_timesheets.id = :id";


            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $value = $stmn->fetch();
            $stmn->closeCursor();

            return $value;

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
			invoicedCompDate =:invoicedCompDate
			WHERE 
				id = :id";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':ticket', $values['ticket'], PDO::PARAM_STR);
            $stmn->bindValue(':date', $values['date'], PDO::PARAM_STR);
            $stmn->bindValue(':hours', $values['hours'], PDO::PARAM_STR);
            $stmn->bindValue(':kind', $values['kind'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
            $stmn->bindValue(':invoicedEmpl', $values['invoicedEmpl'], PDO::PARAM_STR);
            $stmn->bindValue(':invoicedComp', $values['invoicedComp'], PDO::PARAM_STR);
            $stmn->bindValue(':invoicedEmplDate', $values['invoicedEmplDate'], PDO::PARAM_STR);
            $stmn->bindValue(':invoicedCompDate', $values['invoicedCompDate'], PDO::PARAM_STR);
            $stmn->bindValue(':id', $values['id'], PDO::PARAM_STR);


            $stmn->execute();
            $stmn->closeCursor();
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

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':date', $values['date'], PDO::PARAM_STR);
            $stmn->bindValue(':hours', $values['hours'], PDO::PARAM_STR);
            $stmn->bindValue(':userId', $values['userId'], PDO::PARAM_STR);
            $stmn->bindValue(':ticketId', $values['ticket'], PDO::PARAM_STR);
            $stmn->bindValue(':kind', $values['kind'], PDO::PARAM_STR);


            $stmn->execute();
            $stmn->closeCursor();

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

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
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

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            $returnValues = array();

            if(count($values) >0) {

                $startDate = "".$values[0]['year']."-".$values[0]['month']."-01";
                $endDate = "".$values[(count($values)-1)]['utc']."";

                $returnValues = $this->dateRange($startDate, $endDate);

                foreach($values as $row) {

                    $returnValues[$row['utc']]["summe"] = $row['summe'];

                }

            }else{

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

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

        }


        /**
         * updateInvoices
         *
         * @access public
         */
        public function updateInvoices($invEmpl, $invComp = '')
        {

            if($invEmpl != '' && is_array($invEmpl) === true) {

                foreach($invEmpl as $row1){

                    $query = "UPDATE zp_timesheets SET invoicedEmpl = 1, invoicedEmplDate = DATE(NOW())
					WHERE id = :id ";

                    $stmn = $this->db->database->prepare($query);
                    $stmn->bindValue(':id',  $row1, PDO::PARAM_STR);
                    $stmn->execute();

                }
            }

            if($invComp != '' && is_array($invComp) === true) {

                foreach($invComp as $row2){

                    $query2 = "UPDATE zp_timesheets SET invoicedComp = 1, invoicedCompDate = DATE(NOW())
				    WHERE id = :id ";

                    $stmn = $this->db->database->prepare($query2);
                    $stmn->bindValue(':id',  $row2, PDO::PARAM_STR);
                    $stmn->execute();


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

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_STR);
            $stmn->bindValue(':sessionId', $_SESSION['userdata']['id'], PDO::PARAM_STR);
            $stmn->bindValue(':time', time(), PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();

        }

        /**
         * punchOut - clock out on whatever ticket is open for the user
         *
         * @access public
         */
        public function punchOut($ticketId)
        {

            $query = "SELECT * FROM `zp_punch_clock` WHERE userId=:sessionId AND id = :ticketId LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
            $stmn->bindValue(':sessionId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            $stmn->execute();

            $result = $stmn->fetch();

            $stmn->closeCursor();

            if($result) {

                $inTimestamp = $result['punchIn'];
                $outTimestamp = time();

                $seconds =  ( $outTimestamp - $inTimestamp );

                $totalMinutesWorked = $seconds / 60;

                $hoursWorked = round(($totalMinutesWorked / 60), 2);

                $query = "DELETE FROM `zp_punch_clock` WHERE userId=:sessionId AND id = :ticketId LIMIT 1 ";

                $stmn = $this->db->database->prepare($query);
                $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_STR);
                $stmn->bindValue(':sessionId', $_SESSION['userdata']['id'], PDO::PARAM_STR);

                $stmn->execute();
                $stmn->closeCursor();

                //At least 1 minutes
                if($hoursWorked >= 0.016) {

                    $date = date("Y-m-d", $inTimestamp)." 00:00:00";

                    $query = "INSERT INTO `zp_timesheets` (userId,ticketId,workDate,hours,kind) 
                    VALUES
                    (:sessionId,:ticketId,:workDate,:hoursWorked,'GENERAL_BILLABLE')
                    
                     ON DUPLICATE KEY UPDATE hours = hours + :hoursWorked";

                    $stmn = $this->db->database->prepare($query);
                    $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_STR);
                    $stmn->bindValue(':sessionId', $_SESSION['userdata']['id'], PDO::PARAM_STR);
                    $stmn->bindValue(':hoursWorked', $hoursWorked, PDO::PARAM_STR);
                    $stmn->bindValue(':workDate', date("Y-m-d", $inTimestamp)." 00:00:00", PDO::PARAM_STR);

                    $stmn->execute();
                    $stmn->closeCursor();

                }else{
                    $hoursWorked = 0;
                }

                return $hoursWorked;

            }else{
                return false;
            }




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

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':sessionId', $_SESSION['userdata']['id'], PDO::PARAM_STR);
            $stmn->execute();

            $results = $stmn->fetchAll();

            $stmn->closeCursor();

            if (count($results) > 0) {
                $onTheClock["id"] = $results[0]["id"];
                $onTheClock["since"] = $results[0]["punchIn"];
                $onTheClock["headline"] = $results[0]["headline"];
                $start_date = new \DateTime();
                $start_date->setTimestamp($results[0]["punchIn"]);
                $since_start = $start_date->diff(new \DateTime('NOW'));

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

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();


            $returnValues = array();

            if(count($values) >0) {
                $startDate = "".$values[0]['year']."-".$values[0]['month']."-01";
                $endDate = "".$values[(count($values)-1)]['utc']."";


                $returnValues = $this->dateRange($startDate, $endDate);

                foreach($values as $row) {

                    $returnValues[$row['utc']]["summe"] = $row['summe'];

                }
            }else{
                $returnValues[date("%Y-%m-%d")]["utc"] = date("%Y-%m-%d");
                $returnValues[date("%Y-%m-%d")]["summe"] = 0;
            }

            return $returnValues;
        }

    }

}