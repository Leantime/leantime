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

class delTime extends timesheets{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		$msgKey = '';

			if(isset($_GET['id']) === true){

				$id = ($_GET['id']);
				
				//Delete User
				if(isset($_POST['del']) === true){

					$this->deleteTime($id);

					$msgKey = 'TIME_DELETED';

				}

				//Assign variables
				$tpl->assign('msg', $msgKey);
			

				$tpl->display('timesheets.delTime');
					
			}else{

				$tpl->display('general.error');
					
			}

		
			
	}

}
?>