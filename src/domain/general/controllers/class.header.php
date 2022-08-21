<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use DebugBar\StandardDebugBar;
    use leantime\core\session;
    use leantime\domain\repositories\reports;

    class header
    {
        public $settingsRepo;
        public $config;

        public function run()
        {
            $this->settingsRepo = new \leantime\domain\repositories\setting();
            $this->config = new core\config();

            $tpl = new core\template();
            $appSettings = new core\appSettings();

            if(isset($_COOKIE['theme'])){
                $_SESSION['usersettings.theme'] = htmlentities($_COOKIE['theme']);
            }

            if( (!isset($_SESSION["usersettings.theme"]) || $_SESSION["usersettings.theme"] == '') && isset($_SESSION["userdata"]["id"])){

                $theme = $this->settingsRepo->getSetting("usersettings.".$_SESSION["userdata"]["id"].".theme");

                if($theme == "default" || $theme == "dark"){
                    $_SESSION["usersettings.theme"] = $theme;
                }else{
                    //No setting at all. Go to default
                    $_SESSION["usersettings.theme"] = "default";
                }

            }

            if (isset($_SESSION["companysettings.logoPath"]) === false) {

                $logoPath = $this->settingsRepo->getSetting("companysettings.logoPath");

                if ($logoPath !== false) {
                    if (strpos($logoPath, 'http') === 0) {
                        $_SESSION["companysettings.logoPath"] = $logoPath;
                    } else {
                        $_SESSION["companysettings.logoPath"] = BASE_URL . $logoPath;
                    }
                } else {
                    if (strpos($this->config->logoPath, 'http') === 0) {
                        $_SESSION["companysettings.logoPath"] = $this->config->logoPath;
                    } else {
                        $_SESSION["companysettings.logoPath"] = BASE_URL . $this->config->logoPath;
                    }
                }
            }

            if (isset($_SESSION["companysettings.primarycolor"]) === false) {

                $_SESSION["companysettings.primarycolor"] = "#1b75bb";
                $_SESSION["companysettings.secondarycolor"] = "#81B1A8";

                //Old setting
                $mainColor = $this->settingsRepo->getSetting("companysettings.mainColor");
                if ($mainColor !== false) {
                    $_SESSION["companysettings.primarycolor"] = "#" . $mainColor;
                    $_SESSION["companysettings.secondarycolor"] = "#" . $mainColor;
                }

                //new setting
                $primaryColor = $this->settingsRepo->getSetting("companysettings.primarycolor");
                if ($primaryColor !== false) {
                    $_SESSION["companysettings.primarycolor"] = $primaryColor;
                    $_SESSION["companysettings.secondarycolor"] = $primaryColor;
                }

                $secondaryColor = $this->settingsRepo->getSetting("companysettings.secondarycolor");
                if ($secondaryColor !== false) {
                    $_SESSION["companysettings.secondarycolor"] = $secondaryColor;
                }
            } else {
                if (!str_starts_with($_SESSION["companysettings.primarycolor"], "#")) {
                    $_SESSION["companysettings.primarycolor"] = "#" . $_SESSION["companysettings.primarycolor"];
                    $_SESSION["companysettings.secondarycolor"] = "#" . $_SESSION["companysettings.primarycolor"];
                }
            }

            if (isset($_SESSION["companysettings.sitename"]) === false) {
                $sitename = $this->settingsRepo->getSetting("companysettings.sitename");
                if ($sitename !== false) {
                    $_SESSION["companysettings.sitename"] = $sitename;
                } else {
                    $_SESSION["companysettings.sitename"] = $this->config->sitename;
                }
            }

            $tpl->assign('theme', $_SESSION["usersettings.theme"] ?? null);
            $tpl->assign('appSettings', $appSettings);
            $tpl->displayPartial('general.header');
        }
    }
}
