<?php

/**
 * newClient Class - Add a new client
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class addEvent extends calendar{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();

			$helper = new helper();
			
			$values = array(
					'description'	=>'',
					'dateFrom'		=>'',
					'dateTo'		=>'',
					'allDay'		=>''
				);
			
			if(isset($_POST['save']) === true){

				if (isset($_POST['allDay']) === true) 
					$allDay = 'true';	
				else
					$allDay = 'false';	
				
				if (isset($_POST['dateFrom']) === true && isset($_POST['timeFrom']) === true) 
					$dateFrom = $helper->date2timestamp($_POST['dateFrom'],$_POST['timeFrom']);	
				
				
				if (isset($_POST['dateTo']) === true && isset($_POST['timeTo']) === true) 	
					$dateTo = $helper->date2timestamp($_POST['dateTo'],$_POST['timeTo']);				
				
				
				$values = array(
					'description'	=>($_POST['description']),
					'dateFrom'		=>$dateFrom,
					'dateTo'		=>$dateTo,
					'allDay'		=>$allDay
				);

				if($values['description'] !== '') {
					
					if($helper->validateTime($_POST['timeFrom']) === true){

						$this->addEvent($values);
	
						$msgKey = $tpl->setNotification('SAVE_SUCCESS', 'success');
					
					}else{
						$tpl->setNotification('WRONG_TIME_FORMAT', 'error');
					}

				}else{

					$tpl->setNotification('NO_DESCRIPTION', 'error');

				}

				$tpl->assign('values', $values);
			}

			$tpl->assign('helper', $helper);

			$tpl->display('calendar.addEvent');

	}

}
?>