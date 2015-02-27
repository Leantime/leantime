<?php

class menuUser extends setting {


	public function run() {
		
		
			// Hier instanzieren wir die Template Engine
			$tpl = new template();
		
			$menues = $this->getWholeMenu(); 
			
			$menu = '';

			if(isset($_POST['menu']) === true && $_POST['menu'] != ""){
				
				$menu = htmlentities($_POST['menu'] );
			
			}

			
			
			if(isset($_POST['save']) === true && $_POST['save'] != ""){
				
				if(isset($_POST['user']) === true){
	
					$user = $_POST['user'];
				
				}else{
	
					$user = array();
				
				}
					$info = 'Änderungen gespeichert';
					
					
					$this->deleteAllRelationsMenuUser($menu);
					
					$this->insertRelationsMenuUser($menu, $user);
				
				

			}else{
				$info = '';
			}
			
			
			$tpl->assign('menu', $menu);
			$tpl->assign('menues', $menues);
			$tpl->assign('info', $info);

			
			$tpl->assign('users', $this->getUsersWithRelation($menu));

			$tpl->display('setting.menuUser');		
		
	}
	
	
	
}
?>