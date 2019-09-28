<?php

namespace leantime\domain\controllers {

    use leantime\core;




    class showOnboardingDialog
    {

        private $tpl;


        /**
         * constructor - initialize private variables
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();


        }

        /**
         * get - handle get requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function get($params)
        {
            if(isset($params['module']) && $params['module'] != "") {
                $filteredInput = filter_var($params['module'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH);
                $this->tpl->displayPartial('help.'.$filteredInput);
            }


        }

        /**
         * post - handle post requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function post($params)
        {

        }

        /**
         * put - handle put requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function put($params)
        {

        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function delete($params)
        {

        }

    }

}
