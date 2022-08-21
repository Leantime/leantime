<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use DateTime;
    use DateInterval;
    use leantime\domain\services\auth;


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
            auth::authOrRedirect([roles::$owner, roles::$admin]);


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
            if(auth::userIsAtLeast(roles::$owner)) {

                $companySettings = array(
                    "logo" => $_SESSION["companysettings.logoPath"],
                    "primarycolor" => $_SESSION["companysettings.primarycolor"],
                    "secondarycolor" => $_SESSION["companysettings.secondarycolor"],
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
                    $companySettings["primarycolor"] = "#".$mainColor;
                    $companySettings["secondarycolor"] = "#".$mainColor;
                }

                $primaryColor = $this->settingsRepo->getSetting("companysettings.primarycolor");
                if($primaryColor !== false){
                    $companySettings["primarycolor"] = $primaryColor;
                }

                $secondaryColor = $this->settingsRepo->getSetting("companysettings.secondarycolor");
                if($secondaryColor !== false){
                    $companySettings["secondarycolor"] = $secondaryColor;
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
                && isset($params['primarycolor'])  && $params['primarycolor'] != ""
                && isset($params['language'])  && $params['language'] != "") {

                $this->settingsRepo->saveSetting("companysettings.sitename", htmlentities(addslashes($params['name'])));
                $this->settingsRepo->saveSetting("companysettings.language", htmlentities(addslashes($params['language'])));

                $this->settingsRepo->saveSetting("companysettings.primarycolor", htmlentities(addslashes($params['primarycolor'])));
                $this->settingsRepo->saveSetting("companysettings.secondarycolor", htmlentities(addslashes($params['secondarycolor'])));


                //Check if main color is still in the system
                //if so remove. This call should be removed in a few versions.
                $mainColor = $this->settingsRepo->getSetting("companysettings.mainColor");
                if($mainColor !== false){
                    $this->settingsRepo->deleteSetting("companysettings.mainColor");
                }


                $_SESSION["companysettings.primarycolor"] = htmlentities(addslashes($params['primarycolor']));
                $_SESSION["companysettings.secondarycolor"] = htmlentities(addslashes($params['secondarycolor']));
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

            }

            $this->tpl->redirect(BASE_URL."/setting/editCompanySettings");

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
