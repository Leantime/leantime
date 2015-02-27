<?php

class showAll extends leads {
	
	public function run() {
		
		$tpl = new template();
	
		$tpl->assign('leads', $this->getAllLeads());
		$tpl->display('leads.showAll');
		
	}
	
}

?>
