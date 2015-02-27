<?php

/**
 * Project class - handle all data related to the projects
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage projects
 * @license	GNU/GPL, see license.txt
 *
 */

class projects {

	/**
	 * @access public
	 * @var string
	 */
	public $name = '';

	/**
	 * @access public
	 * @var integer
	 */
	public $id = '';

	/**
	 * @access public
	 * @var integer
	 */
	public $clientId='';

	/**
	 * @access private
	 * @var object
	 */
	private $db='';

	/**
	 * @access public
	 * @var object
	 */
	public $result='';

	/**
	 * @access public
	 * @var array state for projects
	 */
	public $state=array(0 => 'OPEN', 1 => 'CLOSED', NULL => 'OPEN');

	/**
	 * __construct - get database connection
	 *
	 * @access public
	 */
	 
	private $encryptionMethod = 'AES-256-CBC';
	
	private $secrethash = '25c6c7ff35b9979b151f2136cd13b0ff';
	 
	function __construct() {

		$this->db = new db();
			
	}

	/**
	 * getAll - get all projects open and closed
	 *
	 * @access public
	 * @param $onlyOpen
	 * @return array
	 */
	public function getAll() {

	
		$query = "SELECT
					project.id,
					project.name,
					project.clientId,
					project.hourBudget,
					project.dollarBudget,
					COUNT(ticket.projectId) AS numberOfTickets,
					client.name AS clientName,
					client.id AS clientId 
				FROM zp_projects as project
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId  
				WHERE project.active > '-1' OR project.active IS NULL
				GROUP BY 
					project.id,
					project.name,
					project.clientId
				ORDER BY clientName, project.name";
		
		$stmn = $this->db->{'database'}->prepare($query);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();

		return $values;
	}
	
	
	// Get all open user projects /param: open, closed, all
	
	public function getUserProjects($status = "all") {
		
		$query = "SELECT
					project.id,
					project.name,
					project.clientId,
					project.hourBudget,
					project.dollarBudget,
					COUNT(ticket.projectId) AS numberOfTickets,
					client.name AS clientName,
					client.id AS clientId 
				FROM zp_relationUserProject AS relation
				LEFT JOIN zp_projects as project ON project.id = relation.projectId
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId  
				WHERE relation.userId = :id AND (project.active > '-1' OR project.active IS NULL)";
				
				if($status == "open") {
					$query .= " AND (project.state = 0 OR project.state IS NULL)";
				}else if($status == "closed") {
					$query .= " AND (project.state = 1)";
				}
				
				$query .= " GROUP BY 
					project.id,
					project.name,
					project.clientId
				ORDER BY clientName, project.name";

				
		$stmn = $this->db->{'database'}->prepare($query);
		$stmn->bindValue(':id',$_SESSION['userdata']['id'],PDO::PARAM_STR);
		
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;		
	}

	
	public function getProjectAccounts($projectId) {
			
		$sql = "SELECT * FROM zp_account WHERE projectId = :projectId";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();		
	
		$returnValues = array();
		foreach($values as $value) {
			$value['name'] = openssl_decrypt($value['name'], $this->encryptionMethod, $this->secrethash); 
			$value['username'] = openssl_decrypt($value['username'], $this->encryptionMethod, $this->secrethash); 
			$value['password'] = openssl_decrypt($value['password'], $this->encryptionMethod, $this->secrethash); 
			$value['host'] = openssl_decrypt($value['host'], $this->encryptionMethod, $this->secrethash); 
			$value['kind'] = openssl_decrypt($value['kind'], $this->encryptionMethod, $this->secrethash); 
			$returnValues[] = $value;
		}
	
		return $returnValues;
	}
	
	public function getClientProjects($clientId) {
		
		$sql = "SELECT count(ticket.id) AS numberOfTickets, project.id, project.name, project.hourBudget, project.details, state, clientId
				FROM zp_projects as project
					LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId 
				WHERE clientId = :clientId";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':clientId', $clientId, PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();		
			
		return $values;
	}
	
	public function getProjectAccount($id) {
		
		$sql = "SELECT * FROM zp_account WHERE id = :id";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id', $id, PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetch();
		$stmn->closeCursor();		
	
		$returnValues = array();
		foreach($values as $value) {
			$value['name'] = openssl_decrypt($value['name'], $this->encryptionMethod, $this->secrethash); 
			$value['username'] = openssl_decrypt($value['username'], $this->encryptionMethod, $this->secrethash); 
			$value['password'] = openssl_decrypt($value['password'], $this->encryptionMethod, $this->secrethash); 
			$value['host'] = openssl_decrypt($value['host'], $this->encryptionMethod, $this->secrethash); 
			$value['kind'] = openssl_decrypt($value['kind'], $this->encryptionMethod, $this->secrethash); 
			$returnValues[] = $value;
		}
	
		return $returnValues;		
	}
	
	public function addAccount($values, $projectId) {
		
		$sql = "INSERT INTO zp_account (projectId, name, username, password, host, kind) VALUES (:projectId, :name, :username, :password, :host, :kind)";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':projectId',$projectId, PDO::PARAM_STR);
		$stmn->bindValue(':name', openssl_encrypt($values['name'], $this->encryptionMethod, $this->secrethash), PDO::PARAM_STR);
		$stmn->bindValue(':username', openssl_encrypt($values['username'], $this->encryptionMethod, $this->secrethash), PDO::PARAM_STR);
		$stmn->bindValue(':password', openssl_encrypt($values['password'], $this->encryptionMethod, $this->secrethash), PDO::PARAM_STR);
		$stmn->bindValue(':host', openssl_encrypt($values['host'], $this->encryptionMethod, $this->secrethash), PDO::PARAM_STR);
		$stmn->bindValue(':kind', openssl_encrypt($values['kind'], $this->encryptionMethod, $this->secrethash), PDO::PARAM_STR);
		
		$stmn->execute();
		$stmn->closeCursor();			
	}
	
	public function editAccount($values, $id) {
	
		$sql = "UPDATE zp_account 
					SET 
						'name' = :name AND
						'username' = :username AND
						'password' = :password AND
						'host' = :host AND
						'kind' = :kind
					WHERE id = :id";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
		$stmn->bindValue(':username', $values['username'], PDO::PARAM_STR);
		$stmn->bindValue(':password', $values['password'], PDO::PARAM_STR);
		$stmn->bindValue(':host', $values['host'], PDO::PARAM_STR);
		$stmn->bindValue(':kind', $values['kind'], PDO::PARAM_STR);
		$stmn->bindValue(':id', $id, PDO::PARAM_STR);
		
		$stmn->execute();
		$stmn->closeCursor();			
	}

	public function deleteAccount($id) {
		
		$sql = "DELETE FROM zp_account WHERE id = :id";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id', $id, PDO::PARAM_STR);
		
		$stmn->execute();
		$stmn->closeCursor();			
	}
	
	public function getProjectTickets($projectId) {
			
		$sql = "SELECT * FROM zp_tickets WHERE projectId=:projectId";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;			
	}

	/**
	 * getProject - get one project
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getProject($id) {
			
		$query = "SELECT
					zp_projects.id, 
					zp_projects.name, 
					zp_projects.clientId, 
					zp_projects.details,
					zp_projects.state,
					zp_projects.hourBudget,
					zp_projects.dollarBudget,
					COUNT(zp_tickets.id) AS numberOfTickets
				FROM zp_projects LEFT JOIN zp_tickets ON zp_projects.id = zp_tickets.projectId 
				WHERE zp_projects.id = :projectId
				GROUP BY 
					zp_projects.id, 
					zp_projects.name, 
					zp_projects.clientId, 
					zp_projects.details
				LIMIT 1";
		
		$stmn = $this->db->{'database'}->prepare($query);
		$stmn->bindValue(':projectId', $id, PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetch();
		$stmn->closeCursor();
		
		return $values;			
			
		
			
	}
	
	
	public function getProjectBookedHours($id) {
		
		$query = "SELECT zp_tickets.projectId, SUM(zp_timesheets.hours) AS totalHours
				FROM zp_tickets
				INNER JOIN zp_timesheets ON zp_timesheets.ticketId = zp_tickets.id
				WHERE projectId = " . $id;		
		
		return $this->db->dbQuery($query)->dbFetchRow();
		
	}
	
	public function recursive_array_search($needle,$haystack) {
	    foreach($haystack as $key=>$value) {
	        $current_key=$key;
	        if($needle===$value OR (is_array($value) && $this->recursive_array_search($needle,$value) !== false)) {
	            return $current_key;
	        }
	    }
	    return false;
	}
	
	public function getProjectBookedHoursArray($id) {
		
		$query = "SELECT 	zp_tickets.projectId, 
			SUM(zp_timesheets.hours) AS totalHours,
			 DATE_FORMAT(zp_timesheets.workDate,'%Y-%m-%d') AS workDate
				FROM zp_tickets
				INNER JOIN zp_timesheets ON zp_timesheets.ticketId = zp_tickets.id
				WHERE projectId =  '" . $id ."' GROUP BY zp_timesheets.workDate	
				ORDER BY workDate";		
		
		
		$results = $this->db->dbQuery($query)->dbFetchResults();
		
		$begin=date_create($results[0]["workDate"]);
		$begin->sub(new DateInterval('P1D'));
		
		$end=date_create($results[(count($results)-1)]["workDate"]);
		$end->add(new DateInterval('P1D'));
		
		$i = new DateInterval('P1D');
		
		$period =new DatePeriod($begin,$i,$end);
		
		$chartArr = array();
		$total = 0;
		
		foreach ($period as $d){
			
		  	
		   $day=$d->format('Y-m-d');	
		  $dayKey=$d->getTimestamp();	
		  
		  $key = $this->recursive_array_search($day, $results);
		
		//var_dump($key);	
		
		 if($key === false){
		 	$value = 0;
		 }else {
		 	$value = $results[$key]['totalHours'];
		 }
		 
		  $total = $total + $value;
		  $chartArr[$dayKey] = $total;
		  
		  

		}
		
		//var_dump($chartArr);
		
		return $chartArr;
		
	}


	
	
	
	public function getProjectBookedDollars($id) {
		
		$query = "SELECT zp_tickets.projectId, SUM(zp_timesheets.hours*zp_timesheets.rate) AS totalDollars
				FROM zp_tickets
				INNER JOIN zp_timesheets ON zp_timesheets.ticketId = zp_tickets.id
				WHERE projectId = " . $id;		
		
		return $this->db->dbQuery($query)->dbFetchRow();
		
	}

	/**
	 * getComments - get all comments to a project
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getComments($id, $fatherId='', $comments=array(), $level = 0) {
		$query = " SELECT
						zp_project_comments.id, 
						zp_project_comments.text, 
						zp_project_comments.datetime, 
						zp_project_comments.userId,
						zp_project_comments.commentParent,
						zp_user.firstname AS firstname, 
						zp_user.lastname AS lastname
					FROM 
						zp_project_comments JOIN zp_user ON zp_project_comments.userId = zp_user.id 
					WHERE 
						zp_project_comments.projectId = '".$id."' ";

		
	if($fatherId == ''){ 
			
			$query .= "AND (commentParent IS NULL || commentParent = '')";
		
		}else{

			$query .= "AND commentParent = '".$fatherId."'";
		
		}
		
		$query .="
		ORDER BY zp_project_comments.datetime DESC";

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
	 * addComment - add a comment to a project and postback test
	 *
	 * @access public
	 * @param $values
	 *
	 */
	public function addComment($values) {

		$testQuery = "SELECT text, userId FROM zp_project_comments WHERE
			text = '".$values['text']."' AND 
			userId = '".$values['userId']."' LIMIT 1";

		if($this->db->dbQuery($testQuery)->count() < 1){

			$query = "INSERT INTO zp_project_comments (text, datetime, userId, projectId, commentParent) VALUES ('".$values['text']."', '".$values['datetime']."', '".$values['userId']."', '".$values['projectId']."', '".$values['commentParent']."')";

			$this->db->dbQuery($query);
		}
			
	}

	/**
	 * deleteComment - delete a comment from a project
	 *
	 * @access public
	 * @param $id
	 *
	 */
	public function deleteComment($id){

		$query = "DELETE FROM zp_project_comments WHERE id = '".$id."' OR commentParent = '".$id."'";
			
		$this->db->dbQuery($query);
	}

	/**
	 * getOpenTickets - get all open tickets related to a project
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getOpenTickets($id) {

		$query = "SELECT COUNT(zp_tickets.status) AS openTickets FROM zp_tickets WHERE zp_tickets.projectId = '".$id."' AND zp_tickets.status > 0";
			
		return $this->db->dbQuery($query)->dbFetchRow();

	}

	/**
	 * addProject - add a project to a client
	 *
	 * @access public
	 * @param array $values
	 *
	 */
	public function addProject($values) {

			$query = "INSERT INTO `zp_projects` (
				`name`, `details`, `clientId`, `hourBudget`, `dollarBudget`
			) VALUES (
				:name,
				:details,
				:clientId,
				:hourBudget,
				:dollarBudget
			)";

			$stmn = $this->db->{'database'}->prepare($query);
			
			$stmn->bindValue('name', $values['name'], PDO::PARAM_STR);
			$stmn->bindValue('details', $values['details'], PDO::PARAM_STR);
			$stmn->bindValue('clientId', $values['clientId'], PDO::PARAM_STR);
			$stmn->bindValue('hourBudget', $values['hourBudget'], PDO::PARAM_STR);
			$stmn->bindValue('dollarBudget', $values['dollarBudget'], PDO::PARAM_STR);

			$stuff = $stmn->execute();
			
			
			$projectId = $this->db->{'database'}->lastInsertId();
			$stmn->closeCursor();
			
			//Add users to relation
			if(is_array($values['assignedUsers']) === true && count($values['assignedUsers']) > 0){
				
				foreach($values['assignedUsers'] as $userId){
					$this->addProjectRelation($userId, $projectId);
				}
				
			}
			
	}

	public function viewProject($id) {
		
		$query = "SELECT * FROM zp_projects
				WHERE id = '$id'";
				
		$this->db->dbQuery($query);
		
	}

	/**
	 * editProject - edit a project
	 *
	 * @access public
	 * @param array $values
	 * @param $id
	 *
	 */
	public function editProject(array $values, $id){




		$query = "UPDATE zp_projects SET
				name = '".$values['name']."', 
				details = '".$values['details']."', 
				clientId = '".$values['clientId']."',
				state = '".$values['state']."',
				hourBudget = '".$values['hourBudget']."',
				dollarBudget = '".$values['dollarBudget']."'
				WHERE id = '".$id."' LIMIT 1";

		$this->db->dbQuery($query);
		
		$this->deleteAllUserRelations($id);
		
		
		//Add users to relation
			if(is_array($values['assignedUsers']) === true && count($values['assignedUsers']) > 0){
				
				foreach($values['assignedUsers'] as $userId){
					$this->addProjectRelation($userId, $id);
				}
				
			}

	}

	/**
	 * deleteProject - delete a project
	 *
	 * @access public
	 * @param $id
	 *
	 */
	public function deleteProject($id) {

		$query = "DELETE FROM zp_projects WHERE id = '".$id."' LIMIT 1";
			
		$this->db->dbQuery($query);
			
	}

	/**
	 * hasTickets - check if there are Tickets related to a project
	 *
	 * @access public
	 * @param $id
	 * @return boolean
	 */
	public function hasTickets($id) {
		$query = "SELECT id FROM zp_tickets WHERE projectId = '".$id."' LIMIT 1";
			
		$this->db->dbQuery($query);
			
		if($this->db->count() == 0) {
				
			return false;

		}else{

			return true;

		}

	}

	/**
	 * getFiles - get a list with files related to the project
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getFiles($ticketId) {
		
		$query = "SELECT
			zp_project_files.id,
			zp_project_files.encName, 
			zp_project_files.realName, 
			zp_project_files.date,
			zp_project_files.userId, 
			zp_user.firstname, 
			zp_user.lastname 
			FROM zp_project_files LEFT JOIN zp_user ON zp_project_files.userId = zp_user.id WHERE ticketId = '".$ticketId."' ORDER BY date";

		return $this->db->dbQuery($query)->dbFetchResults();

	}

	/**
	 * 
	 * @access public
	 * 
	 */
	public function getRealName($id) {
				
		$query = mysql_query("SELECT * FROM `zp_project_files` WHERE id='".$id."'");
		while ( $row = mysql_fetch_assoc($query) ) {
			return $row['realName'];			
		}
	}
	
	/**
	 * 
	 * @access public
	 * 
	 */
	public function getEncName($id) {
				
		$query = mysql_query("SELECT * FROM `zp_project_files` WHERE id='".$id."'");
		while ( $row = mysql_fetch_assoc($query) ) {
			return $row['encName'];			
		}
	}
	
	/**
	 * addFile - add a file to the list
	 *
	 * @access public
	 * @param $values
	 *
	 */
	public function addFile($values) {
			
		$query = "INSERT INTO zp_project_files
			(encName, realName, date, ticketId, userId) 
			VALUES 
				('".$values['encName']."', '".$values['realName']."', '".$values['date']."', '".$values['ticketId']."', '".$values['userId']."')";
			
		$this->db->dbQuery($query);
			
	}

	/**
	 * deleteFile - delete a file from the list
	 *
	 * @access public
	 * @param $file
	 *
	 */
	public function deleteFile($file) {
		$query = "DELETE FROM zp_project_files WHERE encName = '".$file."' LIMIT 1";
			
		$this->db->dbQuery($query);
	}

	/**
	 * deleteAllFiles - delete all files that are related to the project from the db list
	 *
	 * @access public
	 * @param $id
	 *
	 */
	public function deleteAllFiles($id) {
			
		$upload = new fileupload();
			
		$query1 = "SELECT encName FROM zp_project_files WHERE ticketId = '".$id."'";
			
		foreach($this->db->dbQuery($query1)->dbFetchResults() as $row) {

			$upload->deleteFile($row['encName']);

		}
			
	}
	
	
	
	/**
	 * getUserProjectRelation - get all projects related to a user
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getUserProjectRelation($id) {

		$query = "SELECT
				zp_relationUserProject.userId, 
				zp_relationUserProject.projectId,
				zp_projects.name 
			FROM zp_relationUserProject JOIN zp_projects 
				ON zp_relationUserProject.projectId = zp_projects.id
			WHERE userId = '".($id)."'";

		return $this->db->dbQuery($query)->dbFetchResults();
	}
	
	public function getProjectUserRelation($id) {

		$query = "SELECT
				zp_relationUserProject.userId, 
				zp_relationUserProject.projectId,
				zp_projects.name 
			FROM zp_relationUserProject JOIN zp_projects 
				ON zp_relationUserProject.projectId = zp_projects.id
			WHERE projectId = '".($id)."'";
		
		$results = $this->db->dbQuery($query)->dbFetchResults();
		
		$users = array();
		foreach($results as $row) {
			$users[] = $row['userId'];
		}
		
		return $users;
	}

	/**
	 * getUserProjectRelation - get all projects related to a user
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */	
	public function editUserProjectRelations($id,$projects) {
		
		$sql = "SELECT id,userId,projectId FROM zp_relationUserProject WHERE userId=:id";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->bindValue(':id',$id,PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		// Add relations that don't exist
		foreach($projects as $project) {
			$exists = false;
			if(count($values)) {
				foreach($values as $value) {
					if ( $project == $value['projectId'] ) 
						$exists = true;
				}
			}
			if (!$exists) 
				$this->addProjectRelation($id, $project);
		}
		
		// Delete relations that were removed in select
		if (count($values)) {
			foreach($values as $value) {
				if (in_array($value['projectId'], $projects) !== TRUE) {
					$this->deleteProjectRelation($id, $value['projectId']);
				}
			}
		}
	}
	
	public function deleteProjectRelation($userId,$projectId) {
		
		$sql = "DELETE FROM zp_relationUserProject WHERE projectId=:projectId AND userId=:userId";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
		$stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
		
		$stmn->execute();
		
		$stmn->closeCursor();		
	}
	
	public function deleteAllProjectRelations($userId) {
		
		$sql = "DELETE FROM zp_relationUserProject WHERE userId=:userId";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
		
		$stmn->execute();
		
		$stmn->closeCursor();		
	}
	
	public function deleteAllUserRelations($projectId) {
		
		$sql = "DELETE FROM zp_relationUserProject WHERE projectId=:projectId";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
		
		$stmn->execute();
		
		$stmn->closeCursor();		
	}	
	
	public function addProjectRelation($userId,$projectId) {
		
		$sql = "INSERT INTO zp_relationUserProject (
					userId,
					projectId
				) VALUES (
					:userId,
					:projectId
				)";	
				
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
		$stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
		
		$stmn->execute();
		
		$stmn->closeCursor();
						
	}

}

?>