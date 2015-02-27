<?php


class publicMenu {

	
	public function run() {
		
		// Hier instanzieren wir die Template Engine
		$tpl = new template();
		
		// Und zu guter letzt zeigen wir das Template an.
		
		$tpl->assign('menu', $this);
		$tpl->display('general.publicMenu');
	
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