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

class newRole extends setting{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();



				$values = array(
						'roleName'		=> '',
						'roleDescription'		=> '',
						'menu'				=> '',
						'sysOrg'		=> '',
						'template'		=> ''
					);

				$infoKey = '';

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
						'sysOrg'		=> $_POST['sysOrg'],
						'template'		=> $_POST['template']
						
					);
	
					if($values['roleName'] !== '') {

						$helper = new helper();

						if($this->roleAliasExist($values['roleName']) === false){

							
							if($values['roleDescription'] !== ''){
								
								$this->newRole($values);

								$infoKey = '<p>Erfolgreich hinzugefügt<br />Sie müssen diese Rolle nun im Rechtesystem den passenden Modulen zuweisen</p> ';

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
				$tpl->assign('sysOrgs', $this->getAllSystemOrganisations());
				$tpl->assign('menu', $this->getWholeMenu());
				$tpl->assign('templates', $this->getAllTemplates());
				$tpl->assign('info', $infoKey);
				$tpl->assign('values', $values);


				$tpl->display('setting.newRole');

	}

}
?>