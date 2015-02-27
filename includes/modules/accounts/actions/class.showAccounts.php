<?php

/**
 * showAccounts Class - Show all projects
 *
 * @author David Bergeron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage projects
 * @license	GNU/GPL, see license.txt
 *
 */

class showAccounts extends accounts {

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();
		
		if(isset($_SESSION['userdata']['role'])) {
		
			if($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'employee') {
		
				$id = $_SESSION['userdata']['id'];
			
				$tpl->assign('account', $this->viewAccount($id));	
			
				$tpl->display('accounts.showAccounts');
				
				if(isset($_POST['save'])) {
					
					
					
				}
					
	 		}else{
				
				$tpl->display('general.error');
			}
		}
	}
}	
	
?>