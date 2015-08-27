<?php

/**
 * Ticket class
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.1
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class tickets {

	/**
	 * @access public
	 * @var object
	 */
	public $result = NULL;

	/**
	 * @access public
	 * @var object
	 */
	public $tickets = NULL;

	/**
	 * @access private
	 * @var object
	 */
	private $db='';

	/**
	 * @access public
	 * @var array
	 */
	public $state = array('0' => 'label-success', '1' => 'label-warning', '2' => 'label-info', '3' => 'label-important', '4' => 'label-inverse');

	/**
	 * @access public
	 * @var array
	 */
	public $statePlain = array('0' => 'FINISHED', '1' => 'ERROR', '2' => 'APPROVAL', '3' => 'NEW', '4' => 'SEEN',); // '5' => 'TAKEN', '6' => 'ERROR');

	/**
	 * @access public
	 * @var array
	 */
	public $os = array('NOT_SPECIFIED','WINDOWS', 'MAC', 'LINUX');

	/**
	 * @access public
	 * @var array
	 */
	public $browser = array('NOT_SPECIFIED','IE6', 'IE7', 'IE8', 'FIREFOX2', 'FIREFOX3', 'OPERA10', 'KONQUEROR', 'SAFARI9', 'SAFARI10');

	/**
	 * @access public
	 * @var array
	 */
	public $res = array('NOT_SPECIFIED', '800x600', '1024x768', '1152x864', '1280x1024');

	/**
	 * @access public
	 * @var array
	 */
	public $priority = array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6');
	
	/**
	 * @access public
	 * @var array
	 */
	 public $pushed = array('1' => 'PUSHED_TO_STAGING', '2' => 'PUSHED_TO_PROD');

	/**
	 * @access public
	 * @var array
	 */
	public $type = array('Story', 'Task', 'Change Request', 'Error Report', 'Other');

	/**
	 * @access private
	 * @var integer
	 */
	private $page = 0;

	/**
	 * @access public
	 * @var integer
	 */
	public $rowsPerPage = 10;

	/**
	 * @access private
	 * @var string
	 */
	private $limitSelect = "";

	/**
	 * @access numPages
	 * @var unknown_type
	 */
	public $numPages='';

	/**
	 * @access public
	 * @var string
	 */
	public $sortBy = 'date';

	/**
	 * __construct - get db connection
	 *
	 * @access public
	 * @return unknown_type
	 */
	public function __construct() {

		$this->db = new db();

	}

	public function getUnreadTickets($userId,$limit = 9999) {
		
		$read = new read();
		$unreadTickets = array();
		$count = 0;
		$values = $this->getAllBySearch("", "", 0);
		
		foreach ($values as $ticket) {
			if (!$read->isRead('ticket', $ticket['id'], $userId) && $count < $limit) {
				$unreadTickets[] = $ticket;
				$count++;
			}
		}
		
		return $unreadTickets;
	}

	/**
	 * getAll - get all Tickets, depending on userrole
	 *
	 * @access public
	 * @return array
	 */
	public function getAll($limit = 9999) {

		$id = $_SESSION['userdata']['id'];
		$users = new users();
		
		if ($users->isAdmin($id)) {
			$values = $this->getAdminTickets($limit);	
		} else {		
			$values = $this->getUsersTickets($id, $limit);
		}
		
		return $values;
	}
	
	/* WTF */
	public function getUsersTickets($id,$limit) {
				
		$sql = "SELECT
						ticket.id,
						ticket.headline,
						ticket.type, 
						ticket.description,
						ticket.date,
						ticket.dateToFinish,
						ticket.projectId,
						ticket.priority,
						ticket.status,
						project.name as projectName,
						client.name as clientName,
						client.name as clientName,
						t1.firstname AS authorFirstname, 
						t1.lastname AS authorLastname,
						t2.firstname AS editorFirstname,
						t2.lastname AS editorLastname
				FROM zp_user as user
				INNER JOIN zp_clients as client ON user.clientId = client.id
					RIGHT JOIN zp_projects as project ON client.id = project.clientId  
					RIGHT JOIN zp_tickets as ticket ON project.id = ticket.projectId 
						LEFT JOIN zp_user AS t1 ON ticket.userId = t1.id
						LEFT JOIN zp_user AS t2 ON ticket.editorId = t2.id
				WHERE user.id = :id AND (zp_projects.state > '-1' OR zp_projects.state IS NULL)
				
				ORDER BY ticket.id DESC
				LIMIT :limit";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id',$id,PDO::PARAM_STR);
		$stmn->bindValue(':limit',$limit,PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;
	}
	
	public function getAdminTickets($limit) {

		$sql = "SELECT
						Distinct(ticket.id),
						ticket.headline,
						ticket.type, 
						ticket.description,
						ticket.date,
						ticket.dateToFinish,
						ticket.projectId,
						ticket.priority,
						ticket.status,
						project.name as projectName,
						client.name as clientName,
						client.name as clientName,
						t1.firstname AS authorFirstname, 
						t1.lastname AS authorLastname,
						t2.firstname AS editorFirstname,
						t2.lastname AS editorLastname
				FROM zp_user as user
				INNER JOIN zp_clients as client ON user.clientId = client.id
					RIGHT JOIN zp_projects as project ON client.id = project.clientId  
					RIGHT JOIN zp_tickets as ticket ON project.id = ticket.projectId 
						LEFT JOIN zp_user AS t1 ON ticket.userId = t1.id
						LEFT JOIN zp_user AS t2 ON ticket.editorId = t2.id
				ORDER BY ticket.id DESC
				LIMIT :limit";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindvalue(':limit',$limit,PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;
		
	}
	
	public function buildJSONTimeline($id) {
			
		$path = $_SERVER['DOCUMENT_ROOT']."/userdata/timeline/";
		$file = "timeline-". $id .".json";	
		$sql = "SELECT 
						th.changeType, th.changeValue, th.dateModified, th.userId, 
						ticket.headline, ticket.description,
						user.firstname, user.lastname 
					FROM zp_ticketHistory as th
					INNER JOIN zp_user as user ON th.userId = user.id
					INNER JOIN zp_tickets as ticket ON th.ticketId = ticket.id
					WHERE ticketId = :ticketId";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':ticketId',$id,PDO::PARAM_STR);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		$response = array();
		$posts = array();
		foreach ($values as $value) {
			$description = $value['description'];		
			$posts[] = array(
						'headline' 	=> $value['headline'],
						'text'		=> $value['description'],
						'startDate'	=> $value['dateModified'],
						'asset' => array('caption' => 'Test', 'media' => '', 'credit' => '')
			);
		}
		
		$response['timeline'] = array('headline' => 'Ticket #'.$id, 'type' => 'default', 'text' => $description, 'date' => $posts);
		
		$fh = fopen($path.$file, 'w');
		fwrite($fh, json_encode($response));
		fclose($fh);
		
	}
	
	public function changeStatus($id,$status) {
								
		$newValues = array();					
		$newValues['status'] = $status;				
		$this->addTicketChange($_SESSION['userdata']['id'],$id,$newValues);
			
		
		$sql = "UPDATE zp_tickets SET status=:status WHERE id=:id";

		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id',$id,PDO::PARAM_STR);
		$stmn->bindValue(':status',$status,PDO::PARAM_STR);
		
		$return = $stmn->execute();
		$stmn->closeCursor();
		
		
		
	
		
		return $return;
	}

	/**
	 * getTicketCost - adds up how much the current cost of the ticket is
	 * 
	 * @param id
	 * @return int
	 */
	public function getTicketCost($id) {
		
			$query = "SELECT * FROM `zp_timesheets` WHERE ticketId='$id'";
			
			$this->db->dbQuery($query);
		
			$total = 0;
		
			foreach($this->db->dbFetchResults() as $times) {
						
				$users = new users();
				
				$user = $users->getUser($times['userId']);
				
				$wage = $user['wage'];
				
				$total += ($wage * $times['hours']);
				
			}
		
			return $total;
	}
	
	public function countMyTickets($id) {
		
		$sql = 'SELECT count(*) as count FROM zp_tickets 
				WHERE editorId LIKE "%'.$id.'%"';
				
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->execute();
		$values = $stmn->fetch();
		$stmn->closeCursor();
		
		return $values['count'];
	}
	
	public function getAssignedTickets($id,$limit=NULL) {
				
		$sql = 'SELECT id, headline, dateToFinish FROM zp_tickets 
				WHERE editorId LIKE "%'.$id.'%" ORDER BY id DESC';
				
		if($limit!=NULL)
			$sql .= " LIMIT :limit";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		
		if ($limit!=NULL)
			$stmn->bindValue(':limit',$limit,PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;
	}
	
	/**
	 * getUSerTickets - get Tickets related to a user and state
	 *
	 * @param $status
	 * @param $id
	 * @return array
	 */
	public function getUserTickets($status, $id) {
		$query = "SELECT
						SQL_CALC_FOUND_ROWS
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.description,
						zp_tickets.date,
						zp_tickets.dateToFinish,
						zp_tickets.projectId,
						zp_tickets.priority,
						zp_tickets.status,
						zp_projects.name AS projectName,
						zp_clients.name AS clientName,
						t1.lastname AS authorLastname,
						t1.firstname AS authorFirstname, 
						t2.firstname AS editorFirstname,
						t2.lastname AS editorLastname
					FROM 
						zp_tickets JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
						JOIN zp_clients ON zp_projects.clientId = zp_clients.id
						LEFT JOIN zp_user AS t1 ON zp_tickets.userId = t1.id
						LEFT JOIN zp_user AS t2 ON zp_tickets.editorId = t2.id
						
					WHERE 
						(zp_tickets.userId = '".$id."'
							OR
						zp_tickets.editorId = '".$id."')
					AND zp_tickets.status IN( ".$status." )
					GROUP BY
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.description,
						zp_tickets.date,
						zp_tickets.dateToFinish,
						zp_tickets.projectId,
						zp_tickets.priority,
						zp_tickets.status
					ORDER BY ".$this->sortBy." DESC ".$this->limitSelect.""; 
			
		$this->db->dbQuery($query);

		return $this->db->dbFetchResults();
	}

	public function getAvailableUsersForTicket() {
		/*
		 *  A user is not an "editor"
		$sql = "SELECT 
					Distinct(projectRelation.userId), 
					user.username, user.firstname, user.lastname, user.id
				FROM zp_relationUserProject as projectRelation
				INNER JOIN zp_user as user ON projectRelation.userId = user.id
				WHERE projectId=:id";
				
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id', $projectId, PDO::PARAM_INT);
		
		$stmn->execute();
		$users = $stmn->fetchAll();
		$stmn->closeCursor();
		*/
		
		//Get the projects the current user is assigned to
		
		
		$sql = "SELECT 
					DISTINCT user.username, 
					user.firstname, 
					user.lastname, 
					user.id 
				FROM zp_user as user 
				JOIN zp_relationUserProject ON user.id = zp_relationUserProject.userId
				
				WHERE zp_relationUserProject.projectId IN 
				(
					SELECT 
						zp_relationUserProject.projectId 
					FROM zp_relationUserProject WHERE userId = ".$_SESSION['userdata']["id"]."
				)";
	
		$stmn = $this->db->{'database'}->prepare($sql);
		
		
		$stmn->execute();
		$admin = $stmn->fetchAll();
		$stmn->closeCursor();	
		
		return $admin;
	}
	
	/**
	 * getNumPages - get REAL number of pages even with LIMIT in SELECT-statement
	 *
	 * @access public
	 * @return integer
	 */
	public function getNumPages(){

		$row = $this->db->dbQuery("SELECT FOUND_ROWS()")->dbFetchResults();

		$numRows = (int)$row[0]['FOUND_ROWS()'];

		$numPages = ceil($numRows / $this->rowsPerPage);

		return $numPages;
	}

	/**
	 * pageLimiter - set the LIMIT-statement for a query
	 *
	 * @access public
	 *
	 */
	public function pageLimiter(){

		$this->limitSelect = " LIMIT 0, 10";

		$begin = $this->page * $this->rowsPerPage;

		$end = $this->rowsPerPage;

		$this->limitSelect = " LIMIT ".$begin.", ".$end."";

	}

	/**
	 * setPage - set the page that is displayed
	 *
	 * @access public
	 * @param $page
	 *
	 */
	public function setPage($page) {
		if(is_numeric($page) && $page > 0){
			$this->page = $page - 1;
		}else{
			$this->page = 0;
		}
	}

	/**
	 * setRowsPerPage - set the rows that are displayed per page
	 *
	 * @access public
	 * @param $rows
	 *
	 */
	public function setRowsPerPage($rows) {

		if(is_numeric($page) === true){

			$this->rowsPerPage = $rows;

		}
	}

	/**
	 * getAllBySearch - get Tickets by a serach term and/or a filter
	 *
	 * @access public
	 * @param $term
	 * @param $filter
	 * @return array
	 */
	public function getAllBySearch($term, $filter, $closedTickets = 0) {

		if($filter == ''  && $term != ''){

			$whereClause = "AND (zp_tickets.id LIKE '%".$term."%' OR zp_tickets.headline LIKE '%".$term."%' OR zp_tickets.description LIKE '%".$term."%')";

		}elseif($filter != '' && $term == ''){

			$whereClause = "AND (zp_tickets.projectId = '".$filter."')";

		}elseif($filter != '' && $term != ''){

			$whereClause = "AND ((zp_tickets.id LIKE '%".$term."%' OR zp_tickets.headline LIKE '%".$term."%' OR zp_tickets.description LIKE '%".$term."%') AND (zp_tickets.projectId = '".$filter."'))";
		}else {
			$whereClause = "";
		}
		
		if($closedTickets == 0){
			$whereClause .= " AND zp_tickets.status <> 0 ";
		}

		
				$query = "SELECT
							zp_tickets.id,
							zp_tickets.headline, 
							zp_tickets.description,
							zp_tickets.date,
							zp_tickets.dateToFinish,
							zp_tickets.projectId,
							zp_tickets.priority,
							zp_tickets.type,
							zp_tickets.status,
							zp_tickets.dependingTicketId,
							zp_projects.name AS projectName,
							zp_clients.name AS clientName,
							t1.lastname AS authorLastname,
							t1.firstname AS authorFirstname, 
							t2.firstname AS editorFirstname,
							t2.lastname AS editorLastname
						FROM 
							zp_tickets 
							LEFT JOIN zp_relationUserProject USING (projectId)
							LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
							LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
							LEFT JOIN zp_user AS t1 ON zp_tickets.userId = t1.id
							LEFT JOIN zp_user AS t2 ON zp_tickets.editorId = t2.id
						
							WHERE zp_relationUserProject.userId = ".$_SESSION['userdata']['id']." AND (zp_projects.state > '-1' OR zp_projects.state IS NULL)
						 ".$whereClause ."
						
						ORDER BY ".$this->sortBy." DESC"; 		
					
		

		return $this->db->dbQuery($query)->dbFetchResults();

	}

	/**
	 * 
	 * @access public
	 * @param id
	 * 
	 */
	public function sendAlert($id) {
									
		$mail = new mailer();
		$user = new users();							
	
		// send alert email !
		$row = $user->getUser($id);
								
		$emailTo = $row['user'];
								
		$to[] = $emailTo;
								
		$subject = "Alert: Hours spent have exceeded planned hours";
								
		$mail->setSubject($subject);
								
		$text = "Hello ".$emailTo.",
								
			This is a friendly reminder that you have surpassed
								
			the estimated hours for this project. While we 
									
			understand it is impossible to meet every deadline
									
			we encourage you to be as diligent as possible with
									
			your workload.";
										
		$mail->setText($text);
								
		$mail->sendMail($to);
	
	}
	
	public function addTicketChange($userId,$ticketId,$values) {
		
		$fields = array(
				'headline' => 'headline',
				'type' => 'type',
				'description' => 'description',
				'project' => 'projectId',
				'priority' => 'priority',
				'deadline' => 'dateToFinish',
				'editors' => 'editorId',
				'fromDate' => 'editFrom',
				'toDate' => 'editTo',
				'staging' => 'staging',
				'production' => 'production',
				'planHours'	=> 'planHours',
				'status' => 'status');
		
		$changedFields = array();
		
		$sql = "SELECT * FROM zp_tickets WHERE id=:ticketId LIMIT 1";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':ticketId',$ticketId,PDO::PARAM_INT);
		
		$stmn->execute();
		$oldValues = $stmn->fetch();
		$stmn->closeCursor();	
		
		
		// compare table
		foreach($fields as $enum => $dbTable) {

			if (isset($values[$dbTable]) === true && ($oldValues[$dbTable] != $values[$dbTable]) && ($values[$dbTable] != "")) {
				$changedFields[$enum] = $values[$dbTable];
			}
		
		}
		

		$sql = "INSERT INTO zp_ticketHistory (
					userId, ticketId, changeType, changeValue, dateModified
				) VALUES (
					:userId, :ticketId, :changeType, :changeValue, NOW()
				)";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		
		foreach ($changedFields as $field => $value) {
		
			$stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
			$stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
			$stmn->bindValue(':changeType', $field, PDO::PARAM_INT);
			$stmn->bindValue(':changeValue', $value, PDO::PARAM_INT);
			$stmn->execute();
		}
		
		$stmn->closeCursor();				
		
	}
	
	/**
	 * getTicket - get a specific Ticket depending on the role
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getTicket($id) {

		$query = "SELECT
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.type,
						zp_tickets.description,
						zp_tickets.date,
						DATE_FORMAT(zp_tickets.date, '%Y,%m,%e') AS timelineDate, 
						DATE_FORMAT(zp_tickets.dateToFinish, '%Y,%m,%e') AS timelineDateToFinish, 
						zp_tickets.dateToFinish,
						zp_tickets.projectId,
						zp_tickets.priority,
						zp_tickets.status,
						zp_tickets.staging,
						zp_tickets.production,
						zp_tickets.userId,
						zp_tickets.editorId,
						zp_tickets.os,
						zp_tickets.planHours,
						zp_tickets.browser,
						zp_tickets.resolution,
						zp_tickets.version,
						zp_tickets.url,
						zp_tickets.editFrom,
						zp_tickets.editTo,
						zp_tickets.dependingTicketId,					
						zp_projects.name AS projectName,
						zp_clients.name AS clientName,
						zp_user.firstname AS userFirstname,
						zp_user.lastname AS userLastname,
						t3.firstname AS editorFirstname,
						t3.lastname AS editorLastname
					FROM 
						zp_tickets LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
						LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
						LEFT JOIN zp_user ON zp_tickets.userId = zp_user.id
						LEFT JOIN zp_user AS t3 ON zp_tickets.editorId = t3.id
					WHERE 
						zp_tickets.id = '".$id."'
					GROUP BY
						zp_tickets.id,
						zp_tickets.headline,
						zp_tickets.type, 
						zp_tickets.description,
						zp_tickets.date,
						zp_tickets.dateToFinish,
						zp_tickets.projectId,
						zp_tickets.priority,
						zp_tickets.status,
						zp_tickets.staging,
						zp_tickets.production,
						zp_tickets.userId,
						zp_tickets.planHours,
						zp_tickets.os,
						zp_tickets.browser,
						zp_tickets.resolution,
						zp_tickets.version,
						zp_tickets.url,
						zp_tickets.editFrom,
						zp_tickets.editTo,
						zp_tickets.dependingTicketId
					LIMIT 1"; 	

		return $this->db->dbQuery($query)->dbFetchRowUnmasked();

	}
	
	public function getTicketHistory($id) {
			
		$sql = "SELECT 
		 	ticket.headline, history.userId, history.ticketId, history.changeType, history.changeValue, history.ticketId, history.dateModified,
		 	user.firstname, user.lastname
		 FROM zp_ticketHistory as history
		  	INNER JOIN zp_user as user ON history.userId = user.id 
		  	INNER JOIN zp_tickets as ticket ON history.ticketId = ticket.id
		 WHERE history.ticketId = :ticketId ORDER BY history.id DESC";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':ticketId',$id,PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;
	}

	public function getFile($id) {
		
		$query = "SELECT realName, encName FROM `zp_ticketFiles` WHERE id=:id";
		
		$stmn = $this->db->{'database'}->prepare($query);
		$stmn->bindValue(':id',$id,PDO::PARAM_INT);
		
		$stmn->execute();
		$file = $stmn->fetch();
		$stmn->closeCursor();
		
		return $file;
	}
		/**
	 * getStatus - get the Status from the status array
	 *
	 * @access public
	 * @param $status
	 * @return string
	 */
	public function getStatus($status) {

		if($status !== NULL && $status !== ''){

			return $this->state[$status];

		}else{

			return $this->state[3];
		}
	}
	
	public function getStatusPlain($status) {

		if($status !== NULL && $status !== ''){

			return $this->statePlain[$status];

		}else{

			return $this->statePlain[3];
		}
	}

	/**
	 * getType - get the Type from the type array
	 *
	 * @access public
	 * @param $type
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
	
	
	/**
	 * getPriority - get the priority from the priority array
	 *
	 * @access public
	 * @param $priority
	 * @return string
	 */
	public function getPriority($priority) {

		if($priority !== NULL && $priority !== ''){

			return $this->priority[$priority];

		}else{

			return $this->priority[1];

		}
	}

	/**
	 * addTicket - add a Ticket with postback test
	 *
	 * @access public
	 * @param array $values
	 *
	 */
	public function addTicket(array $values) {

		
		$query = "INSERT INTO zp_tickets (
						headline, 
						type, 
						description, 
						date, 
						dateToFinish, 
						priority, 
						projectId, 
						status, 
						userId, 
						os, 
						browser, 
						resolution, 
						version, 
						url, 
						editFrom, 
						editTo, 
						editorId
				) VALUES (
						:headline,
						:type,
						:description,
						:date,
						:dateToFinish,
						:priority,
						:projectId,
						:status,
						:userId,
						:os,
						:browser,
						:resolution,
						:version,
						:url,
						:editFrom,
						:editTo,
						:editorId
				)";
					
		$stmn = $this->db->{'database'}->prepare($query);

		$stmn->bindValue(':headline', $values['headline'], PDO::PARAM_STR);
		$stmn->bindValue(':type', $values['type'], PDO::PARAM_STR);
		$stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
		$stmn->bindValue(':date', $values['date'], PDO::PARAM_STR);
		$stmn->bindValue(':dateToFinish', $values['dateToFinish'], PDO::PARAM_STR);
		$stmn->bindValue(':priority', $values['priority'], PDO::PARAM_STR);
		$stmn->bindValue(':projectId', $values['projectId'], PDO::PARAM_STR);
		$stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
		$stmn->bindValue(':userId', $values['userId'], PDO::PARAM_STR);
		$stmn->bindValue(':os', $values['os'], PDO::PARAM_STR);
		$stmn->bindValue(':browser', $values['browser'], PDO::PARAM_STR);
		$stmn->bindValue(':resolution', $values['resolution'], PDO::PARAM_STR);
		$stmn->bindValue(':version', $values['version'], PDO::PARAM_STR);
		$stmn->bindValue(':url', $values['url'], PDO::PARAM_STR);
		$stmn->bindValue(':editFrom', $values['editFrom'], PDO::PARAM_STR);
		$stmn->bindValue(':editTo', $values['editTo'], PDO::PARAM_STR);
		$stmn->bindValue(':editorId', $values['editorId'], PDO::PARAM_STR);
		
		$stmn->execute();

		$stmn->closeCursor();

		return $this->db->{'database'}->lastInsertId();

	}

	/**
	 * updateTicket - Update Ticketinformation
	 *
	 * @access public
	 * @param array $values
	 * @param $id
	 *
	 */
	public function updateTicket(array $values, $id) {

		$this->addTicketChange($_SESSION['userdata']['id'],$id,$values);

		$query = "UPDATE zp_tickets
			SET 
				headline = :headline,
				type = :type,
				description=:description,
				priority=:priority, 
				projectId=:projectId, 
				status = :status,
				staging = :staging,
				production = :production,
				dateToFinish = :dateToFinish,
				os = :os,
				planHours = :planHours,
				browser	= :browser,
				resolution = :resolution,
				version = :version,
				url = :url,
				editorId = :editorId,
				editFrom = :editFrom,
				editTo = :editTo,
				dependingTicketId = :dependingTicketId
			WHERE id = :id LIMIT 1";
			
		$stmn = $this->db->{'database'}->prepare($query);
		
		$stmn->bindValue(':headline', $values['headline'], PDO::PARAM_STR);
		$stmn->bindValue(':type', $values['type'], PDO::PARAM_STR);
		$stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
		$stmn->bindValue(':priority', $values['priority'], PDO::PARAM_STR);
		$stmn->bindValue(':projectId', $values['projectId'], PDO::PARAM_STR);
		$stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
		$stmn->bindValue(':staging', $values['staging'], PDO::PARAM_STR);
		$stmn->bindValue(':production', $values['production'], PDO::PARAM_STR);
		$stmn->bindValue(':dateToFinish', $values['dateToFinish'], PDO::PARAM_STR);
		$stmn->bindValue(':os', $values['os'], PDO::PARAM_STR);
		$stmn->bindValue(':planHours', $values['planHours'], PDO::PARAM_STR);
		$stmn->bindValue(':browser', $values['browser'], PDO::PARAM_STR);
		$stmn->bindValue(':resolution', $values['resolution'], PDO::PARAM_STR);
		$stmn->bindValue(':version', $values['version'], PDO::PARAM_STR);
		$stmn->bindValue(':url', $values['url'], PDO::PARAM_STR);
		$stmn->bindValue(':editorId', $values['editorId'], PDO::PARAM_STR);
		$stmn->bindValue(':editFrom', $values['editFrom'], PDO::PARAM_STR);
		$stmn->bindValue(':editTo', $values['editTo'], PDO::PARAM_STR);
		$stmn->bindValue(':dependingTicketId', $values['dependingTicketId'], PDO::PARAM_STR);
		$stmn->bindValue(':id', $id, PDO::PARAM_STR);
		
		$stmn->execute();
		
		$stmn->closeCursor();
	}

	/**
	 * delTicket - delete a Ticket and all dependencies
	 *
	 * @access public
	 * @param $id
	 *
	 */
	public function delticket($id) {

		if($_SESSION['userdata']['role'] === 'admin' || $_SESSION['userdata']['role'] === 'employee'){

			$query = "DELETE FROM zp_tickets WHERE id = '".$id."'";
			$query2 = "DELETE FROM zp_ticket_comments WHERE ticketID = '".$id."'";
			$query3 = "DELETE FROM zp_ticketFiles WHERE ticketID = '".$id."'";

			$this->db->dbQuery($query);
			$this->db->dbQuery($query2);
			$this->db->dbQuery($query3);

		}

	}

	/**
	 * getComments - get all comments of a ticket
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getComments($id, $fatherId='', $comments=array(), $level = 0) {

		$query = "SELECT
				zp_ticket_comments.id,
				zp_ticket_comments.text, 
				zp_ticket_comments.userId, 
				zp_ticket_comments.date,
				zp_ticket_comments.commentParent,  
				zp_user.firstname,
				zp_user.lastname
				FROM zp_ticket_comments 
					LEFT JOIN zp_user ON zp_ticket_comments.userId = zp_user.id 
				WHERE 
					zp_ticket_comments.ticketId = '".$id."' ";
		
		if($fatherId == ''){ 
			
			$query .= "AND (commentParent IS NULL || commentParent = '')";
		
		}else{

			$query .= "AND commentParent = '".$fatherId."'";
		
		}
		
		$query .="
		Order BY zp_ticket_comments.date DESC";

		$results = $this->db->dbQuery($query)->dbFetchResults();
		
		foreach($results as $row){
			
			global $comments;
			//$comments[]['level']= $level;
			
			$row['level'] = $level;
			$comments[] = $row;
			
			
			
			$this->getComments($id, $row['id'], $comments, $level+1);
			
		
			
		}
		
		return $comments;
		
	}

	/**
	 * addComment - add a comment to a project
	 *
	 * @access public
	 * @param $values
	 *
	 */
	public function addComment($values) {

		$query = "INSERT INTO zp_ticket_comments (text, userId, date, ticketId, commentParent)
		VALUES ('".$values['text']."', '".$values['userId']."', '".$values['date']."', '".$values['ticketId']."', '".$values['commentParent']."')";

		$this->db->dbQuery($query);

	}

	/**
	 * deleteComment - delete a comment
	 *
	 * @access public
	 * @param $id
	 *
	 */
	public function deleteComment($id) {

		$query = "DELETE FROM zp_ticket_comments WHERE id = '".$id."' OR commentParent = '".$id."'";

		$this->db->dbQuery($query);

	}

	/**
	 * getFiles - get a list of Files related to the project
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getFiles($id) {

		$query = "SELECT
			zp_ticketFiles.id,
			zp_ticketFiles.encName, 
			zp_ticketFiles.realName, 
			zp_ticketFiles.date, 
			zp_ticketFiles.userId, 
			zp_user.firstname, 
			zp_user.lastname 
			FROM zp_ticketFiles JOIN zp_user ON zp_ticketFiles.userId = zp_user.id WHERE ticketId = '".$id."' ORDER BY date";

		return $this->db->dbQuery($query)->dbFetchResults();

	}

	/**
	 * addFile - add a file to the list
	 *
	 * @access public
	 * @param $values
	 *
	 */
	public function addFile($values) {

		$query = "INSERT INTO zp_ticketFiles (
			encName, realName, date, ticketId, userId
		) VALUES (
			:encName, :realName, NOW(), :ticketId, :userId
		)";

		$stmn = $this->db->{'database'}->prepare($query);
		$stmn->bindValue(':encName',$values['encName'],PDO::PARAM_STR);
		$stmn->bindValue(':realName',$values['realName'],PDO::PARAM_STR);
		$stmn->bindValue(':ticketId',$values['ticketId'],PDO::PARAM_STR);
		$stmn->bindValue(':userId',$values['userId'],PDO::PARAM_STR);
		
		$stmn->execute();
		$stmn->closeCursor();
		
	}

	/**
	 * deleteFile - delete a file
	 *
	 * @access public
	 * @param $file
	 *
	 */
	public function deleteFile($file) {
		$query = "DELETE FROM zp_ticketFiles WHERE encName = '".$file."' LIMIT 1";

		$this->db->dbQuery($query);
	}

	/**
	 * deleteAllFiles - delete the whole list and the files on the server
	 *
	 * @access public
	 * @param $id
	 *
	 */
	public function deleteAllFiles($id) {

		$upload = new fileupload();

		$query1 = "SELECT encName FROM zp_ticketFiles WHERE ticketId = '".$id."'";

		foreach($this->db->dbQuery($query1)->dbFetchResults() as $row) {

			$upload->deleteFile($row['encName']);

		}

	}

	/**
	 * punchIn - clock in on a specified ticket
	 *
	 * @access public
	 * @param $ticketId
	 *
	 */
	public function punchIn($ticketId) {
		$sql = "INSERT INTO `zp_punch_clock` (ticketId,userId,punchIn) VALUES ('$ticketId','".$_SESSION['userdata']['id']."',UNIX_TIMESTAMP(CURRENT_TIMESTAMP))";

		$this->db->dbQuery($sql);
	}

	/**
	 * punchOut - clock out on whatever ticket is open for the user
	 *
	 * @access public
	 *
	 */
	public function punchOut() {
		$sql = "SELECT * FROM `zp_punch_clock` WHERE userId='".$_SESSION['userdata']['id']."' AND minutes='0' AND hours='0' AND punchOut='0' LIMIT 1";
		
		foreach ($this->db->dbQuery($sql)->dbFetchResults() as $time) {
			$inTimestamp = $time['punchIn'];
			$punchId = $time['id'];	
			$ticketId = $time['ticketId'];
		}
		
		$outTimestamp = time();
		
		$totalMinutesWorked = ( $outTimestamp - $inTimestamp ) / 60;
		
		$hoursWorked = floor($totalMinutesWorked / 60);
		
		if ( $totalMinutesWorked > 60 ) {
		
			$minutesWorked = $totalMinutesWorked % 60;  
		
		} else {
			
			$minutesWorked = $totalMinutesWorked;
			
		}

		$sql = "UPDATE `zp_punch_clock` SET punchOut='$outTimestamp', hours='$hoursWorked', minutes='$minutesWorked' WHERE id='$punchId'";

		$this->db->dbQuery($sql);
		
		$sql = "SELECT * FROM `zp_tickets` WHERE id='$ticketId' LIMIT 1";
		
		foreach($this->db->dbQuery($sql)->dbFetchResults() as $ticket) {
			$type = $ticket['type'];
			$headline = $ticket['headline'];
		}
		
		$description = "Worked on: ".$ticket['headline'];
		
		$sql = "INSERT INTO `zp_timesheets` (userId,ticketId,workDate,hours,minutes,description,kind) 
			VALUES
		('".$_SESSION['userdata']['id']."','$ticketId',CURRENT_TIMESTAMP,'$hoursWorked','$minutesWorked','$description','$type')";
		
		$this->db->dbQuery($sql);
	}
	
	/**
	 * isClocked - Checks to see whether a user is clocked in
	 * 
	 * @access public
	 * @param id
	 * 
	 */
	public function isClocked($id) {
		$sql = "SELECT * FROM `zp_punch_clock` WHERE userId='".$id."' AND minutes=0 AND hours=0 AND punchOut = 0";

		$onTheClock = false;
		$query = $this->db->dbQuery($sql)->dbFetchResults();

		if (count($query) > 0) 
			$onTheClock = true;

		return $onTheClock;
	}
	
	/**
	 * 
	 * Checks whether a user has access to a ticket or not
	 * 
	 */
	public function getAccessRights($id) {
			
		$sql = "SELECT 
				
				zp_relationUserProject.userId
				
			FROM zp_tickets
			
			LEFT JOIN zp_relationUserProject ON zp_tickets.projectId = zp_relationUserProject.projectId
			
			WHERE zp_tickets.id=:id AND zp_relationUserProject.userId = :user";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->bindValue(':id',$id,PDO::PARAM_STR);
		$stmn->bindValue(':user',$_SESSION['userdata']['id'],PDO::PARAM_STR);
		
		
		$stmn->execute();
		$result = $stmn->fetchAll();
		$stmn->closeCursor();
		
			if (count($result) > 0) {
				return true;
			}else{
				return false;
			}
		
	} 
	
	public function getTimelineHistory ($id){
			
		$sql = "SELECT 
						th.changeType, 
						th.changeValue, 
						DATE_FORMAT(th.dateModified, '%Y,%m,%e') AS date, 
						th.userId, 
						ticket.id as ticketId, 
						ticket.headline, 
						ticket.description,
						user.firstname, 
						user.lastname,
						user.id AS userId
					FROM zp_ticketHistory as th
					INNER JOIN zp_user as user ON th.userId = user.id
					INNER JOIN zp_tickets as ticket ON th.ticketId = ticket.id
					WHERE ticketId = :id";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->bindValue(':id',$id,PDO::PARAM_STR);
				
		$stmn->execute();
		$result = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $result;
	
	}
}

?>
