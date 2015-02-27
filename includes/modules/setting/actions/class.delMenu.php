<?php

/**
 * delUser Class - Deleting users
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 *
 *
 */

defined('RESTRICTED') or die('No access');

class delMenu extends setting{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 * @return
	 */
	public function run() {

		$tpl = new template();


			if(isset($_GET['id']) === true){
					
				$id = (int)($_GET['id']);

				$msgKey = '';
				

				
				//Delete User
				if(isset($_POST['del']) === true){
					
				
					
					$this->deleteMenu($id);

					$tpl->setNotification('Menu item deleted!', 'success');
					

				}

					//Assign variables
					$tpl->assign('msg', $msgKey);
					
	
					$tpl->display('setting.delMenu');

				
				
			}else{

				$tpl->display('general.error');
					
			}

		
			
	}

}
?>