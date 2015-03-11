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
	
}

?>