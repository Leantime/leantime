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
            $themeCore = new core\theme();

            if(!isset($_SESSION["userdata"]["id"])) {
                
                // This is a login session, we need to ensure the default theme and the default language (or the user's browser)
                if($this->config->keepTheme && isset($_COOKIE['theme'])) {
                    
                    $theme = $_COOKIE['theme'];
                    
                }else{
                    
                    $theme = $this->config->defaultTheme;
                    
                }
                
            }
            else {

                // This is not a login session
                if(!isset($_SESSION["usersettings.".$_SESSION["userdata"]["id"].".theme"]) ||
                   empty($_SESSION["usersettings.".$_SESSION["userdata"]["id"].".theme"])) {

                    // User has a saved theme
                    $theme = $this->settingsRepo->getSetting("usersettings.".$_SESSION["userdata"]["id"].".theme");
                    if($theme === false) {
                        
                        if($this->config->keepTheme && isset($_COOKIE['theme'])) {
                    
                            $theme = $_COOKIE['theme'];

                        }else{
                            
                            $theme = $this->config->defaultTheme;
                            
                        }
                        
                    }
                    
                }else{

                    $theme = $_SESSION["usersettings.".$_SESSION["userdata"]["id"].".theme"];
                    
                }
                
            }
            $themeCore->setActive($theme);
            // error_log("CURRENT THEME=$theme/ACTIVE=".$themeCore->getActive()."/COOKIES=".print_r($_COOKIE,true));
            
            if (!isset($_SESSION["companysettings.logoPath"])) {

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

            if (!isset($_SESSION["companysettings.primarycolor"])) {

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

            if (!isset($_SESSION["companysettings.sitename"])) {
                
                $sitename = $this->settingsRepo->getSetting("companysettings.sitename");
                if ($sitename !== false) {
                    $_SESSION["companysettings.sitename"] = $sitename;
                } else {
                    $_SESSION["companysettings.sitename"] = $this->config->sitename;
                }
                
            }

            $tpl->assign('theme', $themeCore->getActive());
            $tpl->assign('appSettings', $appSettings);
            $tpl->displayPartial('general.header');
        }
    }
}
