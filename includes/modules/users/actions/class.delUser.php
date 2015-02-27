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

class delUser extends users{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		//Only Admins
		if($_SESSION['userdata']['role'] == 'admin') {

			if(isset($_GET['id']) === true){
					
				$id = (int)($_GET['id']);

				$user = $this->getUser($id);

				$msgKey = '';

				//Delete User
				if(isset($_POST['del']) === true){

					$this->deleteUser($id);

					$msgKey = 'USER_DELETED';

				}

				//Assign variables
				$tpl->assign('msg', $msgKey);
				$tpl->assign('user', $user);

				$tpl->display('users.delUser');
					
			}else{

				$tpl->display('general.error');
					
			}

		}else{

			$tpl->display('general.error');

		}
			
	}

}
?>