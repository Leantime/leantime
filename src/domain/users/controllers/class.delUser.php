<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delUser
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
            if ($_SESSION['userdata']['role'] == 'admin') {

                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    $user = $userRepo->getUser($id);

                    $msgKey = '';

                    //Delete User
                    if (isset($_POST['del']) === true) {

                        $userRepo->deleteUser($id);

                        $msgKey = 'USER_DELETED';

                        header("Location:".BASE_URL."/users/showAll");

                    }

                    //Assign variables
                    $tpl->assign('msg', $msgKey);
                    $tpl->assign('user', $user);

                    $tpl->display('users.delUser');

                } else {

                    $tpl->display('general.error');

                }

            } else {

                $tpl->display('general.error');

            }

        }

    }
}
