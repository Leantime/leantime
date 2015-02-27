<?php

/**
 * editOwn Class - Edit own data
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage users
 * @license	GNU/GPL, see license.txt
 *
 */

class editOwn extends users{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		$hasher = new PasswordHash(8,TRUE);
		$userId = $_SESSION['userdata']['id'];

		$row = $this->getUser($userId);

		// $projects = $this->getUserProjectRelation($userId);

		$infoKey = '';

		//Build values array
		$values = array(
			'firstname'	=>$row['firstname'],
			'lastname'	=>$row['lastname'],
			'user'		=>$row['username'],
			'phone'		=>$row['phone'],
			'role'		=>$row['role'],
		);

		//Save form
		if(isset($_POST['save'])){

			$values = array(
				'firstname'	=>($_POST['firstname']),
				'lastname'	=>($_POST['lastname']),
				'user'		=>($_POST['user']),
				'phone'		=>($_POST['phone']),
				'password'	=>($hasher->HashPassword($_POST['newPassword']))
			);

			$changedEmail = 0;
				
			if($row['username'] != $values['user']){

				$changedEmail = 1;
					
			}

			//Validation
			if($values['user'] !== '') {

				$helper = new helper();

				if($helper->validateEmail($values['user']) == 1){

					if($_POST['newPassword'] == $_POST['confirmPassword']){

						if($_POST['newPassword'] == ''){

							$values['password'] = '';

						} else {
							
							$this->editOwn($values, $userId);
							
						}

						if ($changedEmail == 1) {

							if ($this->usernameExist($values['user'], $userId) === false) {

								$this->editOwn($values, $userId);
									
								$tpl->setNotification('EDIT_SUCCESS', 'success');

							} else {
									
								$tpl->setNotification('USERNAME_EXISTS', 'error');

							}
								
						}else{
								
							$this->editOwn($values, $userId);
								
							$tpl->setNotification('EDIT_SUCCESS', 'success');

						}
							
					}else{

						$tpl->setNotification('PASSWORDS_DONT_MATCH', 'ERROR');
						
					}

				}else{

					$tpl->setNotification('NO_VALID_EMAIL', 'error');

				}

			}else{

				$tpl->setNotification('NO_USERNAME', 'error');

			}

		}

		$file = new files();
		if (isset($_POST['savePic'])) {
			
			if (isset($_FILES)) {
				
				$this->setPicture($_FILES,$_SESSION['userdata']['id']);
		
			}
		}
		//Assign vars
		$users = new users();
//		$tpl->assign('profilePic', $file->getFilesByModule('user',$_SESSION['userdata']['id']));
		$tpl->assign('profilePic',$users->getProfilePicture($_SESSION['userdata']['id']));
		$tpl->assign('info', $infoKey);
		$tpl->assign('values', $values);
		//$tpl->assign('roles', $this->roles);
		$tpl->assign('user', $row);
		
		$tpl->display('users.editOwn');

	}

}
?>