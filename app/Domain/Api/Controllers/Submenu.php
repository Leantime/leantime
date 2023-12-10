<?php

/**
 * AJAX class - Save menu state in a persistent way
 */

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;

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
            if (! isset($params['submenu'], $params['state'])) {
                return $this->tpl->displayJson(['status' => false], 500);
            }

            $this->menu->setSubmenuState($params['submenu'], $params['state']);
            return $this->tpl->displayJson(['status' => 'ok']);
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
