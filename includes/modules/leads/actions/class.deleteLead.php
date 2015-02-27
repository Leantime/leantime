<?php

class deleteLead extends leads {
	
	public function run() {
		
		$tpl = new template();
		$id = (int)$_GET['id'];
		$language = new language();
		$language->setModule('leads');
		$language->readIni();
		
		if ($id > 0) {
			
			if (isset($_POST['delete'])) {
				$this->deleteLead($id);
				$tpl->setNotification($language->lang_echo('LEAD_DELETED'), 'success');
			}
		
			$tpl->assign('lead',$this->getLead($id));

		} else {
			
			$tpl->display('general.error');

		}
		
		$tpl->display('leads.deleteLead');
	}
	
}

?>
