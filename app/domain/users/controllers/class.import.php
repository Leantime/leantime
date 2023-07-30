<?php

/* Not production ready yet. Prepping for future version */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;
    use leantime\domain\models\auth\roles;

    class import extends controller
    {
        private repositories\users $userRepo;
        private services\ldap $ldapService;

        public function init(repositories\users $userRepo, services\ldap $ldapService)
        {
            $this->userRepo = $userRepo;
            $this->ldapService = $ldapService;

            if (!isset($_SESSION['tmp'])) {
                $_SESSION['tmp'] = [];
            }
        }

        public function get()
        {

            //Only Admins
            if (auth::userIsAtLeast(roles::$admin)) {
                $this->tpl->assign('allUsers', $this->userRepo->getAll());
                $this->tpl->assign('admin', true);
                $this->tpl->assign('roles', roles::getRoles());

                if (isset($_SESSION['tmp']["ldapUsers"]) && count($_SESSION['tmp']["ldapUsers"]) > 0) {
                    $this->tpl->assign('allLdapUsers', $_SESSION['tmp']["ldapUsers"]);
                    $this->tpl->assign('confirmUsers', true);
                }

                $this->tpl->displayPartial('users.importLdapDialog');
            } else {
                $this->tpl->display('errors.error403');
            }
        }

        public function post($params)
        {
            $this->tpl = app()->make(core\template::class);
            $this->ldapService = app()->make(services\ldap::class);

            //Password Submit to connect to ldap and retrieve users. Sets tmp session var
            if (isset($params['pwSubmit'])) {
                $username = $this->ldapService->extractLdapFromUsername($_SESSION["userdata"]["mail"]);

                $this->ldapService->connect();

                if ($this->ldapService->bind($username, $params['password'])) {
                    $_SESSION['tmp']["ldapUsers"] = $this->ldapService->getAllMembers();
                    $this->tpl->assign('allLdapUsers', $_SESSION['tmp']["ldapUsers"]);
                    $this->tpl->assign('confirmUsers', true);
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.username_or_password_incorrect"), "error");
                }
            }

            //Import/Update User Post
            if (isset($params['importSubmit'])) {
                if (is_array($params["users"])) {
                    $users = array();
                    foreach ($_SESSION['tmp']["ldapUsers"] as $user) {
                        if (array_search($user['username'], $params["users"])) {
                            $users[] = $user;
                        }
                    }

                    $this->ldapService->upsertUsers($users);
                }
            }

            $this->tpl->displayPartial('users.importLdapDialog');
        }
    }
}
