<?php

namespace Leantime\Domain\Users\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Core\Frontcontroller;

    /**
     *
     */
    class DelUser extends Controller
    {
        private UserRepository $userRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(UserRepository $userRepo)
        {
            $this->userRepo = $userRepo;
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

                $user = $this->userRepo->getUser($id);

                //Delete User
                if (isset($_POST['del']) === true) {
                    if (isset($_POST[$_SESSION['formTokenName']]) && $_POST[$_SESSION['formTokenName']] == $_SESSION['formTokenValue']) {
                        $this->userRepo->deleteUser($id);

                        $this->tpl->setNotification($this->language->__("notifications.user_deleted"), "success", "user_deleted");

                        return Frontcontroller::redirect(BASE_URL . "/users/showAll");
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.form_token_incorrect"), 'error');
                    }
                }

                //Sensitive Form, generate form tokens
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                $_SESSION['formTokenName'] = substr(str_shuffle($permitted_chars), 0, 32);
                $_SESSION['formTokenValue'] = substr(str_shuffle($permitted_chars), 0, 32);

                //Assign variables
                $this->tpl->assign('user', $user);

                return $this->tpl->display('users.delUser');
            } else {
                return $this->tpl->display('errors.error403');
            }
        }
    }
}
