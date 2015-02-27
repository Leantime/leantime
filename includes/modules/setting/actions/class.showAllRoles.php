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

class showAllRoles extends setting{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		
			//Assign vars
			$tpl->assign('allRoles', $this->getRoles());
			

			$tpl->display('setting.showAllRoles');

		
			
	}

}
?>