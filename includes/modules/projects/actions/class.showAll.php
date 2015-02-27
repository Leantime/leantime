<?php

/**
 * shwAll Class - Show all projects
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage projects
 * @license	GNU/GPL, see license.txt
 *
 */

class showAll extends projects{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */

	 public function run() {

		$tpl = new template();
			
		$tpl->assign('role', $_SESSION['userdata']['role']);
		$tpl->assign('allProjects', $this->getUserProjects());
		
		$tpl->display('projects.showAll');
		
	}

}

?>