<?php

class convertToUser extends leads {
	
	public function run() {
	
		$tpl = new template();
		$id = (int)$_GET['id'];
		
		$users = new users();
		$clients = new clients();		

		if ($id && $id > 0) {
			
			$lead = $this->getLead($id);
			$contact = $this->getLeadContact($id);
			
			$values = array(
				'user' 		=> $contact['email'],
				'password'	=> '',
				'firstname' => '',
				'lastname' 	=> '',
				'phone'		=> $contact['phone'],
				'role'		=> 3,
				'clientId'	=> $lead['clientId']
			);
			
			if (isset($_POST['save'])) {
				
				if(isset($_POST['user']) && isset($_POST['firstname']) && isset($_POST['lastname'])) {
					$hasher = new PasswordHash(8,TRUE);
					$values = array(
						'user' 		=> $_POST['user'],
						'password'	=> $hasher->HashPassword($_POST['password']),
						'firstname' => $_POST['firstname'],
						'lastname' 	=> $_POST['lastname'],
						'phone'		=> $_POST['phone'],
						'role'		=> $_POST['role'],
						'clientId'	=> $_POST['clientId']
					);
				
					if ($users->usernameExist($values['user']) !== true) {
						
						$users->addUser($values);
						$tpl->setNotification('USER_CREATED', 'success');
					} else {
						
						$tpl->setNotification('USERNAME_EXISTS', 'error');
					}
				} else {
					
					$tpl->setNotification('MISSING_FIELDS', 'error');
				}
				
			}
			
			$tpl->assign('values', $values);
			$tpl->assign('clients', $clients->getAll());
			$tpl->assign('roles', $users->getRoles());
			$tpl->display('leads.convertToUser');
		} else {
			
			$tpl->display('general.error');
		}
		
		
	}
	
}

?>
