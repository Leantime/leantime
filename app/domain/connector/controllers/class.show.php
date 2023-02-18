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

    class show extends controller
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
            $this->providerService = new services\connector\providers();


        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {


            $providers = $this->providerService->getProviders();

            $this->tpl->assign("providers", $providers);

            $this->tpl->display('connector.show');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            //Redirect.
        }


    }

}
