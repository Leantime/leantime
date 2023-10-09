<?php

/**
 * AJAX class - Save menu state in a persistent way
 */

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
    use Leantime\Core\Controller;

    /**
     *
     */
    class Submenu extends Controller
    {
        private MenuRepository $menu;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(MenuRepository $menu)
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

            if (isset($params['submenu']) && isset($params['state'])) {
                $this->menu->setSubmenuState($params['submenu'], $params['state']);
                echo "{status:ok}";
            } else {
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
