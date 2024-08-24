<?php

namespace Leantime\Domain\Users\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Users\Services\Users;

    /**
     *
     */
    class DelUser extends Controller
    {
        private Users $userService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(Users $userService)
        {
            $this->userService = $userService;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            //Only Admins
            if (isset($_GET['id']) === true) {
                $id = (int)($_GET['id']);

                $user = $this->userService->getUser($id);

                //Delete User
                if (isset($_POST['del']) === true) {
                    if (isset($_POST[session("formTokenName")]) && $_POST[session("formTokenName")] == session("formTokenValue")) {
                        $this->userService->deleteUser($id);

                        $this->tpl->setNotification($this->language->__("notifications.user_deleted"), "success", "user_deleted");

                        return Frontcontroller::redirect(BASE_URL . "/users/showAll");
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.form_token_incorrect"), 'error');
                    }
                }

                //Sensitive Form, generate form tokens
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                session(["formTokenName" => substr(str_shuffle($permitted_chars), 0, 32)]);
                session(["formTokenValue" => substr(str_shuffle($permitted_chars), 0, 32)]);

                //Assign variables
                $this->tpl->assign('user', $user);

                return $this->tpl->display('users.delUser');
            } else {
                return $this->tpl->display('errors.error403');
            }
        }
    }
}
