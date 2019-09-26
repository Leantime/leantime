<?php

namespace leantime\core;

class application
{
    
    /**
     * @access private
     * @var    string - array of scripts to render (currently only CSS and Javascript)
     */
    private static $sections = array();
    
    /**
     * start - renders applicaiton and routes to correct template, writes content to output buffer
     *
     * @access public static
     * @return void
     */
    public function start()
    {
        
        $config = new config();
        $login = new login(session::getSID());
        $frontController = frontcontroller::getInstance(ROOT);

        //Override theme settings
        $this->overrideThemeSettings();

        ob_start();
        
        if($login->logged_in()===false) {
                
            //Run password reset through application to avoid security holes in the front controller
            if(isset($_GET['resetPassword']) === true) {
                include '../src/resetPassword.php';
            }else if(isset($_GET['install']) === true) {
                 include '../src/install.php';
            }else{
                include '../src/login.php';
            }    
        
        }else{

            $frontController->run();
        }
        $toRender = ob_get_clean();
        echo $toRender;
            
    }

    public function overrideThemeSettings() {

        $settings = new \leantime\domain\repositories\setting();
        $config = new config();

        if(isset($_SESSION["companysettings.logoPath"]) === false) {
            $logoPath = $settings->getSetting("companysettings.logoPath");
            if ($logoPath !== false) {
                $_SESSION["companysettings.logoPath"] = $logoPath;
            }else{
                $_SESSION["companysettings.logoPath"] = $config->logoPath;
            }
        }

        if(isset($_SESSION["companysettings.mainColor"]) === false) {
            $mainColor = $settings->getSetting("companysettings.mainColor");
            if ($mainColor !== false) {
                $_SESSION["companysettings.mainColor"] = $mainColor;
            }else{
                $_SESSION["companysettings.mainColor"] = $config->mainColor;
            }
        }

        if(isset($_SESSION["companysettings.sitename"]) === false) {
            $sitename = $settings->getSetting("companysettings.sitename");
            if ($sitename !== false) {
                $_SESSION["companysettings.sitename"] = $sitename;
            }else{
                $_SESSION["companysettings.sitename"] = $config->sitename;
            }
        }


    }

}
