<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showAll
    {


        public function get()
        {

            $tpl = new core\template();
            $userRepo =  new repositories\users();
            $ldapService = new services\ldap();

            //Only Admins
            if(services\auth::userIsAtLeast("clientManager")) {

                if(services\auth::userIsAtLeast("manager")) {
                    $tpl->assign('allUsers', $userRepo->getAll());

                }else{
                    $tpl->assign('allUsers', $userRepo->getAllClientUsers(services\auth::getUserClientId()));
                }

                $tpl->assign('admin', true);
                $tpl->assign('roles', services\auth::$userRoles);

                $tpl->display('users.showAll');

            }else{

                $tpl->display('general.error');

            }

        }

        public function post($params) {

        }

    }

}
