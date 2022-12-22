<?php

namespace leantime\domain\controllers {

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
            $this->userService = new services\users();
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
                    $values = array(
                        'firstname' => ($_POST['firstname']),
                        'lastname' => ($_POST['lastname']),
                        'user' => ($_POST['user']),
                        'phone' => ($_POST['phone']),
                        'role' => ($_POST['role']),
                        'password' => '',
                        'pwReset' => '',
                        'status' => '',
                        'clientId' => ($_POST['client'])
                    );

                    if ($values['user'] !== '') {
                        if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
                            if ($this->userRepo->usernameExist($values['user']) === false) {
                                $userId = $this->userService->createUserInvite($values);

                                //Update Project Relationships
                                if (isset($_POST['projects']) && count($_POST['projects']) > 0) {
                                    if ($_POST['projects'][0] !== '0') {
                                        $this->projectsRepo->editUserProjectRelations($userId, $_POST['projects']);
                                    } else {
                                        $this->projectsRepo->deleteAllProjectRelations($userId);
                                    }
                                }

                                $this->tpl->setNotification("notification.user_invited_successfully", 'success');


                                $this->tpl->redirect(BASE_URL . "/users/showAll");
                            } else {
                                $this->tpl->setNotification($this->language->__("notification.user_exists"), 'error');
                            }
                        } else {
                            $this->tpl->setNotification($this->language->__("notification.no_valid_email"), 'error');
                        }
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.enter_email"), 'error');
                    }
                }

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
