<?php

namespace Leantime\Domain\TwoFA\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Services\Auth as AuthService;

    /**
     *
     */
    class Verify extends Controller
    {
        private AuthService $authService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(AuthService $authService)
        {
            $this->authService = $authService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {

            $redirectUrl = BASE_URL . "/dashboard/home";

            if (isset($_GET['redirect'])) {
                $redirectUrl = BASE_URL . urldecode($_GET['redirect']);
            }

            $this->tpl->assign("redirectUrl", $redirectUrl);
            $this->tpl->display("twofa.verify", "entry");
        }

        /**
         * @param $params
         * @return void
         */
        /**
         * @param $params
         * @return void
         */
        public function post($params): void
        {

            if (isset($_SESSION['userdata']) && $this->authService->use2FA()) {
                if (isset($params['twoFA_code']) === true) {
                    $redirectUrl = filter_var($params['redirectUrl'], FILTER_SANITIZE_URL);

                    if ($this->authService->verify2FA($params['twoFA_code'])) {
                        $this->authService->set2FAVerified();
                        FrontcontrollerCore::redirect($redirectUrl);
                    } else {
                        $this->tpl->setNotification("notification.incorrect_twoFA_code", "error");
                        FrontcontrollerCore::redirect(BASE_URL . "/twoFA/verify");
                    }
                } else {
                    $this->tpl->setNotification("notification.incorrect_twoFA_code", "error");
                    FrontcontrollerCore::redirect(BASE_URL . "/twoFA/verify");
                }
            }
        }
    }

}
