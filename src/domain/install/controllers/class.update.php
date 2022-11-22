<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class update extends controller
    {

        private $installRepo;
        private $settingsRepo;
        private $appSettings;


        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {

            $this->installRepo = new repositories\install();
            $this->settingsRepo = new repositories\setting();
            $this->appSettings = new core\appSettings();

        }


        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
            $dbVersion = $this->settingsRepo->getSetting("db-version");
            if ($this->appSettings->dbVersion == $dbVersion){
                core\frontcontroller::redirect(BASE_URL."/auth/login");
            }

            $this->tpl->display("install.update", "entry");

        }

        public function post($params) {


            if(isset($_POST['updateDB'])) {

                $success = $this->installRepo->updateDB();

                if(is_array($success) === true) {

                    var_dump($success);

                    foreach($success as $errorMessage) {

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
