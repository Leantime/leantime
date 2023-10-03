<?php

namespace Leantime\Domain\Auth\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Services\Auth as AuthService;

    /**
     *
     */
    class KeepAlive extends Controller
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

            $userId = $_SESSION['userdata']['id'];
            $sessionId = $this->authService->getSessionId();

            $return = $this->authService->updateUserSessionDB($userId, $sessionId);

            if ($return) {
                $this->tpl->displayJson("{'status':'ok'}");
            } else {
                $this->tpl->displayJson("{'status':'logout'}");
            }
        }
    }

}
