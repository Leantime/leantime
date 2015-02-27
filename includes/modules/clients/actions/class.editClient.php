<?php

/**
 * editClient Class - Editing clients
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class editClient extends clients{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();

		//Only admins
		if($_SESSION['userdata']['role'] == 'admin'){

			if(isset($_GET['id']) === true){

				$id = (int)($_GET['id']);

				$row = $this->getClient($id);
					
				$msgKey = '';

				$values = array(
					'name'		=>$row['name'],
					'street'	=>$row['street'],
					'zip'		=>$row['zip'],
					'city'		=>$row['city'],
					'state'		=>$row['state'],
					'country'	=>$row['country'],
					'phone'		=>$row['phone'],
					'internet'	=>$row['internet'],
					'email'		=>$row['email']
				);
					
				if(isset($_POST['save']) === true){

					$values = array(
						'name'		=>$_POST['name'],
						'street'	=>$_POST['street'],
						'zip'		=>$_POST['zip'],
						'city'		=>$_POST['city'],
						'state'		=>$_POST['state'],
						'country'	=>$_POST['country'],
						'phone'		=>$_POST['phone'],
						'internet'	=>$_POST['internet'],
						'email'		=>$_POST['email']
					);

					if ($values['name'] !== '') {

						$this->editClient($values, $id);

						$tpl->setNotification('EDIT_CLIENT_SUCCESS', 'success');
					} else {

						$tpl->setNotification('NO_NAME', 'error');
					}	
				}

				$tpl->assign('values', $values);

				$tpl->display('clients.editClient');

					
			}else{
					
				$tpl->display('general.error');
					
			}

		}else{

			$tpl->display('general.error');

		}

	}

}
?>