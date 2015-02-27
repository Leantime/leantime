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

class editSystemOrg extends setting{

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

				$row = $this->getSystemOrg($id);

				$infoKey = '';
	
				//Build values array
				$values = array(
					'name'	=> $row['name'],
					'alias'	=>$row['alias'],
					'modules' =>explode(',', $row['modules'])
				);

				if(isset($_POST['save'])){				
					
					
					$modules = '';
					
					foreach($_POST['modules'] as $row){
						$modules .= $row.',';
					}
					
					
					$values = array(
						'name'		=> ($_POST['name']),
						'alias'		=> ($_POST['alias']),
						'modules'	=> $modules
						
					);

					
					if($values['name'] !== '') {

						$helper = new helper();

						

							
							if($values['alias'] !== ''){
								
								$this->editSystemOrg($values, $id);

								$infoKey = '<p>Erfolgreich bearbeitet</p>';

								$row = $this->getSystemOrg($id);
								//Get relations to menue
							

								//Build values array
								$values = array(
									'name'	=> $row['name'],
									'alias'	=>$row['alias'],
									'modules' =>explode(',', $row['modules'])
								);
								
								

							}else{
								
								$infoKey = 'Keine Beschreibung angegeben';
							}
						
						
						
					}else{

						$infoKey = 'Keinen Alias angegeben';

					}

				}

				
					
				//Assign vars
				$tpl->assign('modules', $this->getAllModules());
				$tpl->assign('info', $infoKey);
				$tpl->assign('values', $values);

				$tpl->display('setting.editSystemOrg');
					
			}else{
					
				$tpl->display('general.error');
			}

	
	}

}
?>