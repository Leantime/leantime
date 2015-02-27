<?php

class setModuleRights extends setting {


	public function run() {
		
		
			// Hier instanzieren wir die Template Engine
			$tpl = new template();
		
			$modules = $this->getAllModules(); 
			$info = '';
			$roles = $this->getRoles();

			$rightsArray = $this->getModuleRights();
			
			if(isset($_POST['save']) === true && $_POST['save'] != ""){
				
				$values = array();
				$i = 0;
				
				foreach($modules as $key => $value){
					
					foreach($value as $row) {
						
						$module = $row;
						
						$moduleName = str_replace(".", "-", $row);
						
						if(isset($_POST[''.$key.'-'.$moduleName.'-select']) === true){

							$moduleRoles = implode(',',$_POST[''.$key.'-'.$moduleName.'-select']);
							
							$values[$i]['module'] = $key.'/'.$module;
							$values[$i]['moduleRoles'] = $moduleRoles;
							
							$i++;
							
						}
						
					}
					
				}
				
				$this->updateModuleRights($values);
				
				$modules = $this->getAllModules(); 
				$rightsArray = $this->getModuleRights();
				
			}
			
			$tpl->assign('rightsArray', $rightsArray);
			$tpl->assign('sysOrgs', $this->getAllSystemOrganisations());
			$tpl->assign('roles', $roles);
			$tpl->assign('modules', $modules);
			$tpl->assign('this', $this);
			$tpl->assign('info', $info);


			$tpl->display('setting.setModuleRights');		
		
	}
	
	
	
}
?>