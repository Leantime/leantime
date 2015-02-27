<?php

/**
 * editTicket Class - Edit a ticket
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class editTicket extends tickets{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl 		= new template();
		
		$projects 	= new projects();
		$user 		= new users();
		$helper 	= new helper();
		$language 	= new language();

		$language->setModule('tickets');

		$lang = $language->readIni();

		$projects = $projects->getUserProjects("open");

		$msgKey = '';

		if(isset($_GET['id']) === true){
			$id = (int)($_GET['id']);
		}

		$row = $this->getTicket($id);
		

				
			$values = array(
					'id'				=>$row['id'],
					'headline'			=>$row['headline'],
					'type'				=>$row['type'],
					'description'		=>$row['description'],
					'priority'			=>$row['priority'],
					'production'		=>$row['production'],
					'staging'			=>$row['staging'],
					'projectId'			=>$row['projectId'],
					'userId'			=>$row['userId'],
					'date'				=>$helper->timestamp2date($row['date'], 2),
					'dateToFinish'		=>$helper->timestamp2date($row['dateToFinish'], 2),
					'status' 			=>$row['status'],
					'browser' 			=>$row['browser'],
					'os' 				=>$row['os'],
					'resolution'		=>$row['resolution'],
					'version' 			=>$row['version'],
					'url' 				=>$row['url'],
					'planHours'			=>$row['planHours'],
					'dependingTicketId' =>$row['dependingTicketId'],
					'editFrom' 			=>$helper->timestamp2date($row['editFrom'], 2),
					'editTo' 			=>$helper->timestamp2date($row['editTo'], 2),
					'editorId'			=>$row['editorId'],
					'userFirstname'		=>$row['userFirstname'],
					'userLastname'		=>$row['userLastname']
			);


			//Make copy of array for comparison later)
			$oldValues = $values;

			if(!empty($row) && $values['headline'] !== null){

				if(isset($_POST['save'])){
					
					$timesheet = new timesheets();

					//Set admin inputs to old values, no need to use hidden fields
					if($_SESSION['userdata']['role'] === 'client'){
							
						$_POST['userId'] 			= $oldValues['userId'];
						$_POST['editFrom'] 			= $oldValues['editFrom'];
						$_POST['editTo'] 			= $oldValues['editTo'];
						$_POST['editorId']			= $oldValues['editorId'];
						$_POST['planHours']			= $oldValues['planHours'];
						$_POST['dependingTicketId'] = $oldValues['dependingTicketId'];
							
					}

					if (!isset($_POST['production'])) 
						$_POST['production'] = 0;
					else 
						$_POST['production'] = 1;

					if (!isset($_POST['staging'])) 
						$_POST['staging'] = 0;
					else 
						$_POST['staging'] = 1;

					if ( isset($_POST['editorId']) && count($_POST['editorId']) ) 
						$editorId = implode(',',$_POST['editorId']);
					else
						$editorId = '';

					$values = array(
							'id'					=>$id,
							'headline'				=>$_POST['headline'],
							'type'					=>$_POST['type'],
							'description'			=>$_POST['description'],
							'projectId'				=>$_POST['project'],
							'priority'				=>$_POST['priority'],
							'editorId'				=>$editorId,
							'staging'				=>$_POST['staging'],
							'production'			=>$_POST['production'],
							'date'					=>$helper->timestamp2date(date("Y-m-d H:i:s"),2),
							'dateToFinish'			=>$_POST['dateToFinish'],
							'status' 				=>$_POST['status'],
							'browser' 				=>$_POST['browser'],
							'os' 					=>$_POST['os'],
							'planHours'				=>$_POST['planHours'],
							'resolution' 			=>$_POST['resolution'],
							'version' 				=>$_POST['version'],
							'url' 					=>$_POST['url'],
							'editFrom'				=>$_POST['editFrom'],
							'editTo'				=>$_POST['editTo'],
							'dependingTicketId'		=>$_POST['dependingTicketId'],
							'userFirstname'			=>$row['userFirstname'],
							'userLastname'			=>$row['userLastname'],
							'userId'				=>$row['userId']
					);
						
					if($values['headline'] === '') {

						$tpl->setNotification('ERROR_NO_HEADLINE', 'error');
						$msgKey = "ERROR_NO_HEADLINE";

					}elseif($values['description'] === ''){

						$tpl->setNotification('ERROR_NO_DESCRIPTION', 'error');
							
					}else{

						//Prepare dates for db
						$values['date'] 		= $helper->date2timestamp($values['date']);
						$values['dateToFinish'] = $helper->date2timestamp($values['dateToFinish']);
						$values['editFrom'] 	= $helper->date2timestamp($values['editFrom']);
						$values['editTo'] 		= $helper->date2timestamp($values['editTo']);

						//Update Ticket
						$this->updateTicket($values, $id);
							
						//Take the old value to avoid nl character
						$values['description'] 	= $_POST['description'];

						$values['date'] 		= $helper->timestamp2date($values['date'], 2);
						$values['dateToFinish'] = $helper->timestamp2date($values['dateToFinish'], 2);
						$values['editFrom'] 	= $helper->timestamp2date($values['editFrom'], 2);
						$values['editTo'] 		= $helper->timestamp2date($values['editTo'], 2);
							
						$tpl->setNotification('EDIT_SUCCESS', 'success');
//						$msgKey = "TICKET_EDITED";
						
						
						
						
						
						
							
					}

				}

				//File upload
				if(isset($_POST['upload'])){


					if(htmlspecialchars($_FILES['file']['name']) !== '' ){
							
						$upload = new fileupload();
						$upload->initFile($_FILES['file']);

						$tpl->assign('info', $upload->error);

						if ($upload->error == '') {

							// hash name on server for securty reasons
							$newname = md5($id.time());

							$upload->renameFile($newname);

							if ($upload->upload()===true) {

								$fileValues = array(
										'encName'		=>($upload->file_name),
										'realName'		=>($upload->real_name),
										'date'			=>date("Y-m-d H:i:s"),
										'ticketId'		=>($id),
										'userId'		=>($_SESSION['userdata']['id'])
								);

								$this->addFile($fileValues);

								$tpl->setNotification('FILE_UPLOADED', 'success');

							} else {
								$tpl->setNotification('ERROR_FILEUPLOAD_'.$upload->error.'', 'error');
							}

						} else {
							$tpl->setNotification('ERROR_FILEUPLOAD_'.$upload->error.'', 'error');
						}

					}else{			
						$tpl->setNotification('NO_FILE', 'error');
					}

				}
				
//				var_dump($values); die();
				
				if (!$values['projectId']) {
					$projectId = $row['projectId'];
				} else {
					$projectId = $values['projectId'];
				}
				$tpl->assign('role', $_SESSION['userdata']['role']);
				
				$tpl->assign('type', $this->getType());
				$tpl->assign('info', $msgKey);
				$tpl->assign('projects', $projects);
				$available = $this->getAvailableUsersForTicket($projectId);
				$tpl->assign('availableUsers', $available);
				$tpl->assign('values', $values);
				$tpl->assign('objTickets', $this);
				$tpl->assign('helper', $helper);

				$tpl->display('tickets.editTicket');
					
			}else{

				$tpl->display('general.error');
					
			}

		

	}

}

?>