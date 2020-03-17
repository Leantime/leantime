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
            $language = new core\language();

            //Only Admins
            if(core\login::userIsAtLeast("clientManager")) {

                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    $user = $userRepo->getUser($id);

                    //Delete User
                    if (isset($_POST['del']) === true) {

                        $userRepo->deleteUser($id);

                        $tpl->setNotification($language->__("notifications.user_deleted"), "success");

                        $tpl->redirect(BASE_URL."/users/showAll");

                    }

                    //Assign variables
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
