<?php

/**
 * Calender class - All data access for clients
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class calendar {
	
	/**
	 * @access public
	 * @var object
	 */
	private $db='';

	/**
	 * __construct - get database connection
	 *
	 * @access public
	 */
	public function __construct() {

		$this->db = new db();

	}
	
	public function getAllDates($dateFrom, $dateTo) {

		$query = "SELECT * FROM zp_calendar WHERE 
		userId = '".$_SESSION['userdata']['id']."' 
			
			
			ORDER BY zp_calendar.dateFrom";
		
		return $this->db->dbQuery($query)->dbFetchResults();
		
	}
	
	public function getCalendar($id) {
		
		$adminTickets = "SELECT tickets.dateToFinish, tickets.headline, tickets.id  FROM zp_tickets as tickets WHERE userId LIKE '%".$id."%' OR editorId LIKE '%".$id."%'";
		$userTickets = "SELECT tickets.dateToFinish, tickets.headline, tickets.id 
				FROM zp_relationUserProject as project
				INNER JOIN zp_tickets as tickets ON project.projectId = tickets.projectId
				WHERE tickets.userId = ".$id;
			
		$stmn = $this->db->{'database'}->prepare($userTickets);	
		if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'employee')	
			$stmn = $this->db->{'database'}->prepare($adminTickets);
		
		$stmn->execute();
		$tickets = $stmn->fetchAll();
		$stmn->closeCursor();
				
		$sql = "SELECT * FROM zp_calendar WHERE userId = :userId";

		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':userId',$id,PDO::PARAM_INT);

		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		$newValues = array();
		foreach ($values as $value) {
			$dateFrom 	= strtotime($value['dateFrom']);
			$dateTo 	= strtotime($value['dateTo']);
			
			$newValues[] = array(
				'title'  => $value['description'],
				'allDay' => $value['allDay'],
				'dateFrom' => array(
					'y' => date('Y',$dateFrom), 
					'm' => date('m',$dateFrom),
					'd' => date('d',$dateFrom),
					'h' => date('H',$dateFrom),
					'i' => date('i',$dateFrom)
				),
				'dateTo' => array(
					'y' => date('Y',$dateTo), 
					'm' => date('m',$dateTo),
					'd' => date('d',$dateTo),
					'h' => date('H',$dateTo),
					'i' => date('i',$dateTo)
				),
				'id' => $value['id']			
			);
		}
		
		if (count($tickets)) {
			foreach ($tickets as $ticket) {
				$dateFrom = strtotime($ticket['dateToFinish']);
				$newValues[] = array(
					'title'  => 'Due: ' . $ticket['headline'],
					'dateFrom' => array(
						'y' => date('Y',$dateFrom), 
						'm' => date('m',$dateFrom),
						'd' => date('d',$dateFrom),
						'h' => date('H',$dateFrom),
						'i' => date('i',$dateFrom)
					),
					'id' => $ticket['id']			
				);			
			}
		}
		
		return $newValues;
	}
	
	public function getTicketWishDates() {
		
		$query = "SELECT id, headline, dateToFinish FROM zp_tickets WHERE (userId = '".$_SESSION['userdata']['id']."' OR editorId = '".$_SESSION['userdata']['id']."') AND dateToFinish <> '000-00-00 00:00:00'";
		
		return $this->db->dbQuery($query)->dbFetchResults();
	}
	
	
	
	public function getTicketEditDates() {
		
		$query = "SELECT id, headline, editFrom, editTo FROM zp_tickets WHERE (userId = '".$_SESSION['userdata']['id']."' OR editorId = '".$_SESSION['userdata']['id']."') AND editFrom <> '000-00-00 00:00:00'";
		
		return $this->db->dbQuery($query)->dbFetchResults();
	}
	
	public function addEvent($values){
		
		$query = "INSERT INTO zp_calendar (userId, dateFrom, dateTo, description, allDay) 
		VALUES ('".$_SESSION['userdata']['id']."', '".$values['dateFrom']."', '".$values['dateTo']."', '".$values['description']."', '".$values['allDay']."')";
		
		$this->db->dbQuery($query);
		
	}
	
	public function getEvent($id){
		
		$query = "SELECT * FROM zp_calendar WHERE id = '".$id."'";
		
		return $this->db->dbQuery($query)->dbFetchRow();
		
	}
	
	public function editEvent($values, $id) {
		
		$query = "UPDATE zp_calendar SET 
			dateFrom = '".$values['dateFrom']."',
			dateTo = '".$values['dateTo']."', 
			description = '".$values['description']."',
			allDay = '".$values['allDay']."'
			WHERE id = '".$id."' AND userId ='".$_SESSION['userdata']['id']."' LIMIT 1";
		
		$this->db->dbQuery($query);
	}
	
	public function delEvent($id) {
		
		$query = "DELETE FROM zp_calendar WHERE id = '".$id."' AND userId = '".$_SESSION['userdata']['id']."' LIMIT 1";
		$this->db->dbQuery($query);
		
	}
	
	public function getMyGoogleCalendars() {
		
		$query = "SELECT id, url, name, colorClass FROM zp_gCalLinks WHERE userId = '".$_SESSION['userdata']['id']."'";
		
		return $this->db->dbQuery($query)->dbFetchResults();
		
	}
	
	public function getGCal($id) {
		
		$query = "SELECT id, url, name, colorClass FROM zp_gCalLinks WHERE userId = '".$_SESSION['userdata']['id']."' AND id = '".$id."' LIMIT 1";
		
		return $this->db->dbQuery($query)->dbFetchRow();
		
	}
	
	public function editGUrl($values, $id) {
		
		$query = "UPDATE zp_gCalLinks SET 
			url = '".$values['url']."',
			name = '".$values['name']."',
			colorClass = '".$values['colorClass']."' 
		WHERE userId = ".$_SESSION['userdata']['id']." AND id = '".$id."' LIMIT 1";
		
		$this->db->dbQuery($query);
		
		
		
	}
	
	public function deleteGCal($id) {
		
		$query = "DELETE FROM zp_gCalLinks WHERE userId = '".$_SESSION['userdata']['id']."' AND id = '".$id."' LIMIT 1";
		
		$this->db->dbQuery($query);
		
	}
	
	public function addGUrl($values) {
		
		$query = "INSERT INTO zp_gCalLinks (userId, name, url, colorClass) 
					VALUES 
				('".$_SESSION['userdata']['id']."', '".$values['name']."', '".$values['url']."', '".$values['colorClass']."')";
		
		die($query);
		$this->db->dbQuery($query);
		
	}

	
	
	
}


?>