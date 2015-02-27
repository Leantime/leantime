<?php

/**
 * showTicket Class - shwo single ticket
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class showTicket extends tickets{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */

	public function run() {
		
			
		$tpl = new template();
		
		$msgKey = '';
		if(isset($_GET['id']) === true){

			$id = (int)($_GET['id']);

			$ticket = $this->getTicket($id);
			$editable = true;
				
			if (!empty($ticket)){
				
				$helper = new helper();
				$file = new files();
				$user = new users();
				$comment = new comments();
				
				
				// Has the user seen this ticket already 
				$read = new read();
				if (!$read->isRead('ticket', $id, $_SESSION['userdata']['id'])) {
					$read->markAsRead('ticket', $id, $_SESSION['userdata']['id']);
				}
				
				
				//TODO New access right management...This is dumb	
				if ($ticket['userId'] == $_SESSION['userdata']['id'] 
					|| $ticket['editorId'] == $_SESSION['userdata']['id'] 
					|| $ticket['editorId'] == ''
				) $editable = true;


				//Punch times
				if (isset($_POST['punchIn']) && $this->isClocked($_SESSION['userdata']['id'])!=true) {
				
					$this->punchIn($ticket['id']);	
					
				} else if (isset($_POST['punchOut']) && $this->isClocked($_SESSION['userdata']['id'])==true) {
						
					$this->punchOut($ticket['id']);
				}


				//Upload File
				if (isset($_POST['upload'])) {
					if (isset($_FILES['file'])) {
												
						if($file->upload($_FILES, 'ticket', $id) !== false){
							$tpl->setNotification('FILE_UPLOADED', 'success');
						}else{
							$tpl->setNotification('ERROR_WHILE_UPLOADING', 'error');
						}
						
						
						
					} else {
						
						$tpl->setNotification('NO_FILE', 'error');
					}
				}

			
				//Add comment
				if(isset($_POST['comment']) === true){

					$mail = new mailer();
					
					$values = array(
						'text'		=> $_POST['text'],
						'date' 		=> date("Y-m-d H:i:s"),
						'userId' 	=> ($_SESSION['userdata']['id']),
						'moduleId' 	=> $id,
						'commentParent' => ($_POST['father'])
					);

					$comment->addComment($values, 'ticket');
					
					$tpl->setNotification('COMMENT_ADDED', 'success');
				}
				
				
				
				
				
				

				//Only admins
				if($_SESSION['userdata']['role'] == 'admin') {
						
					$editable = true;
						
					//Delete file
					if(isset($_GET['delFile']) === true){

						$file = $_GET['delFile'];

						$upload = new fileupload();

						$upload->initFile($file);

						//Delete file from server
						$upload->deleteFile($file);

						//Delete file from db
						$this->deleteFile($file);

						$msgKey = 'FILE_DELETED';

					}

					//Delete comment
					if(isset($_GET['delComment']) === true){
							
						$commentId = (int)($_GET['delComment']);

						$comment->deleteComment($commentId);

						$msgKey = 'COMMENT_DELETED';
							
					}

				}
				
				$allHours = 0;
				

					$values = array(
						'userId' => $_SESSION['userdata']['id'],
						'ticket' => $id,
						'date' => '',
						'kind' => '',
						'hours' => '',
						'description' => '',
						'invoicedEmpl' => '',
						'invoicedComp' => '',
						'invoicedEmplDate' => '',
						'invoicedCompDate' => ''
					
						);

						$timesheets = new timesheets();
						$ticketHours = $timesheets->getTicketHours($id);
						
						
						$tpl->assign('ticketHours', $ticketHours);
						$tpl->assign('userHours', $timesheets->getUsersTicketHours($id, $_SESSION['userdata']['id']));
						
						
						
						
						
						
						$userinfo = $user->getUser($values['userId']);
						$tpl->assign('kind', $timesheets->kind);
						$tpl->assign('userInfo', $userinfo);
							
						if (isset($_POST['saveTimes']) === true){

							if (isset($_POST['kind']) && $_POST['kind'] != ''){
									
								$values['kind']= $_POST['kind'];
									
							}
							if (isset($_POST['date']) && $_POST['date'] != ''){

								$date = $helper->date2timestamp($_POST['date']);
								//die($date);
								//$values['date'] = ($helper->timestamp2date($date, 4));
								$values['date'] = $date;

							}
							$values['rate'] = $userinfo['wage'];
							if (isset($_POST['hours']) && $_POST['hours'] != ''){
									
								$values['hours'] = ($_POST['hours']);
									
							}

							if (isset($_POST['description']) && $_POST['description'] != ''){
									
								$values['description'] = $_POST['description'];
									
							}


							if ($values['kind'] != ''){
									
								if ($values['date'] != ''){

									if ($values['hours'] != '' && $values['hours'] > 0){
											
										$timesheets->addTime($values);
										$tpl->setNotification('TIME_SAVED', 'success');
										
									} else {
											
										$tpl->setNotification('NO_HOURS', 'success');	

									}
								} else {

			
									$tpl->setNotification('NO_DATE', 'error');	
										
								}
									
							}else{
									
								$tpl->setNotification('NO_KIND', 'success');	
							}
							
							$tpl->assign('userId', $values['userId']);
							
						}
							

						$timesheets = new timesheets();
						$language 	= new language();
						$language->setModule('tickets');
						$lang = $language->readIni();

						$data = array();
						$data2 = array();
						$months = array();
						
						$results = $timesheets->getTicketHours($id);

						$allHours = 0;
						foreach ($results as $row) {
							if ($row['summe']) {
								$allHours += $row['summe'];
							}
						}

						$tpl->assign('timesheetsAllHours', $allHours);
							

				

				$remainingHours = $ticket['planHours'] - $allHours;
				
				
				
				$comments = $comment->getComments('ticket', $ticket['id']);
				
				$files = $file->getFilesByModule('ticket', $id);
				
				$unreadCount = count($this->getUnreadTickets($_SESSION['userdata']['id']));
				$tpl->assign('unreadCount', $unreadCount);
				
				$tpl->assign('imgExtensions', array('jpg','jpeg','png','gif','psd','bmp','tif','thm','yuv'));
				$tpl->assign('ticketHistory', $this->getTicketHistory((int)$_GET['id']));
				$tpl->assign('remainingHours', $remainingHours);
				$tpl->assign('ticketPrice', $this->getTicketCost($_GET['id']));
				$tpl->assign('info', $msgKey);
				$tpl->assign('role', $_SESSION['userdata']['role']);
				$tpl->assign('ticket', $ticket);
				$tpl->assign('objTicket', $this);
				$tpl->assign('state', $this->state);
				$tpl->assign('statePlain', $this->statePlain);
				$tpl->assign('numComments', $comment->countComments('ticket', $ticket['id']));
				$tpl->assign('comments', $comments);
				$tpl->assign('editable', $editable);
				$tpl->assign('files', $files);
				$tpl->assign('numFiles', count($files));
				$tpl->assign('helper', $helper);
				$tpl->display('tickets.showTicket');
				
			} else {

				$tpl->display('general.error');

			}

		}else{
				
			$tpl->display('general.error');

		}

	}

}

?>
