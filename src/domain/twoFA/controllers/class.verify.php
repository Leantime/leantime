<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class verify
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
            $this->authService = services\auth::getInstance();

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

            $this->tpl->assign("redirectUrl", $redirectUrl);
            $this->tpl->display("twoFA.verify", 200, "entry");
        }

        public function post($params) {

            if(isset($_SESSION['userdata']) && $this->authService->use2FA()) {

                if (isset($params['twoFA_code']) === true) {

                    $redirectUrl = filter_var($params['redirectUrl'], FILTER_SANITIZE_URL);

                    if($this->authService->verify2FA($params['twoFA_code'])){

                        $this->authService->set2FAVerified();
                        core\frontcontroller::redirect($redirectUrl);

                    } else {

                        $this->tpl->setNotification("notification.incorrect_twoFA_code", "error");
                        core\frontcontroller::redirect(BASE_URL."/twoFA/verify");

                    }

                }else {

                    $this->tpl->setNotification("notification.incorrect_twoFA_code", "error");
                    core\frontcontroller::redirect(BASE_URL."/twoFA/verify");

                }

            }

        }

    }

}
