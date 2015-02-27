<?php

class showLead extends leads {
	
	public function run() {
	
		$tpl = new template();
		
		$id = (int)$_GET['id'];
		if ($id > 0) {
		
			$lead = $this->getLead($id);

			// Comments
			$comments = new comments();
			if (isset($_POST['comment']) === true){

				$values = array(
					'text'			=> $_POST['text'],
					'date' 			=> date("Y-m-d H:i:s"),
					'userId' 		=> $_SESSION['userdata']['id'],
					'moduleId' 		=> $id,
					'commentParent' => $_POST['father']
				);

				$comments->addComment($values, 'lead');
			}			
			
			// files
			$file = new files();
			if (isset($_POST['upload'])) {
				if (isset($_FILES['file'])) {
						
					$file->upload($_FILES,'lead',$id);
						
					$tpl->setNotification('FILE_UPLOADED', 'success');
				} else {
						
					$tpl->setNotification('NO_FILE', 'error');
				}			
			} 
			
			$files = new files();
			$tpl->assign('files', $files->getFilesByModule('lead', $id));
			$tpl->assign('comments', $comments->getComments('lead', $id));
			$tpl->assign('contactInfo', $this->getLeadContact($id));
			$tpl->assign('lead', $lead);
			
		} else {
			
			$tpl->display('general.error');

		}
		
		$tpl->display('leads.showLead');
		
	}
	
}

?>
