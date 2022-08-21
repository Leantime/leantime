<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class resetPw
    {

        private $tpl;
        private $usersService;
        private $redirectUrl;


        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->fileRepo = new repositories\files();

            $this->authService = services\auth::getInstance();
            $this->language = new core\language();

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
            if((isset($_GET["id"]) === true && $this->authService->validateResetLink($_GET["id"]))) {

                $this->tpl->display('auth.resetPw', 200, 'entry');
            }else{
                $this->tpl->display('auth.requestPwLink', 200, 'entry');
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

            if(isset($_POST["resetPassword"])) {

                if(isset($_POST['username']) === true) {

                    //Always return success to prevent db attacks checking which email address are in there
                   $this->authService->generateLinkAndSendEmail($_POST["username"]);
                   $this->tpl->setNotification($this->language->__('notifications.email_was_sent_to_reset'), "success");

                }

                if(isset($_POST['password']) === true && isset($_POST['password2']) === true) {

                    if(strlen($_POST['password']) == 0 || $_POST['password'] != $_POST['password2']) {

                        $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), "error");

                    }else{

                        if($this->authService->changePW($_POST['password'], $_GET['id'])){
                            $this->tpl->setNotification($this->language->__('notifications.passwords_changed_successfully'), "success");
                        }else{
                            $this->tpl->setNotification($this->language->__('notifications.problem_resetting_password'), "error");

                        }

                    }

                }
            }

            core\frontcontroller::redirect(BASE_URL."/auth/resetPw");
        }
    }

}
