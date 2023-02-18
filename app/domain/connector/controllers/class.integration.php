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
                $this->tpl->assign("provider", $provider);

                //Initiate connection
                if(isset($params["step"])  && $params["step"] == "connect") {

                    //This should handle connection UI
                    $provider->connect();

                    //Not sure if this is needed at all if connect handles the connection.
                    //$this->tpl->display('connectors.integrationConnect');
                }

                //Choose Entities to sync
                if(isset($params["step"])  && $params["step"] == "entity") {
                    $provider->getEntities();
                    //TODO UI to show entity picker/mapper
                    $this->tpl->display('connector.integrationEntity');
                }

                //Choose fields to map
                //Choose Entities to sync
                if(isset($params["step"])  && $params["step"] == "fields") {
                    $provider->getFields();
                    //TODO UI to show field picker/mapper
                    $this->tpl->display('connector.integrationFields');
                }

                if(isset($params["step"])  && $params["step"] == "sync"){
                    //TODO UI to show sync schedule/options
                    $this->tpl->display('connector.integrationSync');
                }

                if(isset($params["step"])  && $params["step"] == "confirm"){
                    //confirm and store in DB
                    $this->tpl->display('connector.integrationConfirm');

                }

                if(!isset($params["step"])) {

                    $this->tpl->display('connector.newIntegration');

                }


            }else if(isset($params["integration"])) {
            //Edit existing integration


            }


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
