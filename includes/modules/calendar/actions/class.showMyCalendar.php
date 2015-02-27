<?php

/**
 * showAll Class - show My Calender
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class showMyCalendar extends calendar{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();
		
		$dateFrom = date("Y-m-d");
		$dateTo = date("Y-m-d");
		
		$tpl->assign('calendar', $this->getCalendar($_SESSION['userdata']['id']));
		$tpl->assign('gCalLink', $this->getMyGoogleCalendars());
		
		$tpl->assign('ticketEditDates', $this->getTicketEditDates());
		$tpl->assign('ticketWishDates', $this->getTicketWishDates());
		$tpl->assign('dates', $this->getAllDates($dateFrom, $dateTo));
		
		$tpl->display('calendar.showMyCalendar');
		
	}

}