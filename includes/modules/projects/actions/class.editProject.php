<?php

/**
 * editProject Class - Edit projects
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage projects
 * @license	GNU/GPL, see license.txt
 *
 */

class editProject extends projects{

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

				$id = (int)($_GET['id']);

				$row = $this->getProject($id);

				$msgKey = '';

				$values = array(
					'name'			=>$row['name'],
					'details'		=>$row['details'],
					'clientId'		=>$row['clientId'],
					'state'			=>$row['state'],
					'hourBudget'	=>$row['hourBudget'],
					'assignedUsers' => $this->getProjectUserRelation($id),
					'dollarBudget' 	=>$row['dollarBudget']
				);
					
				//Edit project
				if(isset($_POST['save'])===true){
					
					if ( isset($_POST['editorId']) && count($_POST['editorId']) ) {
						$assignedUsers = $_POST['editorId'];
					} else {
						$assignedUsers = array();
					}
				
				

					$values = array(
						'name'			=> $_POST['name'],
						'details'		=> $_POST['details'],
						'clientId'		=> $_POST['clientId'],
						'state' 		=> $_POST['projectState'],
						'hourBudget'	=> $_POST['hourBudget'],
						'assignedUsers' => $assignedUsers,
						'dollarBudget'	=> $_POST['dollarBudget']
					);

					if($values['name'] !== '') {

						if($this->hasTickets($id) && $values['state'] == 1){

							$tpl->setNotification('PROJECT_HAS_TICKETS','error');

						}else{

							$this->editProject($values, $id);
								
							//Take the old value to avoid nl character
							$values['details'] = $_POST['details'];
								
								
							$tpl->setNotification('PROJECT_EDITED','success');
								
						}

					}else{

						$tpl->setNotification('NO_PROJECTTNAME','error');
							
					}
						
				}

				//Add Account
				if(isset($_POST['accountSubmit']) === true){

					$accountValues = array(
						'name'		=> $_POST['accountName'],
						'kind'		=> $_POST['kind'],
						'username'	=> $_POST['username'],
						'password'	=> $_POST['password'],
						'host'		=> $_POST['host'],
						'projectId' => $id
					);

					if($accountValues['name'] !== '') {

						$this->addProjectAccount($accountValues);

						$tpl->setNotification('ACCOUNT_ADDED','sucess');

					}else{

						$tpl->setNotification('NO_ACCOUNT_NAME','error');

					}
						
					$tpl->assign('accountValues', $accountValues);

				}

				//Upload file
				if(isset($_POST['upload']) === true){

					if($_FILES['file']['name'] !== '' ){

						$upload = new fileupload();

						$upload->initFile($_FILES['file']);
							
						if($upload->error == '') {

							//Name on Server is encoded
							$newname = md5($id.time());

							$upload->renameFile($newname);

							if($upload->upload() === true){

								$fileValues = array(
									'encName'		=>($upload->file_name),
									'realName'		=>($upload->real_name),
									'date'			=>date("Y-m-d H:i:s"),
									'ticketId'		=>($id),
									'userId'		=>($_SESSION['userdata']['id'])
								);

								$this->addFile($fileValues);

								$tpl->setNotification('FILE_UPLOADED', 'success');

							}else{

								$tpl->setNotification('ERROR_FILEUPLOAD','error');
							}

						}else{
								
							$tpl->setNotification('ERROR_FILEUPLOAD','error');
								
						}
					}else{

						$tpl->setNotification('NO_FILE','error');
					}

				}

				$helper = new helper();
				$clients = new clients();
				
				$user = new users();
				$tpl->assign('availableUsers', $user->getAll());
				
				
				
				//Assign vars
				$tpl->assign('info', $msgKey);
				$tpl->assign('clients', $clients->getAll());
				$tpl->assign('values', $values);
				$tpl->assign('files', $this->getFiles($id));
				$tpl->assign('helper', $helper);
				$tpl->assign('accounts', $this->getProjectAccounts($id));

				$tpl->display('projects.editProject');
					
			}else{
					
				$tpl->display('general.error');
					
			}

		}else{

			$tpl->display('general.error');

		}

	}

}
?>