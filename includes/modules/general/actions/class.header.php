<?php

class header {

	public function __toString() {
		return 'Die Main Action';
	}

	public function run() {
		// Hier instanzieren wir die Template Engine
		$tpl = new template();
		// Und zu guter letzt zeigen wir das Template an.
		$login = new login(session::getSID());

		$tpl->assign('login', $login);

		$tpl->display('general.header');
	}
}
?>