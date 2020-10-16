<?php

namespace leantime\domain\controllers {

	use leantime\core;
	use leantime\domain\repositories;
	use leantime\domain\services;

	class newUser
	{

		/**
		 * run - display template and edit data
		 *
		 * @access public
		 */
		public function run()
		{

			$tpl = new core\template();
			$userRepo = new repositories\users();
			$project = new repositories\projects();
			$language = new core\language();

			$values = array(
				'firstname' => "",
				'lastname' => "",
				'user' => "",
				'phone' => "",
				'role' => "",
				'password' => "",
				'clientId' => ""
			);

			//only Admins
			if (core\login::userIsAtLeast("clientManager")) {

				$projectrelation = array();

				if (isset($_POST['save'])) {

					$tempPasswordVar = $_POST['password'];
					$values = array(
						'firstname' => ($_POST['firstname']),
						'lastname' => ($_POST['lastname']),
						'user' => ($_POST['user']),
						'phone' => ($_POST['phone']),
						'role' => ($_POST['role']),
						'password' => (password_hash($_POST['password'], PASSWORD_DEFAULT)),
						'clientId' => ($_POST['client'])
					);

					//Choice is an illusion for client managers
					if (core\login::userHasRole("clientManager")) {
						$values['clientId'] = core\login::getUserClientId();
					}

					//Validation
					print_r($_POST);
					# exit();

					if ($values['user'] !== '') {
						if ($_POST['password'] == $_POST['password2']) {
							if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
								if (password_verify($_POST['password'], $values['password']) && $_POST['password'] != '') {
									if ($userRepo->usernameExist($values['user']) === false) {

										$userId = $userRepo->addUser($values);

										//Update Project Relationships
										if (isset($_POST['projects'])) {
											if ($_POST['projects'][0] !== '0') {
												$project->editUserProjectRelations($userId, $_POST['projects']);
											} else {
												$project->deleteAllProjectRelations($userId);
											}
										}

										$mailer = new core\mailer();

										$mailer->setSubject($language->__("email_notifications.new_user_subject"));
										$actual_link = BASE_URL;

										$message = sprintf($language->__("email_notifications.new_user_message"), $_SESSION["userdata"]["name"], $actual_link, $values["user"], $tempPasswordVar);
										$mailer->setHtml($message);

										$to = array($values["user"]);

										$mailer->sendMail($to, $_SESSION["userdata"]["name"]);

										$tpl->setNotification($language->__("notification.user_created"), 'success');

										$tpl->redirect(BASE_URL . "/users/showAll");

									} else {

										$tpl->setNotification($language->__("notification.user_exists"), 'error');

									}
								} else {

									$tpl->setNotification($language->__("notification.passwords_dont_match"), 'error');
								}
							} else {

								$tpl->setNotification($language->__("notification.no_valid_email"), 'error');
							}
						} else {


							$tpl->setNotification($language->__("notification.passwords_dont_match"), 'error');

						}
					} else {

						$tpl->setNotification($language->__("notification.enter_email"), 'error');
					}
				}
				//exit();

				$tpl->assign('values', $values);
				$clients = new repositories\clients();

				if (core\login::userIsAtLeast("manager")) {
					$tpl->assign('clients', $clients->getAll());
					$tpl->assign('allProjects', $project->getAll());
					$tpl->assign('roles', core\login::$userRoles);
				} else {

					$tpl->assign('clients', array($clients->getClient(core\login::getUserClientId())));
					$tpl->assign('allProjects', $project->getClientProjects(core\login::getUserClientId()));
					$tpl->assign('roles', core\login::$clientManagerRoles);
				}
				$tpl->assign('relations', $projectrelation);


				$tpl->display('users.newUser');

			} else {

				$tpl->display('general.error');

			}

		}

	}

}
