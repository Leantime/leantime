<?php 

class compose extends messages {
	
	public function run() {

		$tpl = new template();
		
		$msg = '';
		
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
				$msg = 'Message sent successfully.';
				
			} else {
				
				$msg = 'All fields are required.';
			}
		}
		
		$tpl->assign('msg',$msg);
		$tpl->assign('friends', $this->getPeople());

		$tpl->display('messages.compose');

	}
}

?>