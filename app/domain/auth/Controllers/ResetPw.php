<?php

namespace Leantime\Domain\Auth\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Users\Services\Users as UserService;
    class ResetPw extends Controller
    {
        private FileRepository $fileRepo;
        private AuthService $authService;
        private UserService $usersService;

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

            if ((isset($_GET["id"]) === true && $this->authService->validateResetLink($_GET["id"]))) {
                $this->tpl->display('auth.resetPw', 'entry');
            } else {
                $this->tpl->display('auth.requestPwLink', 'entry');
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

            if (isset($_POST["resetPassword"])) {
                if (isset($_POST['username']) === true) {
                    //Always return success to prevent db attacks checking which email address are in there
                    $this->authService->generateLinkAndSendEmail($_POST["username"]);
                    $this->tpl->setNotification($this->language->__('notifications.email_was_sent_to_reset'), "success");
                }

                if (isset($_POST['password']) === true && isset($_POST['password2']) === true) {
                    if (strlen($_POST['password']) == 0 || $_POST['password'] != $_POST['password2']) {
                        $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), "error");

                        FrontcontrollerCore::redirect(BASE_URL . "/auth/resetPw/" . $_GET['id']);
                    } else {
                        if ($this->usersService->checkPasswordStrength($_POST['password'])) {
                            if ($this->authService->changePW($_POST['password'], $_GET['id'])) {
                                $this->tpl->setNotification(
                                    $this->language->__('notifications.passwords_changed_successfully'),
                                    "success"
                                );

                                FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                            } else {
                                $this->tpl->setNotification(
                                    $this->language->__('notifications.problem_resetting_password'),
                                    "error"
                                );

                                FrontcontrollerCore::redirect(BASE_URL . "/auth/resetPw/" . $_GET['id']);
                            }
                        } else {
                            $this->tpl->setNotification(
                                $this->language->__("notification.password_not_strong_enough"),
                                'error'
                            );

                            FrontcontrollerCore::redirect(BASE_URL . "/auth/resetPw/" . $_GET['id']);
                        }
                    }
                }
            }

            FrontcontrollerCore::redirect(BASE_URL . "/auth/resetPw/");
        }
    }

}
