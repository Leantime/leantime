<?php

class userMenu extends setting {


	public function run() {
		
		
			// Hier instanzieren wir die Template Engine
			$tpl = new template();
		
			$users = $this->getUsers(); 
			
			$user = '';

			if(isset($_POST['user']) === true && $_POST['user'] != ""){
				
				$user = htmlentities($_POST['user'] );
			
			}

			
			
			if(isset($_POST['save']) === true && $_POST['save'] != ""){
				
				if(isset($_POST['menu']) === true){
	
					$menu = $_POST['menu'];
				
				}else{

					$menu = array();	
				
				}
				
					$info = 'Änderungen gespeichert';
					
					
					$this->deleteAllRelations($user);
					
					$this->insertRelations($menu, $user);
				
				

			}else{
				$info = '';
			}
			
			
			
			$tpl->assign('users', $users);
			$tpl->assign('user', $user);
			$tpl->assign('info', $info);

			
			$tpl->assign('menu', $this->getMenu($user));

			$tpl->display('setting.userMenu');		
		
	}
	
	
	
}
?>