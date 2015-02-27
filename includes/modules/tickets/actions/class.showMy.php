<?php

/**
 * showMy Class - show personal tickets. Dashboard function
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class showMy extends tickets{

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
		$user = new users();

		$tpl->assign('objTickets', $this);
		$tpl->assign('helper', $helper);
		$tpl->assign('allProjects', $projects->getAll());

		$tpl->assign('userProjectrelation', $user->getUserProjectRelation($_SESSION['userdata']['id']));

		$tpl->assign('numText', '');

		//Closed Tickets
		$tpl->assign('allClosedTickets', $this->getUserTickets('0,1', $_SESSION['userdata']['id']));

		//Open Tickets
		$tpl->assign('allOpenTickets', $this->getUserTickets('3,2,4,5,6', $_SESSION['userdata']['id']));
		$tpl->assign('role', $_SESSION['userdata']['role']);
		$tpl->assign('numPages', $this->getNumPages());

		$tpl->display('tickets.showMy');

	}

}
?>


