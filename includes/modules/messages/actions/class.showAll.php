<?php 

class showAll extends messages {
	
	public function run() {
	
		$tpl = new template();
		
		// Messages
		$msg = '';
		$id = NULL;

		// Compose 	
		if ( isset($_POST['send']) ) {
			if ( isset($_POST['username']) 
				&& isset($_POST['subject']) 
				&& isset($_POST['content']) ) {
				
				$values = array(
					'from_id' 	=> $_SESSION['userdata']['id'],
					'to_id' 	=> $_POST['username'],
					'subject' 	=> $_POST['subject'],
					'content' 	=> $_POST['content']
				);
				
				$this->sendMessage($values);
				$tpl->setNotification('MESSAGE_SENT', 'success');
				
			} else {
				
				$tpl->setNotification('MISSING_FIELDS', 'error');
			}
		}
		
		if ( isset($_POST['reply']) ) {
			if ( isset($_POST['message']) ) {
				$values = array(
					'content' 	=> $_POST['message'],
					'to_id' 	=> $_POST['to_id'],
					'from_id' 	=> $_SESSION['userdata']['id']
				);
				
				$this->reply($values,$_POST['parent_id']);
			}
		}

		$myMessages = $this->getMessages($_SESSION['userdata']['id']);
		
		$users = new users();
		$user = $users->getUser($_SESSION['userdata']['id']);

		if ( !isset($_GET['id']) ) {
			$messages = $this->getMessages($_SESSION['userdata']['id'],1);
			foreach ($messages as $message) {
				$id = $message['id'];
			}
		} else {
			$id = $_GET['id'];
			$this->markAsRead($id);
		}
				
		$tpl->assign('info', $msg);
		$tpl->assign('displayId', $id);
		$tpl->assign('userEmail', $user['username']);
		$tpl->assign('messages', $myMessages);		
		$tpl->assign('friends', $this->getPeople());
		
		$tpl->display('messages.showAll');	
		
	}
	
}

?>
