<?php

/**
 * delClient Class - Deleting clients
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class delEvent extends calendar{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();

		

			if(isset($_GET['id']) === true){
					
				$id = (int)($_GET['id']);

				$msgKey = '';

				

					if(isset($_POST['del']) === true){

						$this->delEvent($id);

						$msgKey ='Termin gelöscht';
							
					}

				

				$tpl->assign('msg', $msgKey);

				$tpl->display('calendar.delEvent');
					
			}else{

				$tpl->display('general.error');
					
			}

	
			
	}

}
?>