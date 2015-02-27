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

class newClient extends clients{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();
		$user = new users();

		//Only admins
		if ($user->isAdmin($_SESSION['userdata']['id'])) {

			$msgKey = '';

			if (isset($_POST['save']) === true){

				$values = array(
					'name'		=>($_POST['name']),
					'street'	=>($_POST['street']),
					'zip'		=>($_POST['zip']),
					'city'		=>($_POST['city']),
					'state'		=>($_POST['state']),
					'country'	=>($_POST['country']),
					'phone'		=>($_POST['phone']),
					'internet'	=>($_POST['internet']),
					'email'		=>($_POST['email'])
				);

				if ($values['name'] !== '') {
					if ($this->isClient($values) !== true) {
						
						$this->addClient($values);
						$tpl->setNotification('ADD_CLIENT_SUCCESS', 'success');
					} else {
						
						$tpl->setNotification('CLIENT_EXISTS', 'error');
					}
				} else {

					$tpl->setNotification('NO_NAME', 'error');
				}

				$tpl->assign('values', $values);
			}


			$tpl->display('clients.newClient');
		} else {

			$tpl->display('general.error');

		}

	}

}
?>