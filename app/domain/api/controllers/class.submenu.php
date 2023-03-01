<?php

  /**
   * AJAX class - Save menu state in a persistent way
   */

namespace leantime\domain\controllers {

    use leantime\domain\repositories;
    use leantime\core\controller;

    class submenu extends controller
    {
        private $menu;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init()
        {

            $this->menu = new repositories\menu();
        }


        /**
         * get - handle get requests
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

            $this->menu->setSubmenuState($params['submenu'], $params['state']);
            echo "{status:ok}";
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
