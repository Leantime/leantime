<?php

/**
 * delTicket Class - Delete tickets
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class delTicket extends tickets{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		//Only admins
		if($_SESSION['userdata']['role'] == 'admin') {

			if(isset($_GET['id'])){
				$id = (int)($_GET['id']);
			}
				
			$msgKey = '';

			if(isset($_POST['del'])){
					
				$this->deleteAllFiles($id);

				$this->delTicket($id);

				$msgKey = 'TICKET_DELETED';
					
			}

			$tpl->assign('info', $msgKey);
			$tpl->assign('ticket', $this->getTicket($id));
				
			$tpl->display('tickets.delTicket');
				
		}else{
				
			$tpl->display('general.error');

		}
			
	}

}

?>