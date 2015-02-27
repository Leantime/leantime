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

class importGCal extends calendar{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();

			$msgKey = '';
			$helper = new helper();
			
				
			$values = array(
						'url'	=>'',
						'name' =>'',
						'colorClass' =>''
					);
				
				if(isset($_POST['save']) === true){
					
					$values = array(
						'url'	=>($_POST['url']),
						'name' =>($_POST['name']),
						'colorClass' =>($_POST['color'])
					);
					
					$this->addGUrl($values);
					
					$msgKey = 'Kalender hinzugefügt';
	
					
				}
	
				$tpl->assign('values', $values);
				$tpl->assign('helper', $helper);
				$tpl->assign('info', $msgKey);
	
				$tpl->display('calendar.importGCal');
			
			
				

	}

}
?>