<?php

/**
 * Users class
 *
 * @author David Bergeron <david@rpimaging.com>
 * @version 1.0
 * @package modules
 * @subpackage users
 * @license	GNU/GPL, see license.txt
 *
 */

 Class accounts {
 	
 	public function __construct() {

		$this->db = new db();

	}
	
	/**
	 * editAccount - change users info in db
	 *
	 * @access public
	 * @param $values
	 * @param $id
	 */
	public function editAccount(array $values, $id) {
		
		$query = "UPDATE `zp_user` SET
				lastname = '".$values['lastname']."',
				firstname = '".$values['firstname']."',
				phone = '".$values['phone']."',
				user = '".$values['user']."'
			WHERE id = '".$id."' LIMIT 1";
			
		$this->db->dbQuery($query);
			
	}
	
	/**
	 * 
	 * changePassword - change just the password
	 * 
	 * @access public
	 * @param $newPassword 
	 * @param $id
	 */
	public function changePassword($newPassword,$id) {
		$query = "UPDATE `zp_user` SET
				password = '".$newPassword."'
			WHERE id = '".$id."' LIMIT 1";
			
		$this->db->dbQuery($query);
	}
	
	/**
	 * getUser - get on user from db
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function getUser($id) {

		$query = "SELECT id, lastname, firstname, phone, user, role, password FROM `zp_user` WHERE id = '".($id)."' LIMIT 1";

		$row = $this->db->dbQuery($query)->dbFetchRow();

		return $this->db->dbQuery($query)->dbFetchRow();
	}
	
	/**
	 * getUser - get on user from db
	 *
	 * @access public
	 * @param $id
	 * @return array
	 */
	public function viewAccount($id) {
		
		$query = "SELECT * FROM `zp_user` WHERE id = '$id'";
		
		return $this->db->dbQuery($query)->dbFetchResults();
		
	}
	

 }
 
 ?>