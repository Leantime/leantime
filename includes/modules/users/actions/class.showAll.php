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

class showAll extends users{ 

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		//Only Admins
		if($_SESSION['userdata']['role'] == 'admin') {

			//Assign vars
			$tpl->assign('allUsers', $this->getAll());
			$tpl->assign('admin', true);
			$tpl->assign('roles', $this->getRoles());

			$tpl->display('users.showAll');

		}else{

			$tpl->display('general.error');

		}
			
	}

}
?>