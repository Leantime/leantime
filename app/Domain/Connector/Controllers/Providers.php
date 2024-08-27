<?php

namespace Leantime\Domain\Connector\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class Providers extends Controller
    {
        /**
         * constructor - initialize private variables
         *
         * @access public
         *
         */
        public function init()
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {
            return $this->tpl->displayPartial('connectors.providers');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            return $this->tpl->displayPartial('connectors.providers');
        }
    }

}
