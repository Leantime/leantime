<?php

namespace leantime\domain\controllers {

	use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
	use leantime\domain\services;
    use leantime\domain\services\auth;

    class newUser extends controller
	{

        private $userRepo;
        private $projectsRepo;

		/**
		 * init - initialize private variables
		 *
		 * @access public
		 */
		public function init()
		{

			$this->userRepo = new repositories\users();
			$this->projectsRepo = new repositories\projects();

        }

		/**
		 * run - display template and edit data
		 *
		 * @access public
		 */
		public function run()
		{

            auth::authOrRedirect([roles::$owner, roles::$admin], true);

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
			if (auth::userIsAtLeast(roles::$admin)) {

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


					if ($values['user'] !== '') {
						if ($_POST['password'] == $_POST['password2']) {
							if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
								if (password_verify($_POST['password'], $values['password']) && $_POST['password'] != '') {
									if ($this->userRepo->usernameExist($values['user']) === false) {

										$userId = $this->userRepo->addUser($values);

										//Update Project Relationships
										if (isset($_POST['projects'])) {
											if ($_POST['projects'][0] !== '0') {
												$this->projectsRepo->editUserProjectRelations($userId, $_POST['projects']);
											} else {
												$this->projectsRepo->deleteAllProjectRelations($userId);
											}
										}

										$mailer = new core\mailer();
										$mailer->setContext('new_user');

										$mailer->setSubject($this->language->__("email_notifications.new_user_subject"));
										$actual_link = BASE_URL;

										$message = sprintf($this->language->__("email_notifications.new_user_message"), $_SESSION["userdata"]["name"], $actual_link, $values["user"], $tempPasswordVar);
										$mailer->setHtml($message);

										$to = array($values["user"]);

										$mailer->sendMail($to, $_SESSION["userdata"]["name"]);

										$this->tpl->setNotification($this->language->__("notification.user_created"), 'success');

										$this->tpl->redirect(BASE_URL . "/users/showAll");

									} else {

										$this->tpl->setNotification($this->language->__("notification.user_exists"), 'error');

									}
								} else {

									$this->tpl->setNotification($this->language->__("notification.passwords_dont_match"), 'error');
								}
							} else {

								$this->tpl->setNotification($this->language->__("notification.no_valid_email"), 'error');
							}
						} else {


							$this->tpl->setNotification($this->language->__("notification.passwords_dont_match"), 'error');

						}
					} else {

						$this->tpl->setNotification($this->language->__("notification.enter_email"), 'error');
					}
				}
				//exit();

				$this->tpl->assign('values', $values);
				$clients = new repositories\clients();


				$this->tpl->assign('clients', $clients->getAll());
				$this->tpl->assign('allProjects', $this->projectsRepo->getAll());
				$this->tpl->assign('roles', roles::getRoles());

				$this->tpl->assign('relations', $projectrelation);


				$this->tpl->display('users.newUser');

			} else {

				$this->tpl->display('errors.error403');

			}

		}

	}

}
