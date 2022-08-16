<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delUser
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin], true);

            $tpl = new core\template();
            $userRepo =  new repositories\users();
            $language = new core\language();

            //Only Admins
                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    $user = $userRepo->getUser($id);

                    //Delete User
                    if (isset($_POST['del']) === true) {

                        if(isset($_POST[$_SESSION['formTokenName']]) && $_POST[$_SESSION['formTokenName']] == $_SESSION['formTokenValue']) {

                            $userRepo->deleteUser($id);

                            $tpl->setNotification($language->__("notifications.user_deleted"), "success");

                            $tpl->redirect(BASE_URL."/users/showAll");

                        }else{
                            $tpl->setNotification($language->__("notification.form_token_incorrect"), 'error');
                        }

                    }

                    //Sensitive Form, generate form tokens
                    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                    $_SESSION['formTokenName'] = substr(str_shuffle($permitted_chars), 0, 32);
                    $_SESSION['formTokenValue'] = substr(str_shuffle($permitted_chars), 0, 32);

                    //Assign variables
                    $tpl->assign('user', $user);

                    $tpl->display('users.delUser');

                } else {

                    $tpl->display('general.error');

                }

        }

    }
}
