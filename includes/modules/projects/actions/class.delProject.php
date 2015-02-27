<?php

/**
 * delProject Class - Delete projects
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage projects
 * @license	GNU/GPL, see license.txt
 *
 */

class delProject extends projects{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		//Only admins
		if($_SESSION['userdata']['role'] == 'admin') {

			if(isset($_GET['id']) === true){
					
				$id = (int)($_GET['id']);
					
				$msgKey = '';

				if($this->hasTickets($id)){

					$msgKey = 'PROJECT_HAS_TICKETS';

				}else{

					if(isset($_POST['del']) === true){

						$this->deleteProject($id);

						$msgKey = 'PROJECT_DELETED';
							
					}

				}

				//Assign vars
				$tpl->assign('msg', $msgKey);
				$tpl->assign('project', $this->getProject($id));
					
				$tpl->display('projects.delProject');
					
			}else{

				$tpl->display('general.error');
					
			}

		}else{
				
			$tpl->display('general.error');

		}
			
	}

}
?>