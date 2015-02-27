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

class delSystemOrg extends setting{

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
				if(isset($_POST['del']) === true&& isset($_POST['newRole']) === true){
					
					$newRole = $_POST['newRole'];
					
					$this->deleteSystemOrg($id, $newRole);

					$msgKey = 'Systemorganisation gelöscht';
					

				}

					//Assign variables
					$tpl->assign('msg', $msgKey);
					
					$tpl->assign('roles', $this->getRoles());
					$tpl->display('setting.delSystemOrg');

				
				
			}else{

				$tpl->display('general.error');
					
			}

		
			
	}

}
?>