<?php

/**
 * newProject Class - New projects
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage projects
 * @license	GNU/GPL, see license.txt
 *
 */

class newProject extends projects{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

			$msgKey= '';
			$values = array(
					'name'			=>'',
					'details'		=>'',
					'clientId'		=>'',
					'hourBudget'	=>'',
					'assignedUsers' => array(),
					'dollarBudget'	=>''
				);
				
				
			if(isset($_POST['save']) === true){

				if(!isset($_POST['hourBudget']) || $_POST['hourBudget']=='' || $_POST['hourBudget']==NULL) {
					$hourBudget = '0';
				} else {
					$hourBudget = $_POST['hourBudget'];
				}
				
				
				if ( isset($_POST['editorId']) && count($_POST['editorId']) ) {
						$assignedUsers = $_POST['editorId'];
				} else {
						$assignedUsers = array();
				}
				
				
				
				$values = array(
					'name'			=>$_POST['name'],
					'details'		=>$_POST['details'],
					'clientId'		=>$_POST['clientId'],
					'hourBudget'	=>$hourBudget,
					'assignedUsers' => $assignedUsers,
					'dollarBudget'	=>$_POST['dollarBudget']
				);
					
				if ($values['name'] === '') {
						
					$msgKey = 'NO_PROJECTNAME';

				} elseif ($values['clientId'] === '') {
						
					$msgKey = 'ERROR_NO_CLIENT';
						
				} else {

					$this->addProject($values);
			
					//Take the old value to avoid nl character
					$values['details'] = $_POST['details'];

					$msgKey = 'PROJECT_ADDED';
						
				}
				

			$tpl->assign('values', $values);

		}
				
			$tpl->assign('values', $values);	
			$user = new users();
			$tpl->assign('availableUsers', $user->getAll());
			
				
			$clients = new clients();

			$tpl->assign('info', $msgKey);
			$tpl->assign('clients', $clients->getAll());

			$tpl->display('projects.newProject');


	}

}
?>