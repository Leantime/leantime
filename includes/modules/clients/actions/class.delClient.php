<?php

/**
 * delClient Class - Deleting clients
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class delClient extends clients{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();

		//Only admins
		if($_SESSION['userdata']['role'] == 'admin') {

			if(isset($_GET['id']) === true){
					
				$id = (int)($_GET['id']);

				$msgKey = '';

				if($this->hasTickets($id) === true){

					$msgKey = 'CLIENT_HAS_TICKETS';

				}else{

					if(isset($_POST['del']) === true){

						$this->deleteClient($id);

						$msgKey = 'CLIENT_DELETED';
							
					}

				}

				//Assign vars
				$tpl->assign('client', $this->getClient($id));
				$tpl->assign('msg', $msgKey);

				$tpl->display('clients.delClient');
					
			}else{

				$tpl->display('general.error');
					
			}

		}else{

			$tpl->display('general.error');

		}
			
	}

}
?>