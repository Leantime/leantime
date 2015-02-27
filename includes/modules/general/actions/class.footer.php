<?php



class footer {

	public function __toString() {
		return 'Die Main Action';
	}

	public function run() {
		// Hier instanzieren wir die Template Engine
		$tpl = new template();
		// Und zu guter letzt zeigen wir das Template an.

		$tpl->display('general.footer');
	}
}
?>