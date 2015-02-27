<?php

/**
 * editUSer Class - Edit user
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage users
 * @license	GNU/GPL, see license.txt
 *
 */

class editTabRights extends setting{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

			$action = base64_decode($_GET['action']);
			
			$infoKey = '';

			$roles = $this->getRoles();
			
			$tabRights = $this->getTabRights($action);
	

				
				if(isset($_POST['saveTabs'])){
					$i = 0;
					foreach($tabRights as $key => $value){

						if(isset($_POST[''.$key.'-select']) === true){
		
								$arrayRoles = $_POST[''.$key.'-select'];
																
								$moduleRoles = '';
									
								foreach($_POST[''.$key.'-select'] as $row2){
										
									$moduleRoles.=$row2.'|';
										
								}
									
								$values[$i]['action'] = $action;
								$values[$i]['tab'] = $key;
								$values[$i]['tabRights'] = $moduleRoles;
								
								$i++;
									
								
						}
					
					
					}
					
					$this->saveTabRights($action, $values);
								
					$infoKey = "Tab Rechte gespeichert";
								
					$tabRights = $this->getTabRights($action);
		
				}
				
				//Assign vars
				$tpl->assign('action', $action);
				$tpl->assign('tabArray', $tabRights);
				$tpl->assign('roles', $roles);
				$tpl->assign('info', $infoKey);
			


				$tpl->display('setting.editTabRights');
					
			

	
	}

}
?>