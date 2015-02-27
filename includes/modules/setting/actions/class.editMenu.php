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

class editMenu extends setting{

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

				$row = $this->getMenuById($id);

				$infoKey = '';

				//Build values array
				$values = array(
					'name'		=> $row['name'],
					'link'		=> $row['link'],
					'parent'	=> $row['parent'],
					'inTopNav'	=> $row['inTopNav'],
					'orderNum'	=> $row['orderNum'],
					'application' =>$row['application'],
					'action'	=> $row['action'],
					'module'	=> $row['module'],
					'icon'		=> $row['icon']
				);

				if(isset($_POST['save'])){
					if (isset($_POST['name'])) {
	
						$action = '';
						$module = '';
						if (isset($_POST['module'])) {
							$module = str_replace('index.php?act=', '', $_POST['module']);
							$module = explode('.', $module);
							$action = $module[1];
							$module = $module[0];
						}
	
						$values = array(
							'name'		=> 	$_POST['name'],
							'module'	=>	$module,
							'action'	=>  $action,
							'icon'		=>	$_POST['icon'],
							'parent'	=>	$_POST['parent']
						);
	
						$this->editMenu($values, $id);
	
						$tpl->setNotification('Menu item edited!', 'success');
					
					} else {
								
						$tpl->setNotification('MISSING_FIELDS', 'error');
					
					}
				}

				$getModuleLinks = $this->getAllModulesAsLinks();
				
//				$publicContent = new publicContent();
				//Assign vars
//				$tpl->assign('articles', $publicContent->getAllArticles());
				//Assign vars
				$tpl->assign('wholeMenu', $this->getWholeMenu());
				$tpl->assign('moduleLinks', $getModuleLinks);
				$tpl->assign('info', $infoKey);
				$tpl->assign('values', $values);
				$tpl->assign('applications', $this->applications);


				$tpl->display('setting.editMenu');
					
			}else{
					
				$tpl->display('general.error');
			}

	
	}

}
?>