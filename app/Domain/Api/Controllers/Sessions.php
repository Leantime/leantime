<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
    use Leantime\Domain\Users\Services\Users as UserService;

    /**
     *
     */
    class Sessions extends Controller
    {
        private UserService $usersService;
        private MenuRepository $menu;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(UserService $usersService, MenuRepository $menu)
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
         * put - Special handling for settings
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            if (isset($params['tourActive'])) {
                $_SESSION['tourActive'] = filter_var($params['tourActive'], FILTER_SANITIZE_NUMBER_INT);
                return $this->tpl->displayJson(['status' => 'ok']);
            }

            if (isset($params['menuState'])) {
                $_SESSION['menuState'] = htmlentities($params['menuState']);
                $this->menu->setSubmenuState("mainMenu", $params['menuState']);
                return $this->tpl->displayJson(['status' => 'ok']);
            }

            return $this->tpl->displayJson(['status' => 'failure'], 400);
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
