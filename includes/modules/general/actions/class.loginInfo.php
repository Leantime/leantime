<?php

class loginInfo {

	public function run() {
		
		$login = new login(session::getSID());
		
		if($login->logged_in()===true){
			
			$user = new users();
			
			$profilePicture = $user->getProfilePicture($_SESSION['userdata']['id']);
			
			$tpl = new template();

			$tpl->assign("profilePicture", $profilePicture);
			$tpl->assign("userName", $_SESSION['userdata']['name']);
			$tpl->assign("userEmail", $_SESSION['userdata']['mail']);
			
			
			$tpl->display("general.loginInfo");
			
			
		}
	}
}