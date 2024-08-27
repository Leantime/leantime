<?php

namespace Leantime\Domain\TwoFA\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Symfony\Component\HttpFoundation\Response;

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
            return $this->tpl->display("twofa.verify", "entry");
        }

        /**
         * @param $params
         * @return Response
         */
        public function post($params): Response
        {

            if (session()->exists("userdata") && $this->authService->use2FA()) {
                if (isset($params['twoFA_code']) === true) {
                    $redirectUrl = filter_var($params['redirectUrl'], FILTER_SANITIZE_URL);

                    if ($this->authService->verify2FA($params['twoFA_code'])) {
                        $this->authService->set2FAVerified();
                        return FrontcontrollerCore::redirect($redirectUrl);
                    } else {
                        $this->tpl->setNotification("notification.incorrect_twoFA_code", "error");
                        return FrontcontrollerCore::redirect(BASE_URL . "/twoFA/verify");
                    }
                } else {
                    $this->tpl->setNotification("notification.incorrect_twoFA_code", "error");
                    return FrontcontrollerCore::redirect(BASE_URL . "/twoFA/verify");
                }
            }

            /** @todo make a 400 response page **/
            return $this->tpl->display('error.400');
        }
    }
}
