<?php

class header {


	public function run() {
		
		$tpl = new template();
	
		$login = new login(session::getSID());

		$tpl->assign('login', $login);

		$tpl->display('general.header');
	}
}
?>