<?php

/**
 * newUser Class - Add new user
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage users
 * @license	GNU/GPL, see license.txt
 *
 */ 

class newUser extends users{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();
		$hasher = new PasswordHash(8,TRUE);

		//only Admins
		if($_SESSION['userdata']['role'] == 'admin'){

			$values = array();
			if(isset($_POST['save'])){
				$values = array(
					'firstname'	=> ($_POST['firstname']),
					'lastname'	=> ($_POST['lastname']),
					'user'		=> ($_POST['user']),
					'phone'		=> ($_POST['phone']),
					'role'		=> ($_POST['role']),
					'password'	=> ($hasher->HashPassword($_POST['password'])),
					'clientId'	=> ($_POST['client'])
				);

				//Validation
				if($values['user'] !== '') {
					$helper = new helper();
					if ($helper->validateEmail($values['user']) == 1){
						if ($hasher->CheckPassword($_POST['password'], $values['password']) && $_POST['password'] != ''){
							if ($this->usernameExist($values['user']) === false){

								$this->addUser($values);
								$tpl->setNotification('USER_ADDED','success');

							} else {

								$tpl->setNotification('USERNAME_EXISTS','error');

							}
						} else {
							
							$tpl->setNotification('PASSWORDS_DONT_MATCH','error');
						}
					} else {
							
						$tpl->setNotification('NO_VALID_EMAIL','error');	
					}
				}else{

					$tpl->setNotification('NO_USERNAME','error');
				}

				$tpl->assign('values', $values);
			}

			$clients = new clients();
			$tpl->assign('clients', $clients->getAll());
			$tpl->assign('roles', $this->getRoles());

			$tpl->display('users.newUser');

		}else{

			$tpl->display('general.error');

		}

	}

}

?>