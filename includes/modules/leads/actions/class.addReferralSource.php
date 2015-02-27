<?php

class addReferralSource extends leads {
	
	public function run() {
		
		$tpl = new template();
		$msg = '';
		
		if (isset($_POST['save'])) {
			if ( isset($_POST['alias']) && isset($_POST['title']) ) {
					
				$values = array(
					'alias' => $_POST['alias'],
					'title' => $_POST['title']
				);
				
				$this->addReferralSource($values);
				$msg = 'EDIT_SUCCESFUL';
				
			} else {
				
				$msg = 'MISSING_FIELDS';
			}
		}
		
		$tpl->assign('msg',$msg);
		$tpl->display('leads.addReferralSource');
	}	
	
}

?>