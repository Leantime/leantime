<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class sessions extends controller
    {
        private services\users $usersService;
        private repositories\menu $menu;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(services\users $usersService, repositories\menu $menu)
        {
            $this->usersService = $usersService;
            $this->menu = $menu;
        }

        /**
         *
         *
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {
        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            //Special handling for settings

            if (isset($params['tourActive'])) {
                $_SESSION['tourActive'] = filter_var($params['tourActive'], FILTER_SANITIZE_NUMBER_INT);
            }

            if (isset($params['menuState'])) {
                $_SESSION['menuState'] = htmlentities($params['menuState']);
                $this->menu->setSubmenuState("mainMenu", $params['menuState']);
            }
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function delete($params)
        {
        }
    }

}
