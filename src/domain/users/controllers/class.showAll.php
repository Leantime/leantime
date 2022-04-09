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
            if(core\login::userIsAtLeast("clientManager")) {

                if(core\login::userIsAtLeast("manager")) {
                    $tpl->assign('allUsers', $userRepo->getAll());

                }else{
                    $tpl->assign('allUsers', $userRepo->getAllClientUsers(core\login::getUserClientId()));
                }

                $tpl->assign('admin', true);
                $tpl->assign('roles', core\login::$userRoles);

                if($ldapService->connect()) {
                    $ldapService->getAllMembers($_SESSION['userdata']['mail'], '');
                }

                $tpl->display('users.showAll');

            }else{

                $tpl->display('general.error');

            }

        }

        public function post($params) {

        }

    }

}
