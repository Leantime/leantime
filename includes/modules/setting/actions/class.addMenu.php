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

class addMenu extends setting{

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
					'name'		=> '',
					'parent'	=> '',
					'module'	=> '',
					'action'	=> '',
					'icon'		=> ''
				);

				if (isset($_POST['save'])){

					if (isset($_POST['module'])) {
						$module = str_replace('index.php?act=', '', $_POST['module']);
						$module = explode('.', $module);
						$action = $module[1];
						$module = $module[0];
	
						$values = array(
							'name'		=> 	$_POST['name'],
							'parent'	=>	$_POST['parent'],
							'module'	=>  $module,
							'action'	=>  $action,
							'icon'		=>  $_POST['icon']
						);
	
						$this->addMenu($values);
	
						$tpl->setNotification('New menu item successfully created', 'success'); // $infoKey = '<p>Erfolgreich hinzugefügt</p>';
					
					} else {
					
						$tpl->setNotification('MISSING_FIELDS', 'error');
						
					}
				} 

				$getModuleLinks = $this->getAllModulesAsLinks();
				
				$tpl->assign('wholeMenu', $this->getWholeMenu());
				$tpl->assign('moduleLinks', $getModuleLinks);
				$tpl->assign('info', $infoKey);
				$tpl->assign('values', $values);
				$tpl->assign('applications', $this->applications);

				$tpl->display('setting.addMenu');
	
	}

}
?>