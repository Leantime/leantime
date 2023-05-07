<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;
    use \leantime\core\eventhelpers;

    class userInvite extends controller
    {
        private $fileRepo;
        private $authService;
        private $usersService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init()
        {

            $this->fileRepo = new repositories\files();

            $this->authService = services\auth::getInstance();
            $this->usersService = new services\users();
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
                    $this->tpl->display('auth.userInvite', 'entry');
                } else {
                    core\frontcontroller::redirect(BASE_URL . "/auth/login");
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

                        core\frontcontroller::redirect(BASE_URL . "/auth/userInvite/" . $_GET['id']);
                    } else {
                        if ($this->usersService->checkPasswordStrength($_POST['password'])) {
                            if (isset($userInvite['id'])) {
                                $user = $this->usersService->getUser($userInvite['id']);
                            } else {
                                core\frontcontroller::redirect(BASE_URL . "/auth/login");
                            }

                            $user["firstname"] = $_POST["firstname"];
                            $user["lastname"] = $_POST["lastname"];
                            $user["status"] = "A";
                            $user["user"] =  $user["username"];
                            $user["password"] = $_POST['password'];

                            if ($this->usersService->editUser($user, $user["id"])) {
                                $this->tpl->setNotification(
                                    $this->language->__('notifications.you_are_active'),
                                    "success"
                                );

                                self::dispatch_event("userSignupSuccess", ['user' => $user]);

                                core\frontcontroller::redirect(BASE_URL . "/auth/login");
                            } else {
                                $this->tpl->setNotification(
                                    $this->language->__('notifications.problem_updating_user'),
                                    "error"
                                );

                                core\frontcontroller::redirect(BASE_URL . "/auth/userInvite/" . $_GET['id']);
                            }
                        } else {
                            $this->tpl->setNotification(
                                $this->language->__("notification.password_not_strong_enough"),
                                'error'
                            );

                            core\frontcontroller::redirect(BASE_URL . "/auth/userInvite/" . $_GET['id']);
                        }
                    }
                }
            }

            core\frontcontroller::redirect(BASE_URL . "/auth/userInvite/");
        }
    }

}
