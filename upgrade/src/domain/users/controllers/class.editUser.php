<?php

namespace leantime\domain\controllers {

	use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class editUser
	{

		/**
		 * run - display template and edit data
		 *
		 * @access public
		 */
		public function run()
		{

            auth::authOrRedirect([roles::$owner, roles::$admin], true);

            $tpl = new core\template();

			//Only admins


				if (isset($_GET['id']) === true) {

					$project = new repositories\projects();
					$userRepo = new repositories\users();
					$language = new core\language();

					$id = (int)($_GET['id']);
					$row = $userRepo->getUser($id);
					$edit = false;
					$infoKey = '';



					//Build values array
					$values = array(
						'firstname' => $row['firstname'],
						'lastname' => $row['lastname'],
						'user' => $row['username'],
						'phone' => $row['phone'],
						'status' => $row['status'],
						'role' => $row['role'],
						'hours' => $row['hours'],
						'wage' => $row['wage'],
						'clientId' => $row['clientId'],
                        'source' =>  $row['source']
					);

					if (isset($_POST['save'])) {

						if (isset($_POST[$_SESSION['formTokenName']]) && $_POST[$_SESSION['formTokenName']] == $_SESSION['formTokenValue']) {

							$values = array(
								'firstname' => ($_POST['firstname'] ?? $row['firstname']),
								'lastname' => ($_POST['lastname'] ??  $row['lastname']),
								'user' => ($_POST['user'] ?? $row['username']),
								'phone' => ($_POST['phone'] ??  $row['phone']),
								'status' => ($_POST['status'] ?? $row['status']),
								'role' => ($_POST['role'] ?? $row['role']),
								'hours' => ($_POST['hours'] ?? $row['hours']),
								'wage' => ($_POST['wage'] ?? $row['wage']),
								'clientId' => ($_POST['client'] ?? $row['clientId']),
								'password' => ($row['password']),
                                'source' =>  $row['source']
							);

							$changedEmail = 0;

							if ($row['username'] != $values['user']) {
								$changedEmail = 1;
							}

							if (isset($_POST['password']) && $_POST['password'] != '' && $_POST['password'] == $_POST['password2']) {
								$values['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
							}

							if ($values['user'] !== '') {
								if ( !isset($_POST['password']) || ($_POST['password'] == $_POST['password2'])) {
									if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
										if ($changedEmail == 1) {
											if ($userRepo->usernameExist($row['username'], $id) === false) {
												if (password_verify($_POST['password'], $values['password']) && $_POST['password'] != '') {
													$edit = true;
												} else {
													$tpl->setNotification($language->__("notification.passwords_dont_match"), 'error');
												}
											} else {

												$tpl->setNotification($language->__("notification.user_exists"), 'error');
											}
										} else {

											$edit = true;
										}
									} else {

										$tpl->setNotification($language->__("notification.no_valid_email"), 'error');
									}
								} else {

									$tpl->setNotification($language->__("notification.enter_email"), 'error');
								}

							} else {


								$tpl->setNotification($language->__("notification.passwords_dont_match"), 'error');

							}
						} else {
							$tpl->setNotification($language->__("notification.form_token_incorrect"), 'error');
						}
					}

					//Was everything okay?
					if ($edit !== false) {

						$userRepo->editUser($values, $id);

						if (isset($_POST['projects'])) {
							if ($_POST['projects'][0] !== '0') {
								$project->editUserProjectRelations($id, $_POST['projects']);
							} else {
								$project->deleteAllProjectRelations($id);
							}
						} else {
							//If projects is not set, all project assignments have been removed.
							$project->deleteAllProjectRelations($id);
						}
						$tpl->setNotification($language->__("notifications.user_edited"), 'success');
					}

					// Get relations to projects
					$projects = $project->getUserProjectRelation($id);

					$projectrelation = array();

					foreach ($projects as $projectId) {
						$projectrelation[] = $projectId['projectId'];
					}

					//Assign vars
					$clients = new repositories\clients();

					$tpl->assign('allProjects', $project->getAll());
					$tpl->assign('roles', roles::getRoles());
					$tpl->assign('clients', $clients->getAll());



					//Sensitive Form, generate form tokens
					$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
					$_SESSION['formTokenName'] = substr(str_shuffle($permitted_chars), 0, 32);
					$_SESSION['formTokenValue'] = substr(str_shuffle($permitted_chars), 0, 32);

					$tpl->assign('values', $values);
					$tpl->assign('relations', $projectrelation);

					$tpl->assign('status', $userRepo->status);
					$tpl->assign('id', $id);


					$tpl->display('users.editUser');
				} else {

					$tpl->display('general.error');
				}


		}

	}
}
