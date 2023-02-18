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

    class integration extends controller
    {


        private services\connector\providers $providerService;

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


            if(isset($params["provider"])) {
            //New integration with provider
                //Get the provider
                $provider = $this->providerService->getProvider($params["provider"]);

                //Initiate connection
                if(isset($params["step"])  && $params["step"] == "connect") {
                    $provider->connect();
                }

                //Choose Entities to sync
                if(isset($params["step"])  && $params["step"] == "entity") {
                    $provider->getEntities();
                    //TODO UI to show entity picker/mapper
                }

                //Choose fields to map
                //Choose Entities to sync
                if(isset($params["step"])  && $params["step"] == "fields") {
                    $provider->getFields();
                    //TODO UI to show field picker/mapper
                }

                if(isset($params["step"])  && $params["step"] == "sync"){
                    //TODO UI to show sync schedule/options
                }

                if(isset($params["step"])  && $params["step"] == "confirm"){
                    //confirm and store in DB

                }

            }else if(isset($params["integration"])) {
            //Edit existing integration


            }

            $this->tpl->displayPartial('connectors.integration');
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
