<?php


class menu {
	
	public function run() {
		
		$tpl = new template();
				
		$tpl->assign('menu', $this);
		$tpl->assign('menus', $this->getMenus());
		$tpl->display('general.menu');
	
	}
	
	public function __construct() {
		
		$this->db = new db();
		
	}
	
	public function getMenus() {
		
		$sql = 'SELECT * FROM zp_menu WHERE parent=0';
		
		$stmn = $this->db->{'database'}->prepare($sql);
		
		$stmn->execute();
		$menus = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $menus;
	}
	
	public function getChildren($id) {
		
		$sql = 'SELECT * FROM zp_menu WHERE parent = :id';
		
		$stmn = $this->db->{'database'}->prepare($sql);
		$stmn->bindValue(':id',$id,PDO::PARAM_STR);
		
		$stmn->execute();
		$menus = $stmn->fetchAll();
		$stmn->closeCursor();
		
		return $menus;		
	}
	
/*	public function getUserMenuRoots(){
		
		$db = new db();
		
		if($_SESSION['userdata']['id'] != 'x'){
			
			$query = "SELECT 
					zp_menu.id,
					zp_menu.name, 
					zp_menu.link 
				FROM  zp_menu
				WHERE 
				(zp_menu.parent IS NULL || zp_menu.parent = '')
				AND zp_menu.inTopNav = 1 
				
				ORDER BY orderNum";
			
			
		}else{

			$query = "SELECT 
					zp_menu.id,
					zp_menu.name, 
					zp_menu.link 
				FROM zp_usermenu 
				LEFT JOIN zp_menu ON zp_usermenu.menuId = zp_menu.id
				WHERE 
				(zp_usermenu.username = '".$_SESSION['userdata']['id']."' OR zp_usermenu.username = '".$_SESSION['userdata']['name']."')
				AND (zp_menu.parent IS NULL || zp_menu.parent = '')
				AND zp_menu.inTopNav = 1 
				AND (zp_menu.application = '".$_SESSION['application']."' OR zp_menu.application = 'general')
				ORDER BY orderNum";
		
		}
		return $db->dbQuery($query)->dbFetchResults();
		
	} 
	
	public function getUserMenuNodes($parent){
		
		$db = new db();
		
		if($_SESSION['userdata']['id'] == 'x') {
			
			$query = "SELECT 
					zp_menu.id,
					zp_menu.name, 
					zp_menu.link 
				FROM zp_menu 
				WHERE 
				zp_menu.parent = '".$parent."'
				AND zp_menu.inTopNav = 1 ORDER BY orderNum";
			
		} else {
			
			$query = "SELECT 
					zp_menu.id,
					zp_menu.name, 
					zp_menu.link 
				FROM zp_usermenu 
				LEFT JOIN zp_menu ON zp_usermenu.menuId = zp_menu.id
				WHERE (zp_usermenu.username = '".$_SESSION['userdata']['id']."' OR zp_usermenu.username = '".$_SESSION['userdata']['name']."')
				AND zp_menu.parent = '".$parent."'
				AND zp_menu.inTopNav = 1 ORDER BY orderNum";
		
		}
		return $db->dbQuery($query)->dbFetchResults();
		
	} */
	
}

?>