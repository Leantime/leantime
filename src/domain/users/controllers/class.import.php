<?php


/* Not production ready yet. Prepping for future version */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;
    use leantime\domain\models\auth\roles;

    class import
    {

        private $language;

        public function __construct() {
            $this->language = new core\language();
            if(!isset($_SESSION['tmp'])) $_SESSION['tmp'] = [];
        }

        public function get()
        {

            $tpl = new core\template();
            $userRepo =  new repositories\users();
            $ldapService = new services\ldap();

            //Only Admins
            if(auth::userIsAtLeast(roles::$admin)) {

                $tpl->assign('allUsers', $userRepo->getAll());
                $tpl->assign('admin', true);
                $tpl->assign('roles', roles::getRoles());

                if(isset($_SESSION['tmp']["ldapUsers"]) && count($_SESSION['tmp']["ldapUsers"]) > 0) {
                    $tpl->assign('allLdapUsers', $_SESSION['tmp']["ldapUsers"]);
                    $tpl->assign('confirmUsers', true);
                }

                $tpl->displayPartial('users.importLdapDialog');

            }else{

                $tpl->display('general.error');

            }

        }

        public function post($params) {

            $tpl = new core\template();
            $userRepo =  new repositories\users();
            $ldapService = new services\ldap();

            //Password Submit to connect to ldap and retrieve users. Sets tmp session var
            if(isset($params['pwSubmit'])) {

                $username = $ldapService->extractLdapFromUsername($_SESSION["userdata"]["mail"]);

                $ldapService->connect();

                if($ldapService->bind($username, $params['password'])) {

                    $_SESSION['tmp']["ldapUsers"] = $ldapService->getAllMembers();
                    $tpl->assign('allLdapUsers',  $_SESSION['tmp']["ldapUsers"]);
                    $tpl->assign('confirmUsers', true);

                }else{

                    $tpl->setNotification($this->language->__("notifications.username_or_password_incorrect"), "error");

                }

            }

            //Import/Update User Post
            if(isset($params['importSubmit'])) {

                if(is_array($params["users"])){

                    $users = array();
                    foreach($_SESSION['tmp']["ldapUsers"] as $user) {
                        if(array_search($user['username'], $params["users"])){
                            $users[] = $user;
                        }
                    }

                    $ldapService->upsertUsers($users);
                }

            }

            $tpl->displayPartial('users.importLdapDialog');

        }

    }

}
