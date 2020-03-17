<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\billing;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showAll
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $userRepo =  new repositories\users();

            //Only Admins
            if(core\login::userIsAtLeast("clientManager")) {

                //Assign vars

                if(core\login::userIsAtLeast("manager")) {
                    $tpl->assign('allUsers', $userRepo->getAll());
                }else{
                    $tpl->assign('allUsers', $userRepo->getAllClientUsers(core\login::getUserClientId()));
                }

                $tpl->assign('admin', true);
                $tpl->assign('roles', core\login::$userRoles);

                $tpl->display('users.showAll');

            }else{

                $tpl->display('general.error');

            }

        }

    }

}
