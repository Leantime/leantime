<?php

namespace leantime\domain\controllers {

	use leantime\core;
	use leantime\domain\repositories;

	class editUser
	{

		/**
		 * run - display template and edit data
		 *
		 * @access public
		 */
		public function run()
		{

			$tpl = new core\template();

			//Only admins
			if (core\login::userIsAtLeast("clientManager")) {

				if (isset($_GET['id']) === true) {

					$project = new repositories\projects();
					$userRepo = new repositories\users();
					$language = new core\language();

					$id = (int)($_GET['id']);
					$row = $userRepo->getUser($id);
					$edit = false;
					$infoKey = '';

					if (core\login::userHasRole("clientManager") && $row['clientId'] != core\login::getUserClientId()) {
						$tpl->display('general.error');
						exit();
					}


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
						'clientId' => $row['clientId']
					);

					if (isset($_POST['save'])) {

						if (isset($_POST[$_SESSION['formTokenName']]) && $_POST[$_SESSION['formTokenName']] == $_SESSION['formTokenValue']) {

							$values = array(
								'firstname' => ($_POST['firstname']),
								'lastname' => ($_POST['lastname']),
								'user' => ($_POST['user']),
								'phone' => ($_POST['phone']),
								'status' => ($_POST['status']),
								'role' => ($_POST['role']),
								'hours' => ($_POST['hours']),
								'wage' => ($_POST['wage']),
								'clientId' => ($_POST['client']),
								'password' => (password_hash($_POST['password'], PASSWORD_DEFAULT)),
							);

							$changedEmail = 0;

							if ($row['username'] != $values['user']) {
								$changedEmail = 1;
							}


							if ($values['user'] !== '') {
								if ($_POST['password'] == $_POST['password2']) {
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


					if (core\login::userIsAtLeast("manager")) {
						$tpl->assign('allProjects', $project->getAll());
						$tpl->assign('roles', core\login::$userRoles);
						$tpl->assign('clients', $clients->getAll());
					} else {
						$tpl->assign('allProjects', $project->getClientProjects($values['clientId']));
						$tpl->assign('roles', core\login::$clientManagerRoles);
						$tpl->assign('clients', array($clients->getClient($values['clientId'])));
					}


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
			} else {

				$tpl->display('general.error');

			}

		}

	}
}
