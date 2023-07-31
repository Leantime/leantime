<?php

  /**
   * AJAX class - Save menu state in a persistent way
   */

namespace leantime\domain\controllers {

    use leantime\domain\repositories;
    use leantime\core\controller;

    class submenu extends controller
    {
        private repositories\menu $menu;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(repositories\menu $menu)
        {
            $this->menu = $menu;
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

            if(isset($params['submenu']) && isset($params['state'])) {
                $this->menu->setSubmenuState($params['submenu'], $params['state']);
                echo "{status:ok}";
            }else{
                echo "{'status':false}";
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
