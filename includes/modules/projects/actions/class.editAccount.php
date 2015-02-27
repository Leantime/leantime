<?php

class editAccount extends projects {
	
	public function run() {
	
		$tpl = new template();
		$id = (int)$_GET['id'];
		
		if ($id > 0) {
			
			$account = $this->getProjectAccount($id);
			$values = array(
					'name' 		=> $_POST['name'],
					'username' 	=> $_POST['username'],
					'password' 	=> $_POST['password'],
					'host' 		=> $_POST['host'],
					'kind' 		=> $_POST['kind']			
			);
			
			if (isset($_POST['accountSubmit'])) {
				$values = array(
					'name' 		=> $_POST['accountName'],
					'username' 	=> $_POST['username'],
					'password' 	=> $_POST['password'],
					'host' 		=> $_POST['host'],
					'kind' 		=> $_POST['kind']
				);
				
				$this->addAccount($values, $id);
			} else {
				
				$tpl->setNotification('MISSING_FIELDS', 'error');
			}
			
		} else {
			
			$tpl->display('general.error');
		}
		
		$tpl->assign('account', $values);
		$tpl->display('projects.editAccount');
		
	}
	
}

?>
