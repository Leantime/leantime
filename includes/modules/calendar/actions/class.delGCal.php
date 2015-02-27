<?php

/**
 * delUser Class - Deleting users
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage users
 * @license	GNU/GPL, see license.txt
 *
 */

class delGCal extends calendar{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		

			if(isset($_GET['id']) === true){
					
				$id = (int)($_GET['id']);


				$msgKey = '';

				//Delete User
				if(isset($_POST['del']) === true){

					$this->deleteGCal($id);

					$msgKey = 'Kalender gelöscht';

				}

				//Assign variables
				
				$tpl->assign('msg', $msgKey);
				$tpl->display('calendar.delGCal');
					
			}else{

				$tpl->display('general.error');
					
			}

		
			
	}

}
?>