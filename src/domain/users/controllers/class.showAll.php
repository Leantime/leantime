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
            if($_SESSION['userdata']['role'] == 'admin') {

                //Assign vars
                $tpl->assign('allUsers', $userRepo->getAll());
                $tpl->assign('admin', true);
                $tpl->assign('roles', $userRepo->getRoles());

                $tpl->display('users.showAll');

            }else{

                $tpl->display('general.error');

            }

        }

    }

}
