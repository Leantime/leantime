<?php

namespace Leantime\Domain\Auth\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Setting\Repositories\Setting;
    use Leantime\Domain\Users\Services\Users as UserService;

    /**
     *
     */
    class UserInvite extends Controller
    {
        private $fileRepo;
        private AuthService $authService;
        private $usersService;
        private Setting $settingsRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(
            FileRepository $fileRepo,
            AuthService $authService,
            UserService $usersService,
            Setting $settingsRepo
        ) {
            $this->fileRepo = $fileRepo;
            $this->authService = $authService;
            $this->usersService = $usersService;
            $this->settingsRepo = $settingsRepo;
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

                if (!$user) {
                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                }

                $this->tpl->assign("user", $user);

                if (isset($_GET['step']) && is_numeric($_GET['step'])) {
                    return $this->tpl->display('auth.userInvite' . $_GET['step'], 'entry');
                }

                return $this->tpl->display('auth.userInvite', 'entry');
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

            $invitationId = $_GET["id"] ?? "";

            //Step 1
            if (isset($_POST["saveAccount"]) && isset($_POST["step"])) {
                $userInvite = $this->authService->getUserByInviteLink($invitationId);

                if (!isset($userInvite['id'])) {
                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                }

                if (strlen($_POST['password']) == 0 || $_POST['password'] != $_POST['password2']) {
                    $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), "error");
                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId);
                }

                if (!$this->usersService->checkPasswordStrength($_POST['password'])) {
                    $this->tpl->setNotification(
                        $this->language->__("notification.password_not_strong_enough"),
                        'error'
                    );

                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId);
                }


                $userInvite["firstname"] = $_POST["firstname"];
                $userInvite["lastname"] = $_POST["lastname"];
                $userInvite["jobTitle"] = $_POST["jobTitle"] ?? "";
                $userInvite["status"] = "I";
                $userInvite["user"] =  $userInvite["username"];
                $userInvite["password"] = $_POST['password'];
                $_SESSION['tempPassword'] = $_POST['password'];

                $editUser = $this->usersService->editUser($userInvite, $userInvite["id"]);

                if ($editUser) {
                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId . "?step=2");
                } else {
                    $this->tpl->setNotification(
                        $this->language->__('notifications.problem_updating_user'),
                        "error"
                    );
                }
            }


            if (isset($_POST["challenge"]) && isset($_POST["step"]) && $_POST["step"] == 2) {

                $userInvite = $this->authService->getUserByInviteLink($invitationId);

                $challenge = $_POST["challenge"];

                $this->settingsRepo->saveSetting("usersettings.".$userInvite['id'].".challenge", $challenge);

                return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId . "?step=3");

            }

            if (isset($_POST["function"]) && isset($_POST["step"]) && $_POST["step"] == 3) {

                $userInvite = $this->authService->getUserByInviteLink($invitationId);

                $function = $_POST["function"];

                $this->settingsRepo->saveSetting("usersettings.".$userInvite['id'].".function", $function);

                $userInvite["status"] = "A";
                $userInvite["password"] = "";
                $userInvite["user"] =  $userInvite["username"];

                 $result = $this->usersService->editUser($userInvite, $userInvite["id"]);


                $this->tpl->setNotification(
                    $this->language->__('notifications.you_are_active'),
                    "success",
                    "user_activated"
                );
                $loggedIn = $this->authService->login($userInvite["username"],  $_SESSION['tempPassword']);

                unset($_SESSION['tempPassword']);

                self::dispatch_event("userSignUpSuccess", ['user' => $userInvite]);

                if ($loggedIn) {
                    return FrontcontrollerCore::redirect(BASE_URL . "/dashboard/show");
                } else {
                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                }

            }


            /*
             * Login when we're ready
             *
             */
            return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId);
        }
    }
}
