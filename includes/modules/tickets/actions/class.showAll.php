<?php

/**
 * showAll Class - show all tickets (clients only related tickets)
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class showAll extends tickets{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();
		$helper = new helper();
		$projects = new projects();
		
		//Show closed tickets? (1=yes, 0=no)
		$closedTickets = 1;
		
		//if(isset($_POST['closedTickets'])===true){
			//$closedTickets = 0;
		//}

		$tpl->assign('closedTickets', $closedTickets);
		$tpl->assign('allTickets', $this->getAllBySearch('', '', $closedTickets));
		$tpl->assign('status', $this->state);
		$tpl->assign('role', $_SESSION['userdata']['role']);
		$tpl->assign('rowsPerPage', $this->rowsPerPage);
		$tpl->assign('objTickets', $this);
		$tpl->assign('helper', $helper);
		$tpl->assign('numPages', $this->getNumPages());

		$tpl->assign('allProjects', $projects->getAll());

		$tpl->display('tickets.showAll');

	}

}

?>


