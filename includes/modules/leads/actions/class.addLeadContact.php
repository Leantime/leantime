<?php

class addLeadContact extends leads {
	
	public function run() {
		
		$tpl = new template();
		
		$id = (int)$_GET['id'];
		
		if ($id > 0) {
			if (isset($_POST['save'])) {
				
				$values = array(
					'street' 	=> $_POST['street'],
					'zip' 		=> $_POST['zip'],
					'city' 		=> $_POST['city'],
					'state' 	=> $_POST['state'],
					'country'	=> $_POST['country'], 
					'phone' 	=> $_POST['phone'],
					'internet' 	=> $_POST['internet']
				);
				
				$this->addLeadContact($values,$id);
				$tpl->setNotification('EDIT_SUCCESS', 'success');
			}
		} else {
			
			$tpl->display('general.error');
		}
		
		$tpl->display('leads.addLeadContact');
	}
	
}

?>
