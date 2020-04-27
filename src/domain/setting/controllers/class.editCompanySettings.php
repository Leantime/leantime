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
            $this->language = new core\language();


        }

        /**
         * get - handle get requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function get($params)
        {
            if(core\login::userIsAtLeast("admin")) {

                $companySettings = array(
                    "logo" => $_SESSION["companysettings.logoPath"],
                    "color" => $_SESSION["companysettings.mainColor"],
                    "name" => $_SESSION["companysettings.sitename"],
                    "language" => $_SESSION["companysettings.language"]
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
         * @param  paramters or body of the request
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

                $this->tpl->setNotification($this->language->__("notifications.company_settings_edited_successfully"), "success");
                $this->tpl->redirect(BASE_URL."/setting/editCompanySettings");
                    

            }

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
