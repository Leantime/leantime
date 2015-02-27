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

class newSystemOrg extends setting{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

				$infoKey = '';
	
				//Build values array
				$values = array(
					'name'	=>'',
					'alias'	=>'',
					'modules' =>array()
					
				);

				if (isset($_POST['save'])) {				
					
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
							
							if ($values['alias'] !== ''){
								
								$this->newSystemOrg($values);

								$infoKey = '<p>Erfolgreich hinzugefügt</p>';

								$values['modules'] = explode(',', $values['modules']);

							} else {
								
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

				$tpl->display('setting.newSystemOrg');
					
	}

}
?>