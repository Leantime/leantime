<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use DateTime;
    use DateInterval;


    class editCompanySettings
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
            $this->config = new core\config();
            $this->settingsRepo = new repositories\setting();
            $this->language = new core\language();


        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {
            if(core\login::userIsAtLeast("admin")) {

                $companySettings = array(
                    "logo" => $_SESSION["companysettings.logoPath"],
                    "color" => $_SESSION["companysettings.mainColor"],
                    "name" => $_SESSION["companysettings.sitename"],
                    "language" => $_SESSION["companysettings.language"],
                    "telemetryActive" => false
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

                $language = $this->settingsRepo->getSetting("companysettings.language");
                if($language !== false){
                    $companySettings["language"] = $language;
                }

                $telemetryActive = $this->settingsRepo->getSetting("companysettings.telemetry.active");
                if($telemetryActive !== false){
                    $companySettings["telemetryActive"] = $telemetryActive;
                }

                $this->tpl->assign("languageList", $this->language->getLanguageList());
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
         *
         */
        public function post($params)
        {
            //If ID is set its an update
            if(isset($params['name']) && $params['name'] != ""
                && isset($params['color'])  && $params['color'] != ""
                && isset($params['language'])  && $params['language'] != "") {

                $this->settingsRepo->saveSetting("companysettings.mainColor", htmlentities(addslashes($params['color'])));
                $this->settingsRepo->saveSetting("companysettings.sitename", htmlentities(addslashes($params['name'])));
                $this->settingsRepo->saveSetting("companysettings.language", htmlentities(addslashes($params['language'])));

                $_SESSION["companysettings.mainColor"] = htmlentities(addslashes($params['color']));
                $_SESSION["companysettings.sitename"] = htmlentities(addslashes($params['name']));
                $_SESSION["companysettings.language"] = htmlentities(addslashes($params['language']));

                if(isset($_POST['telemetryActive'])) {

                    $this->settingsRepo->saveSetting("companysettings.telemetry.active", "true");

                }else{

                    //When opting out, delete all telemetry related settings including UUID
                    $this->settingsRepo->deleteSetting("companysettings.telemetry.active");
                    $this->settingsRepo->deleteSetting("companysettings.telemetry.lastUpdate");
                    $this->settingsRepo->deleteSetting("companysettings.telemetry.anonymousId");

                }

                $this->tpl->setNotification($this->language->__("notifications.company_settings_edited_successfully"), "success");
                $this->tpl->redirect(BASE_URL."/setting/editCompanySettings");
                    

            }

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
