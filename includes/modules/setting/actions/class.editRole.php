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

class editRole extends setting{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();



			if(isset($_GET['id'])===true){

				$id = (int)($_GET['id']);

				$row = $this->getRole($id);

				$infoKey = '';

				//Get relations to menue
				$menues = $this->getDefaultMenu($id);
				
			
				
				//Build values array
				$values = array(
					'roleName'	=> $row['roleName'],
					'roleDescription'	=>$row['roleDescription'],
					'menu'				=> $menues,
					'sysOrg'		=> $row['sysOrg'],
					'template'		=> $row['template']
					
				);

				if(isset($_POST['save'])){
					
					if(isset($_POST['menu'])){
	
						$menu = $_POST['menu'];
	
					}else{
	
						$menu[0]='';

					}
					
					$values = array(
						'roleName'			=> ($_POST['roleName']),
						'roleDescription'	=> ($_POST['roleDescription']),
						'menu'				=> $menu,
						'sysOrg'			=> $_POST['sysOrg'],
						'template'			=> $_POST['template']
					);

				
						
					
						
					if($values['roleName'] !== '') {

						$helper = new helper();

						if($this->roleAliasExist($values['roleName'], $id) === false){

							
							if($values['roleDescription'] !== ''){
								
								$this->editRole($values, $id);

								$infoKey = '<p>Erfolgreich bearbeitet</p>';

								$row = $this->getRole($id);
								//Get relations to menue
								$menues = $this->getDefaultMenu($id);

								//Build values array
								$values = array(
									'roleName'	=> $row['roleName'],
									'roleDescription'	=>$row['roleDescription'],
									'menu'				=> $menues,
									'sysOrg'		=> $row['sysOrg'],
									'template'		=> $row['template']
								);
								
								

							}else{
								
								$infoKey = 'Keine Beschreibung angegeben';
							}
						
						}else{

							$infoKey = 'Rolle existiert bereits';

						}
						
					}else{

						$infoKey = 'Keinen Rollen-Alias angegeben';

					}

				}

				
					
				//Assign vars
				$tpl->assign('info', $infoKey);
				$tpl->assign('values', $values);
				$tpl->assign('templates', $this->getAllTemplates());
				$tpl->assign('sysOrgs', $this->getAllSystemOrganisations());
				$tpl->assign('menu', $this->getWholeMenu());

				$tpl->display('setting.editRole');
					
			}else{
					
				$tpl->display('general.error');
			}

	
	}

}
?>