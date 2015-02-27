<?php

/**
 * editUSer Class - Edit user
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage users
 * @license	GNU/GPL, see license.txt
 *
 */

class editUser extends users{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		//Only admins
		if($_SESSION['userdata']['role'] == 'admin'){

			if(isset($_GET['id'])===true){

				$project = new projects();

				$id = (int)($_GET['id']);
				$row = $this->getUser($id);
				$edit = false;
				$infoKey = '';
				
				//Build values array
				$values = array(
					'firstname'	=> $row['firstname'],
					'lastname'	=> $row['lastname'],
					'user'		=> $row['username'],
					'phone'		=> $row['phone'],
					'status' 	=> $row['status'],
					'role'		=> $row['role'],
					'hours'		=> $row['hours'],
					'wage'		=> $row['wage'],
					'clientId'	=> $row['clientId']
				);

				if (isset($_POST['save'])){

					$values = array(
						'firstname'	=> ($_POST['firstname']),
						'lastname'	=> ($_POST['lastname']),
						'user'		=> ($_POST['user']),
						'phone'		=> ($_POST['phone']),
						'status'	=> ($_POST['status']),
						'role'		=> ($_POST['role']),
						'hours'		=> ($_POST['hours']),
						'wage'		=> ($_POST['wage']),
						'clientId'	=> ($_POST['client'])
					);

					$changedEmail = 0;
						
					if ($row['username'] != $values['user'])
						$changedEmail = 1;

						
					if ($values['user'] !== '') {

						$helper = new helper();

						if ($helper->validateEmail($values['user']) === 1){
							if ($changedEmail == 1){
								if ($this->usernameExist($row['username'], $id) === false){

									$edit = true;
								} else {

									$tpl->setNotification('USERNAME_EXISTS', 'error');
								}
							} else {

								$edit = true;
							}
						} else {

							$tpl->setNotification('NO_VALID_EMAIL_'.$helper->validateEmail($values['user']), 'error');
						}
					} else {
						
						$tpl->setNotification('NO_USERNAME', 'error');
					}
				}
				
				//Was everything okay?
				if ($edit !== false) {
					
					$this->editUser($values, $id);
					
						
					if (isset($_POST['projects'])) { 
						if ($_POST['projects'][0] !== '0') {
							$project->editUserProjectRelations($id, $_POST['projects']);
						} else {
							$project->deleteAllProjectRelations($id);
						}
					}	
					$tpl->setNotification('EDIT_SUCCESS', 'success');	
				}

				// Get relations to projects
				$projects = $project->getUserProjectRelation($id);

				$projectrelation = array();
					
				foreach($projects as $projectId) {
					$projectrelation[] = $projectId['projectId'];
				}
					
				//Assign vars
				$clients = new clients();
				$tpl->assign('clients', $clients->getAll());
				$tpl->assign('allProjects', $project->getAll());
				$tpl->assign('values', $values);
				$tpl->assign('relations', $projectrelation);
				$tpl->assign('roles', $this->getRoles());
				$tpl->assign('status',$this->status);


				$tpl->display('users.editUser');				
			} else {
					
				$tpl->display('general.error');
			}
		} else {

			$tpl->display('general.error');

		}

	}

}
?>