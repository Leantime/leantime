<?php

/**
 * Timesheet class - All data access for timesheets
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class timesheets {

	/**
	 * @access public
	 * @var object
	 */
	private $db='';

	/**
	 * @access public
	 * @var array
	 */

	public $kind = array('GENERAL_BILLABLE', 'GENERAL_NOT_BILLABLE', 'PROJECTMANAGEMENT', 'DEVELOPMENT', 'BUGFIXING_NOT_BILLABLE', 'TESTING');

	/**
	 * __construct - get database connection
	 *
	 * @access public
	 */
	public function __construct() {

		$this->db = new db();

	}

	/**
	 * getAll - get all timesheet entries
	 *
	 * @access public
	 */
	public function getAll($projectId=-1, $kind='all', $dateFrom='0000-01-01 00:00:00', $dateTo='9999-12-24 00:00:00', $userId = 'all', $invEmpl = '1', $invComp = '1', $ticketFilter = '-1') {

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
			((TO_DAYS(zp_timesheets.workDate) >= TO_DAYS('".$dateFrom."')) AND (TO_DAYS(zp_timesheets.workDate) <= (TO_DAYS('".$dateTo."'))))";
		if($projectId > 0){
			$query.=" AND (zp_tickets.projectId = '".$projectId."')";
		}
		
		if($ticketFilter > 0){
			$query.=" AND (zp_tickets.id = '".$ticketFilter."')";
		}

		if($kind != 'all'){
			$query.= " AND (zp_timesheets.kind = '".$kind."')";
		}

		if($userId != 'all'){
			$query.= " AND (zp_timesheets.userId= '".$userId."')";
		}
		
		if($invComp == '1' && $invEmpl == '1'){
			
			
			
		}elseif($invComp == '1' && $invEmpl != '1'){
			
			$query.= " AND (zp_timesheets.invoicedComp = '1')";
			
		
		}elseif($invComp != '1' && $invEmpl == '1'){
			
			$query.= " AND (zp_timesheets.invoicedComp <> '1')";
			
		}else{
			
			$query.= " AND (zp_timesheets.invoicedComp = '0' AND zp_timesheets.invoicedEmpl = '0')";
			
		
		}

		$query.= "GROUP BY
		zp_timesheets.id, 
			zp_timesheets.userId, 
			zp_timesheets.ticketId,
			zp_timesheets.workDate,
			zp_timesheets.hours,
			zp_timesheets.description,
			zp_timesheets.kind";
			
		return $this->db->dbQuery($query)->dbFetchResults();


	}
	
	public function export($values) {
				
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
		$values = $this->getAll($values['project'],$values['kind'],$values['dateFrom'],$values['dateTo'],$values['userId'],$values['invEmplCheck'],$values['invCompCheck']);	

		$filename = "export_".date('m-d_h:m');
		$hash = md5(time().$_SESSION['userdata']['id']);
		$path = $_SERVER['DOCUMENT_ROOT'].'/userdata/export/';
		$ext = 'xls';
		$file = $path.$hash.'.'.$ext;
		header('Content-type: application/ms-excel');
		header('Content-Disposition: attachment; filename='.$filename);
				
		$sql = "INSERT INTO zp_file (module, userId, extension, encName, realName, date) 
					VALUES (:module,:userId,:extension,:encName,:realName,NOW())";

		$stmn = $this->db->{'database'}->prepare($sql);

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
	
	public function getUsersHours($id) {
		$sql = "SELECT id, hours, description FROM zp_timesheets WHERE userId=:userId ORDER BY id DESC";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':userId', $id, PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;
	}
	
	public function getAllByProject($kind='all', $dateFrom='0000-01-01 00:00:00', $dateTo='9999-12-24 00:00:00', $userId = 'all', $invEmpl = '1', $invComp = '1') {
		
		
		
		$query = "
		SELECT 
			zp_projects.id AS id,
			zp_projects.name AS project, 
			SUM( zp_timesheets.hours ) AS hours, 
			IF(zp_timesheets.invoicedEmpl = '0', SUM( zp_timesheets.hours ) , 0 ) AS hoursInvEmpl, 
			IF( zp_timesheets.invoicedComp = '0', SUM( zp_timesheets.hours ) , 0 ) AS hoursInvComp
		FROM zp_tickets, 
			zp_timesheets, 
			zp_projects
		WHERE 
		((TO_DAYS(zp_timesheets.workDate) >= TO_DAYS('".$dateFrom."')) AND (TO_DAYS(zp_timesheets.workDate) <= (TO_DAYS('".$dateTo."')))) AND
		zp_timesheets.ticketId = zp_tickets.id
		AND zp_tickets.projectId = zp_projects.id";
		
		if($kind != 'all'){
			$query.= " AND (zp_timesheets.kind = '".$kind."')";
		}

		if($userId != 'all'){
			$query.= " AND (zp_timesheets.userId= '".$userId."')";
		}	
		
		$query .= " GROUP BY zp_projects.name
		ORDER BY zp_projects.name			
		";

		return $this->db->dbQuery($query)->dbFetchResults();
	}
	
	function getTicketSummaryForProject($id, $kind='all', $dateFrom='0000-01-01 00:00:00', $dateTo='9999-12-24 00:00:00', $userId = 'all', $invEmpl = '1', $invComp = '1') {
		
		$query = "SELECT 
				zp_tickets.headline, 
				zp_tickets.id, 
				SUM( zp_timesheets.hours ) AS hours, 
				IF( zp_timesheets.invoicedEmpl = '0', SUM( zp_timesheets.hours ) , 0 ) AS hoursInvEmpl, 
				IF( zp_timesheets.invoicedComp = '0', SUM( zp_timesheets.hours ) , 0 ) AS hoursInvComp
			FROM 
				zp_tickets, 
				zp_timesheets, 
				zp_projects
			WHERE 
			((TO_DAYS(zp_timesheets.workDate) >= TO_DAYS('".$dateFrom."')) AND (TO_DAYS(zp_timesheets.workDate) <= (TO_DAYS('".$dateTo."')))) AND
				zp_timesheets.ticketId = zp_tickets.id
				AND zp_tickets.projectId = zp_projects.id
				AND zp_projects.id = '".$id."'
			GROUP BY zp_tickets.headline, zp_tickets.id
			ORDER BY zp_projects.name";
		
		return $this->db->dbQuery($query)->dbFetchResults();
		
	}

	/**
	 * getMy - get user specific timesheet entries
	 *
	 * @access public
	 */
	public function getMy($projectId=-1, $kind='all', $dateFrom='0000-01-01 00:00:00', $dateTo='9999-12-24 00:00:00', $invEmpl = '1', $invComp = '1') {

		$query = "SELECT
			zp_timesheets.id, 
			zp_timesheets.userId, 
			zp_timesheets.ticketId,
			zp_timesheets.workDate,
			zp_timesheets.hours,
			zp_timesheets.description,
			zp_timesheets.kind,
			zp_timesheets.invoicedEmpl,
			zp_timesheets.invoicedComp,
			zp_timesheets.invoicedEmplDate,
			zp_timesheets.invoicedCompDate,
			zp_timesheets.kind,
			zp_timesheets.kind,
			zp_tickets.headline,
			zp_tickets.planHours,
			zp_projects.name,
			zp_projects.id AS projectId
		FROM
			zp_timesheets
		LEFT JOIN zp_tickets ON zp_tickets.id = zp_timesheets.ticketId
		LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
		WHERE 
			((TO_DAYS(zp_timesheets.workDate) >= TO_DAYS('".$dateFrom."')) AND (TO_DAYS(zp_timesheets.workDate) <= (TO_DAYS('".$dateTo."'))))
			AND (zp_timesheets.userId = '".$_SESSION['userdata']['id']."')
		";

		if($projectId > 0){
			$query.=" AND zp_tickets.projectId = '".$projectId."'";
		}

		if($kind != 'all'){
			$query.= " AND zp_timesheets.kind = '".$kind."'";
		}
		
		
		
		if($invComp == '1' && $invEmpl == '1'){
			
		}elseif($invComp == '1' && $invEmpl != '1'){
			
			$query.= " AND (zp_timesheets.invoicedComp = '1' OR (zp_timesheets.invoicedComp = '0' AND zp_timesheets.invoicedEmpl = '0'))";
		
		}elseif($invEmpl == '1' && $invComp != '1'){
			$query.= " AND (zp_timesheets.invoicedEmpl = '1' OR (zp_timesheets.invoicedComp = '0' AND zp_timesheets.invoicedEmpl = '0'))";
			
		}else{
			
			$query.= " AND (zp_timesheets.invoicedComp = '0' AND zp_timesheets.invoicedEmpl = '0')";
			
		
		}
		
		$query.="";

		return $this->db->dbQuery($query)->dbFetchResults();


	}

	/**
	 * getUsersTicketHours - get the total hours 
	 * 
	 * @access public
	 * 
	 */
	public function getUsersTicketHours($ticketId, $userId) {
			$totalHours = 0;
			$sql = "SELECT * FROM `zp_timesheets` WHERE zp_timesheets.ticketId ='$ticketId' AND zp_timesheets.userId='$userId'";
			
			$query = $this->db->dbQuery($sql)->dbFetchResults();
			
			if(is_resource($query)) {
				while($row = mysql_fetch_assoc($query)) {
					$totalHours += $row['hours'];
				}
			}
			
			return $totalHours;
	}
	
	/**
	 * addTime - add user specific time entry
	 *
	 * @access public
	 */
	public function addTime($values){

		$query = "INSERT INTO zp_timesheets
			(userId, ticketId, workDate, hours, kind, description, invoicedEmpl, invoicedComp, invoicedEmplDate, invoicedCompDate, rate) 
			VALUES
			('".$values['userId']."', '".$values['ticket']."', '".$values['date']."', '".$values['hours']."', '".$values['kind']."', '".$values['description']."', '".$values['invoicedEmpl']."', '".$values['invoicedComp']."',  '".$values['invoicedEmplDate']."', '".$values['invoicedCompDate']."', '".$values['rate']."')";

		$this->db->dbQuery($query);
	}


	/**
	 * getTime - get a specific time entry
	 *
	 * @access public
	 */
	public function getTimesheet($id) {

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
		WHERE zp_timesheets.id = '".$id."'";

		return $this->db->dbQuery($query)->dbFetchRow();

	}

	/**
	 * updatTime - update specific time entry
	 *
	 * @access public
	 */
	public function updateTime($values){

		$query = "UPDATE
					zp_timesheets 
				SET
			ticketId = '".$values['ticket']."',
			workDate = '".$values['date']."',
			hours = '".$values['hours']."', 
			kind = '".$values['kind']."',
			description ='".$values['description']."',
			invoicedEmpl ='".$values['invoicedEmpl']."',
			invoicedComp ='".$values['invoicedComp']."',
			invoicedEmplDate ='".$values['invoicedEmplDate']."',
			invoicedCompDate ='".$values['invoicedCompDate']."'
			WHERE 
				id = '".$values['id']."' ";

		$this->db->dbQuery($query);
	}

	/**
	 * getProjectHours - get the Project hours for a specific project
	 *
	 * @access public
	 */
	public function getProjectHours($projectId){

		$query = "SELECT
				MONTH(zp_timesheets.workDate) AS month,
				SUM(zp_timesheets.hours) AS summe
			FROM 
				zp_timesheets LEFT JOIN zp_tickets ON zp_timesheets.ticketId = zp_tickets.id
			WHERE 
				zp_tickets.projectId = '".$projectId."'
			GROUP BY
				MONTH(zp_timesheets.workDate)
				WITH ROLLUP
			LIMIT 12";

		return $this->db->dbQuery($query)->dbFetchResults();
	}

	/**
	 * getTicketHours - get the Ticket hours for a specific ticket
	 *
	 * @access public
	 */
	public function getTicketHours($ticketId){
		/*
		
		$sql = "SELECT * FROM `zp_timesheets` 
					WHERE ticketId = :ticketId ORDER BY workDate asc";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':ticketId',$ticketId,PDO::PARAM_STR);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		
		$hours = 0;
		
		$results = $this->db->dbQuery($sql)->dbFetchResults();
		
		
		foreach($results as $timesheet) {
			$hours += $timesheet['hours'];
		}
		*/
		

		
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
		$stmn->bindValue(':ticketId',$ticketId,PDO::PARAM_STR);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		
		$returnValues = array();
		
		if(count($values) >0){
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


	function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d' ) { 

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
	
	public function deleteTime($id){
		
		$query = "DELETE FROM zp_timesheets WHERE id = '".$id."' LIMIT 1";
		
		$this->db->dbQuery($query);
	}


	/**
	 * updateInvoices
	 *
	 * @access public
	 */
	public function updateInvoices($invEmpl, $invComp = ''){

		if($invEmpl != '' && is_array($invEmpl) === true){
				
			foreach($invEmpl as $row1){

				$query = "UPDATE zp_timesheets SET invoicedEmpl = 1, invoicedEmplDate = DATE(NOW())
					WHERE id = '".$row1."' ";

				$this->db->dbQuery($query);

					
			}
		}

		if($invComp != '' && is_array($invComp) === true){

			foreach($invComp as $row2){
					
				$query2 = "UPDATE zp_timesheets SET invoicedComp = 1, invoicedCompDate = DATE(NOW())
				WHERE id = '".$row2."' ";
					
				$this->db->dbQuery($query2);
					

			}
				
		}

	}


}

?>