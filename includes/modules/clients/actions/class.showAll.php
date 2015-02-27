<?php

/**
 * showAll Class - Show all clients
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class showAll extends clients{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();

		//Only admins and employees
		if($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'employee') {

			if($_SESSION['userdata']['role'] == 'admin'){

				$tpl->assign('admin', true);
					
			}

			$tpl->assign('allClients', $this->getAll());

			$tpl->display('clients.showAll');

		}else{

			$tpl->display('general.error');

		}
			
	}

}
?>