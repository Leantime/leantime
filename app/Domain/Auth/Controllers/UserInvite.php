<?php

namespace Leantime\Domain\Auth\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Users\Services\Users as UserService;

    /**
     *
     */
    class UserInvite extends Controller
    {
        private $fileRepo;
        private AuthService $authService;
        private $usersService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(
            FileRepository $fileRepo,
            AuthService $authService,
            UserService $usersService
        ) {
            $this->fileRepo = $fileRepo;
            $this->authService = $authService;
            $this->usersService = $usersService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {


            if (isset($_GET["id"]) === true) {
                $user = $this->authService->getUserByInviteLink($_GET["id"]);

                if ($user) {
                    $this->tpl->assign("user", $user);
                    return $this->tpl->display('auth.userInvite', 'entry');
                } else {
                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                }
            }
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {

            if (isset($_POST["saveAccount"])) {
                $userInvite = $this->authService->getUserByInviteLink($_GET["id"]);

                if (isset($_POST['password']) === true && isset($_POST['password2']) === true) {
                    if (strlen($_POST['password']) == 0 || $_POST['password'] != $_POST['password2']) {
                        $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), "error");

                        return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $_GET['id']);
                    } else {
                        if ($this->usersService->checkPasswordStrength($_POST['password'])) {
                            if (isset($userInvite['id'])) {
                                $user = $this->usersService->getUser($userInvite['id']);
                            } else {
                                return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                            }

                            $user["firstname"] = $_POST["firstname"];
                            $user["lastname"] = $_POST["lastname"];
                            $user["jobTitle"] = $_POST["jobTitle"] ?? "";
                            $user["status"] = "A";
                            $user["user"] =  $user["username"];
                            $user["password"] = $_POST['password'];

                            $editUser = $this->usersService->editUser($user, $user["id"]);

                            if ($editUser) {
                                $this->tpl->setNotification(
                                    $this->language->__('notifications.you_are_active'),
                                    "success",
                                    "user_activated"
                                );
                                $loggedIn = $this->authService->login($user["username"], $_POST['password']);

                                self::dispatch_event("userSignUpSuccess", ['user' => $user]);

                                if ($loggedIn) {
                                    return FrontcontrollerCore::redirect(BASE_URL . "/dashboard/show");
                                } else {
                                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                                }
                            } else {
                                $this->tpl->setNotification(
                                    $this->language->__('notifications.problem_updating_user'),
                                    "error"
                                );

                                return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $_GET['id']);
                            }
                        } else {
                            $this->tpl->setNotification(
                                $this->language->__("notification.password_not_strong_enough"),
                                'error'
                            );

                            return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $_GET['id']);
                        }
                    }
                }
            }

            return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/");
        }
    }
}
