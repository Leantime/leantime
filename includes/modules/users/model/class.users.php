<?php

/**
 * Users class
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage users
 * @license	GNU/GPL, see license.txt
 *
 */

class users {

	/**
	 * @access public
	 * @var string
	 */
	public $user;

	/**
	 * @access public
	 * @var string
	 */
	public $lastname;

	/**
	 * @access public
	 * @var string
	 */
	public $firstname;

	/**
	 * @access public
	 * @var integer
	 */
	public $role;

	/**
	 * @access public
	 * @var integer
	 */
	public $id;


	/**
	 * @access public
	 * @var array
	 */
	public $adminRoles = array(2, 4);

	/**
	 * @access public
	 * @var array
	 */
	public $status = array('active' => 'ACTIVE', 'inactive' => 'INACTIVE');

	/**
	 * @access public
	 * @var object
	 */
	private $db;

	/**
	 * __construct - neu db connection
	 *
	 * @access public
	 *
	 */
	public function __construct() {

		$this->db = new db();

	}

	/**
	 * getUser - get on user from db
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getUser($id) {

		$sql = "SELECT * FROM `zp_user` WHERE id = :id LIMIT 1";

		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id',$id,PDO::PARAM_STR);

		$stmn->execute();
		$values = $stmn->fetch();
		$stmn->closeCursor();
		
		return $values;
	}
		
 
	/**
	 * getEmployees - get all employees
	 *
	 * @access public
	 * @return array
	 */
	public function getEmployees() {

		$sql = "SELECT * FROM zp_user WHERE role = 'employee' OR role = 'admin' ORDER BY lastname";

		$stmn = $this->db->{'database'}->prepare($sql);

		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $values;

	}

	/**
	 * getAll - get all user
	 *
	 * @access public
	 * @return array
	 */
	public function getAll() {

		$query = "SELECT zp_user.id, lastname, firstname, role, role.roleName 
					FROM `zp_user` 
					INNER JOIN zp_roles as role ON zp_user.role = role.id
					ORDER BY lastname";
					
		$stmn = $this->db->{'database'}->prepare($query);
		
		$stmn->execute();
		$values = $stmn->fetchAll();
		$stmn->closeCursor();

		return $values;
	}

	public function isAdmin($userId) {
		
		$sql = "SELECT role FROM zp_user WHERE id = :id LIMIT 1";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id',$userId,PDO::PARAM_STR);

		$stmn->execute();
		$user = $stmn->fetch();
		$stmn->closeCursor();
		
		$flag = false;
		if (in_array($user['role'], $this->adminRoles)) 
			$flag = true;
		
		return $flag;
	}

	/**
	 * editUSer - edit user
	 *
	 * @access public
	 * @param array $values
	 * @param $id
	 *
	 */
	public function editUser(array $values, $id) {
			
		$query = "UPDATE `zp_user` SET
				firstname = :firstname,
				lastname = :lastname,
				username = :username,
				phone = :phone,
				status = :status,
				role = :role,
				hours = :hours,
				wage = :wage,
				clientId = :clientId
			 WHERE id = :id LIMIT 1";	
			
		$stmn = $this->db->{'database'}->prepare($query);
		$stmn->bindValue(':firstname',$values['firstname'],PDO::PARAM_STR);
		$stmn->bindValue(':lastname',$values['lastname'],PDO::PARAM_STR);
		$stmn->bindValue(':username',$values['user'],PDO::PARAM_STR);
		$stmn->bindValue(':phone',$values['phone'],PDO::PARAM_STR);
		$stmn->bindValue(':status',$values['status'],PDO::PARAM_STR);
		$stmn->bindValue(':role',$values['role'],PDO::PARAM_STR);
		$stmn->bindValue(':hours',$values['hours'],PDO::PARAM_STR);
		$stmn->bindValue(':wage',$values['wage'],PDO::PARAM_STR);
		$stmn->bindValue(':clientId',$values['clientId'],PDO::PARAM_STR);
		$stmn->bindValue(':id',$id,PDO::PARAM_STR);

		$stmn->execute();
		$stmn->closeCursor();
			
	}

	/**
	 * usernameExist - Check if a username is already in db
	 *
	 * @access public
	 * @param $username
	 * @param $userId
	 * @return boolean
	 */
	public function usernameExist($username, $userId =''){

		if ($userId != '') {

			$queryOwn = " AND id != '".$userId."' ";
		} else {

			$queryOwn = "";
		}

		$query = "SELECT COUNT(username) AS numUser FROM `zp_user` WHERE username = '".$username."' ".$queryOwn." LIMIT 1";

		$row = $this->db->dbQuery($query)->dbFetchRow();

		if($row['numUser'] == 1){

			return true;

		}else{
			
			return false;

		}
	}

	/**
	 * editOwn - Edit own Userdates
	 *
	 * @access public
	 * @param $values
	 * @param $id
	 *
	 */
	public function editOwn($values, $id){

		if($values['password'] != '') {

			$chgPW = " password = '".$values['password']."', ";

		}else{

			$chgPW = "";

		}

		$query = "UPDATE `zp_user` SET
				lastname = '".$values['lastname']."', 
				firstname = '".$values['firstname']."', 
				username = '".$values['user']."', 
				".$chgPW."
				phone = '".$values['phone']."'			 
				WHERE id = '".$id."' LIMIT 1";

		$this->db->dbQuery($query);

	}


	/**
	 * addUser - add User to db with postback test
	 *
	 * @access public
	 * @param array $values
	 *
	 */
	public function addUser(array $values) {

		$query = "INSERT INTO `zp_user` (
							firstname, 
							lastname, 
							phone, 
							username, 
							role,
							wage,
							clientId, 
							password
						) VALUES (
							:firstname,
							:lastname,
							:phone,
							:user,
							:role,
							:wage,
							:clientId,
							:password
						)";
		
		$stmn = $this->db->{'database'}->prepare($query);
		
		$stmn->bindValue(':firstname',$values['firstname'],PDO::PARAM_STR);		
		$stmn->bindValue(':lastname',$values['lastname'],PDO::PARAM_STR);		
		$stmn->bindValue(':phone',$values['phone'],PDO::PARAM_STR);		
		$stmn->bindValue(':user',$values['user'],PDO::PARAM_STR);		
		$stmn->bindValue(':role',$values['role'],PDO::PARAM_STR);
		$stmn->bindValue(':wage',$values['wage'],PDO::PARAM_STR);		
		$stmn->bindValue(':password',$values['password'],PDO::PARAM_STR);	
		$stmn->bindValue(':clientId',$values['clientId'],PDO::PARAM_STR);		
		
		
		$stmn->execute();
		$stmn->closeCursor();

	}

	/**
	 * deleteUser - delete user from db
	 *
	 * @access public
	 * @param $id
	 *
	 */
	public function deleteUser($id) {

		$query = "DELETE FROM `zp_user` WHERE zp_user.id = :id";
			
		$stmn = $this->db->{'database'}->prepare($query);
		$stmn->bindValue(':id',$id,PDO::PARAM_STR);		
		
		$stmn->execute();
		$stmn->closeCursor();
			
	}

	/**
	 * setPicture - set the profile picture for an individual
	 * 
	 * @access public
	 * @param string 
	 */
	public function setPicture($_FILE,$id) {
		
		$sql = "SELECT * FROM `zp_user` WHERE id=:id";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id',$id,PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetch();
		$stmn->closeCursor();
		
		$files = new files();
		
		if (isset($values['profileId']) && $values['profileId'] > 0) {
			$file = $files->getFile($values['profileId']);
			$img = 'userdata/'.$file['encName'].$file['extension'];
			
			$files->deleteFile($values['profileId']);
			
		}
		
		$lastId = $files->upload($_FILE,'user',$id);	
		
		var_dump($lastId);
		
		$sql = 'UPDATE `zp_user` SET profileId = :fileId WHERE id = :userId';
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':fileId',$lastId,PDO::PARAM_INT);
		$stmn->bindValue(':userId',$id,PDO::PARAM_INT);
		
		$stmn->execute();
		$stmn->closeCursor();
	}
	
	public function getProfilePicture($id) {
		
		$sql = "SELECT profileId FROM `zp_user` WHERE id = :id LIMIT 1";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id',$id,PDO::PARAM_INT);
		
		$stmn->execute();
		$value = $stmn->fetch();
		$stmn->closeCursor();
		
		$files = new files();
		$file = $files->getFile($value['profileId']);
		
		$return = '/includes/modules/general/templates/img/default-user.png';
		if ($file && file_exists($_SERVER['DOCUMENT_ROOT'].'/userdata/user/'.$file['encName'].'.'.$file['extension']))
			$return = '/userdata/user/'.$file['encName'].'.'.$file['extension'];
		
		return $return;
	}
	
	/**
	 * getRoles - get all the roles that are available
	 * 
	 * @access public
	 * 
	 */
	public function getRoles() {
		
		$sql = "SELECT * FROM `zp_roles`";
		
		return $this->db->dbQuery($sql)->dbFetchResults();
		
	}

	/**
	 * getRole - get a role by id
	 * 
	 * @access public
	 * 
	 */
	public function getRole($id) {
		
		$sql = "SELECT * FROM `zp_roles` WHERE id=:id";
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindParam(':id',$id,PDO::PARAM_INT);
		
		$stmn->execute();
		$values = $stmn->fetch();
		$stmn->closeCursor();
		
		return $values;
		
	}

	/**
	 * getMailRecipients - get mail addresses from a specific project
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getMailRecipients ($id) {

		$query = "SELECT zp_user.username FROM `zp_user` JOIN zp_relationUserProject ON zp_relationUserProject.userId = zp_user.id WHERE zp_relationUserProject.projectId = '".$id."'";

		return $this->db->dbQuery($query)->dbFetchResults();

	}
	
	public function getSpecificMailRecipients($id){
		
		$query = "SELECT 
				t1.username AS user1,
				t2.username AS user2 
			FROM zp_tickets 
			LEFT JOIN zp_user AS t1 ON zp_tickets.editorId = t1.id
			LEFT JOIN zp_user AS t2 ON zp_tickets.userId = t2.id
			WHERE zp_tickets.id = '".$id."'";
		
		$arr = $this->db->dbQuery($query)->dbFetchResults();
		
		if(is_array($arr)==true){
			
			foreach($arr as $row){
				if($row['user1'] == $row['user2']){
					
					$results[0]['user'] = $row['user1'];
					
				}else{
					$results[0]['user'] = $row['user1'];
					$results[1]['user'] = $row['user2'];
			
				}
			}
		}else{
			$results = array();
		}
		
		return $results;
		
	}

}

?>