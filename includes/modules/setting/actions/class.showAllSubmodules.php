<?php

class showAllSubmodules extends setting {
	
	public function run() {
		
		$tpl = new template();
		
		$submodules = $this->getAllSubmodules();
		if (isset($_POST['save'])) {
			
			$insertVals = array();
			foreach ($submodules as $submodule) {
				
				$values = array(
					'alias' 	=> $submodule['alias'],
					'module' 	=> $submodule['module'],
					'submodule' => $submodule['submodule'],
					'title'		=> NULL,
					'roleIds'	=> NULL
				);
				
				if ( isset($_POST['roles-'.$submodule['alias']]) && count($_POST['roles-'.$submodule['alias']]) ) 
					$values['roleIds'] = implode(',',$_POST['roles-'.$submodule['alias']]);
				
				if (isset($_POST['title-'.$submodule['alias']]))
					$values['title'] = $_POST['title-'.$submodule['alias']];
				
				$insertVals[] = $values;
			}
			
			$this->saveSubmoduleRights($insertVals);
		}
		
		$submodules = $this->getAllSubmodules();
		
		$tpl->assign('submodules', $submodules);
		$tpl->assign('roles', $this->getRoles());
		$tpl->display('setting.showAllSubmodules');
		
	}
	
}

?>
