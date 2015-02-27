<?php

/**
 * newTicket Class - Add a new ticket
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class newTicket extends tickets{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl 		= new template();

		$helper		= new helper();
		$projectObj = new projects();
		$user 		= new users();

		$language 	= new language();

		$language->setModule('tickets');

		$lang = $language->readIni();

		$projects 	= $projectObj->getUserProjects("open");

		$msgKey = '';


		if(isset($_POST['save'])){

			$values = array(
				'headline'		=> $_POST['headline'],
				'type'			=> $_POST['type'],
				'description'	=> $_POST['description'],
				'priority'		=> $_POST['priority'],
				'projectId'		=> $_POST['project'],
				'editorId'		=> implode(',',$_POST['editorId']),
				'userId'		=> $_SESSION['userdata']['id'],
				'date'			=> $helper->timestamp2date(date("Y-m-d H:i:s"),2),
				'dateToFinish'	=> $_POST['dateToFinish'],
				'status' 		=> 3,
				'browser' 		=> $_POST['browser'],
				'os' 			=> $_POST['os'],
				'resolution' 	=> $_POST['resolution'],
				'version' 		=> $_POST['version'],
				'url' 			=> $_POST['url'],
				'editFrom'		=> $_POST['editFrom'],
				'editTo'		=> $_POST['editTo']
			);

			if($values['headline'] === '') {

				
				$tpl->setNotification('ERROR_NO_HEADLINE','error');

			}elseif($values['description'] === ''){
					

				$tpl->setNotification('ERROR_NO_DESCRIPTION','error');

			}elseif($values['projectId'] === ''){
					
	
				$tpl->setNotification('ERROR_NO_PROJECT','error');
				
					
			}else{

				$values['date'] = $helper->timestamp2date($values['date'], 4);
				$values['dateToFinish'] = $helper->timestamp2date($values['dateToFinish'], 4);
				$values['editFrom'] = $helper->timestamp2date($values['editFrom'], 4);
				$values['editTo'] = $helper->timestamp2date($values['editTo'], 4);

				// returns last inserted id
				$id = $this->addTicket($values);

				//Take the old value to avoid nl character
				$values['description'] = $_POST['description'];
					
				$values['date'] = $helper->timestamp2date($values['date'], 2);
				$values['dateToFinish'] = $helper->timestamp2date($values['dateToFinish'], 2);
				$values['editFrom'] = $helper->timestamp2date($values['editFrom'], 2);
				$values['editTo'] = $helper->timestamp2date($values['editTo'], 2);

				$msgKey = 'TICKET_ADDED';

				$tpl->setNotification('TICKET_ADDED','success');
				
					
				//Fileupload
				if(htmlspecialchars($_FILES['file']['name']) != '' ){

					$upload = new fileupload();

					$upload->initFile($_FILES['file']);

					if($upload->error == '') {
						
						// hash name on server for security reasons
						$newname = md5($id.time());

						//Encrypt filename on server
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
								
								

						}else{

							$msgKey = 'ERROR_FILEUPLOAD_'.$upload->error.'';
						}

					}else{
							
						$msgKey = 'ERROR_FILEUPLOAD_'.$upload->error.'';

					}

				}
				/*
				//Send mail
				$mail = new mailer();

				$row = $projectObj->getProject($values['projectId']);

				$mail->setSubject(''.$lang['ZYPRO_NEW_TICKET'].' "'.$row['name'].'" ');

				$username = $user->getUser($_SESSION['userdata']['id']);

				$url = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?act=tickets.showTicket&id='.$id.'';

				$mailMsg = "".$lang['NEW_TICKET_MAIL_1']." ".$id." ".$lang['NEW_TICKET_MAIL_2']." ".$username['lastname']." ".$username['firstname']." ".$lang['NEW_TICKET_MAIL_3']." ".$row['name']." ".$lang['NEW_TICKET_MAIL_4']." ".$url." ".$lang['NEW_TICKET_MAIL_5']."";

				$mail->setText($mailMsg);

				if(is_numeric($values['editorId']) === false ){

					$mails = $user->getMailRecipients($values['projectId']);
						
				}else{
							
					$mails = $user->getSpecificMailRecipients($id);
						
				}
						
				

				$to = array();

				foreach($mails as $row){
						
					array_push($to, $row['user']);

				}

				$mail->sendMail($to);
				*/

			}
				
			$tpl->assign('values', $values);

		}

		$tpl->assign('role', $_SESSION['userdata']['role']);
		
		
		
		$tpl->assign('availableUsers', $this->getAvailableUsersForTicket());
		$tpl->assign('type', $this->getType());
//		var_dump($tpl->get)'getAll')
		$tpl->assign('info', $msgKey);
		$tpl->assign('projects', $projects);
		$tpl->assign('objTickets', $this);
		$tpl->assign('employees', $user->getEmployees());

		$tpl->display('tickets.newTicket');

	}

}
?>