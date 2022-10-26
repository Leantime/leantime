<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showAll
    {


        public function get()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin], true);

            $tpl = new core\template();
            $userRepo =  new repositories\users();
            $ldapService = new services\ldap();

            //Only Admins
            if(auth::userIsAtLeast(roles::$admin)) {

                if(auth::userIsAtLeast(roles::$admin)) {
                    $tpl->assign('allUsers', $userRepo->getAll());

                }else{
                    $tpl->assign('allUsers', $userRepo->getAllClientUsers(auth::getUserClientId()));
                }

                $tpl->assign('admin', true);
                $tpl->assign('roles', roles::getRoles());

                $tpl->display('users.showAll');

            }else{

                $tpl->display('general.error');

            }

        }

        public function post($params) {

        }

    }

}
