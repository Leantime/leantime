<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class showUser
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

            if (isset($_GET['id'])) {

                $id = ((int)$_GET['id']);

                $user = $userRepo->getUser($id);

                //Assign vars
                $tpl->assign('user', $userRepo->getUser($_GET['id']));
                $tpl->assign('roles', $userRepo->getRole($_SESSION['userdata']['role']));
                $tpl->assign('user', $user);

                $tpl->display('users.showUser');

            } else {

                $tpl->display('general.error');

            }

        }

    }
}
