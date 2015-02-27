<?php

/**
 * importGCal Class - Add a new client
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class editGCal extends calendar{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();

			$msgKey = '';
			$helper = new helper();
			
		
			if(isset($_GET['id']) === true) {
				
				$id = ($_GET['id']);
				
				$row = $this->getGCal($id);
				
				$values = array(
						'url'	=>$row['url'],
						'name' =>$row['name'],
						'colorClass' =>$row['colorClass']
					);
				
				if(isset($_POST['save']) === true){
					
					$values = array(
						'url'	=>($_POST['url']),
						'name' =>($_POST['name']),
						'colorClass' =>($_POST['color'])
					);
					
					$this->editGUrl($values, $id);
					
					$msgKey = 'Kalender bearbeitet';
	
					
				}
	
				$tpl->assign('values', $values);
				$tpl->assign('helper', $helper);
				$tpl->assign('info', $msgKey);
	
				$tpl->display('calendar.editGCal');
			
			}else{
				
				$tpl->display('general.error');
				
			}
				

	}

}
?>