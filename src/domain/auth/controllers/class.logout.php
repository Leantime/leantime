<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class logout extends controller
    {

        private $fileRepo;
        private $authService;

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

        }


        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {

            $this->authService->logout();

            core\frontcontroller::redirect(BASE_URL."/");

        }

    }

}
