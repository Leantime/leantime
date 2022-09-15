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
         *
         */
        public function __construct()
        {

            $this->tpl = new core\template();


        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {
            if(isset($params['module']) && $params['module'] != "") {
                $filteredInput = htmlspecialchars($params['module']);
                $this->tpl->displayPartial('help.'.$filteredInput);
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

        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {

        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {

        }

    }

}
