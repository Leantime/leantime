<?php

class logout {

	public function run() {
		
		$login = new login(session::getSID());
		
		if($login->logged_in()===true){
			
			header("Location: /index.php?logout=1");
		}
	}
}