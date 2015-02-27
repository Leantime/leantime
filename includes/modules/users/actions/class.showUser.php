<?php

/**
 * newUser Class - show all User
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage users
 * @license	GNU/GPL, see license.txt
 *
 */
 
class showUser extends users{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		if(isset($_GET['id'])){

			$id = ((int)$_GET['id']);
				
			$user = $this->getUser($id);

			//Assign vars
			$tpl->assign('user', $this->getUser($_GET['id']));
			$tpl->assign('roles', $this->getRole($_SESSION['userdata']['role']));
			$tpl->assign('user', $user);
//			$tpl->assign('userProjectrelation', $this->getUserProjectRelation($id));

			$tpl->display('users.showUser');

		} else {

			$tpl->display('general.error');

		}
			
	}

}
?>