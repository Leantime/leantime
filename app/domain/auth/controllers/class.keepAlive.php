<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class keepAlive extends controller
    {
        private $authService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init()
        {
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

            $userId = $_SESSION['userdata']['id'];
            $sessionId = $this->authService->getSessionId();

            $return = $this->authService->updateUserSessionDB($userId, $sessionId);

            if($return){

                $this->tpl->displayJson("{'status':'ok'}");
            }else{
                $this->tpl->displayJson("{'status':'logout'}");
            }
        }
    }

}
