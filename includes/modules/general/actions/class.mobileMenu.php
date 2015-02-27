<?php

class mobileMenu {

	public function __toString() {
		return 'Die Main Action';
	}

	public function run() {
		// Hier instanzieren wir die Template Engine
		$tpl = new template();
		
		// Und zu guter letzt zeigen wir das Template an.
		$login = new login(session::getSID());

		$tpl->assign('login', $login);
		
		$tpl->assign('menu', $this);
		$tpl->display('general.mobileMenu');
	}
	
	public function getUserMenuRoots(){
		
		$db = new db();
		
		
			
			$query = "SELECT 
					zp_public_menu.id,
					zp_public_menu.name, 
					zp_public_menu.link 
				FROM  zp_public_menu
				WHERE 
				(zp_public_menu.parent IS NULL || zp_public_menu.parent = '')
				AND zp_public_menu.inTopNav = 1
				
				 
				
				ORDER BY orderNum";
			
			
		
		return $db->dbQuery($query)->dbFetchResults();
		
	} 
	
   
	public function getUserMenuNodes($parent){
		
		$db = new db();
		
		$query = "SELECT 
					zp_public_menu.name, 
					zp_public_menu.link 
				FROM zp_public_menu 
				WHERE 
				zp_public_menu.parent = '".$parent."'
				AND zp_public_menu.inTopNav = 1 ORDER BY orderNum";
			
		
		return $db->dbQuery($query)->dbFetchResults();
     
		
	} 
     
}
?>