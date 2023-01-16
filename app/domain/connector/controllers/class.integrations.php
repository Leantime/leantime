<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use DateTime;
    use DateInterval;
    use leantime\domain\services\auth;

    class integrations extends controller
    {


        /**
         * constructor - initialize private variables
         *
         * @access public
         *
         */
        public function init()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);



        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {


            $this->tpl->displayPartial('connectors.providers');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            $this->tpl->displayPartial('connectors.providers');
        }


    }

}
