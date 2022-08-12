<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class update
    {

        private $tpl;
        private $usersService;
        private $redirectUrl;


        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->installRepo = new repositories\install();


        }


        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {

            $this->tpl->display("install.update", 200, "entry");

        }

        public function post($params) {

            if(isset($_POST['updateDB'])) {

                $success = $this->installRepo->updateDB();

                if(is_array($success) === true) {

                    foreach($success as $errorMessage) {
                        error_log($errorMessage);
                        $this->tpl->setNotification($errorMessage, "error");
                        core\frontcontroller::redirect(BASE_URL."/install/update");
                    }
                }

                if($success === true){

                    $this->tpl->setNotification(sprintf($this->language->__("text.update_was_successful"),BASE_URL), "success");
                    core\frontcontroller::redirect(BASE_URL);

                }
            }

        }

    }
}
