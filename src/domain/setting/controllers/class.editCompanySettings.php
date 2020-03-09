<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use \DateTime;
    use \DateInterval;


    class editCompanySettings
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
            $this->config = new core\config();
            $this->settingsRepo = new repositories\setting();


        }

        /**
         * get - handle get requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function get($params)
        {
            if($_SESSION['userdata']['role'] == 'admin') {

                $companySettings = array(
                    "logo" => $_SESSION["companysettings.logoPath"],
                    "color" => $_SESSION["companysettings.mainColor"],
                    "name" => $_SESSION["companysettings.sitename"]
                );

                $logoPath = $this->settingsRepo->getSetting("companysettings.logoPath");
                if($logoPath !== false){

                    if (strpos($logoPath, 'http') === 0) {
                        $companySettings["logo"] = $logoPath;
                    }else{
                        $companySettings["logo"] = BASE_URL.$logoPath;
                    }
                }

                $mainColor = $this->settingsRepo->getSetting("companysettings.mainColor");
                if($mainColor !== false){
                    $companySettings["color"] = $mainColor;
                }

                $sitename = $this->settingsRepo->getSetting("companysettings.sitename");
                if($sitename !== false){
                    $companySettings["name"] = $sitename;
                }

                $this->tpl->assign("companySettings", $companySettings);
                $this->tpl->display('setting.editCompanySettings');

            }else{

                $this->tpl->display('general.error');

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
            //If ID is set its an update
            if(isset($params['name']) && isset($params['color']) && $params['name'] != "" && $params['color'] != "") {

                $this->settingsRepo->saveSetting("companysettings.mainColor", htmlentities(addslashes($params['color'])));
                $this->settingsRepo->saveSetting("companysettings.sitename", htmlentities(addslashes($params['name'])));

                $_SESSION["companysettings.mainColor"] = htmlentities(addslashes($params['color']));
                $_SESSION["companysettings.sitename"] = htmlentities(addslashes($params['name']));

                header("Location:".BASE_URL."/setting/editCompanySettings");

            }

            $this->get("");
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
