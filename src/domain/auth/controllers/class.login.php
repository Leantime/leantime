<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class login
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

            $this->redirectUrl = BASE_URL."/dashboard/show";

            if($_SERVER['REQUEST_URI'] != '' && isset($_GET['logout']) === false) {
                $this->redirectUrl = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
            }

        }


        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
            $redirectUrl = BASE_URL."/dashboard/show";

            if(isset($_SESSION['redirectOrigin'])){
                $redirectUrl = $_SESSION['redirectOrigin'];
            }

            $this->tpl->assign('redirectUrl', urlencode($redirectUrl));
            $this->tpl->display('auth.login', 200, 'entry');
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {
            if (isset($_POST['username'])===true && isset($_POST['password'])===true) {

                $redirectUrl = urldecode(filter_var($_POST['redirectUrl'], FILTER_SANITIZE_URL));
                $username = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'];

                //If login successful redirect to the correct url to avoid post on reload
                if($this->authService->login($username, $password) === true){

                    if($this->authService->use2FA()) {
                        core\frontcontroller::redirect(BASE_URL."/auth/twoFA");
                    }

                   core\frontcontroller::redirect($redirectUrl);

                }else{
                    $this->tpl->setNotification("notifications.username_or_password_incorrect", "error");
                    core\frontcontroller::redirect(BASE_URL."/auth/login");
                }

            }else {

                $this->tpl->setNotification("notifications.username_or_password_missing", "error");
                core\frontcontroller::redirect(BASE_URL."/auth/login");

            }

        }

    }

}
