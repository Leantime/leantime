<?php

namespace leantime\core;

/**
 * Class application
 * @package leantime\core
 */
class application
{

    private $config;
    private $settings;
    private $login;
    private $frontController;
    private $language;


    public function __construct(config $config, settings $settings, login $login, frontcontroller $frontController, language $language)
    {

        $this->config = $config;
        $this->settings = $settings;
        $this->login = $login;
        $this->frontController = $frontController;
        $this->language = $language;

    }

    /**
     * start - renders application and routes to correct template, writes content to output buffer
     *
     * @access public static
     * @return void
     */
    public function start()
    {
        
        $config = $this->config; // Used in template
        $settings = $this->settings; //Used in templates to show app version
        $login = $this->login;
        $frontController = $this->frontController;


        //Override theme settings
        $this->overrideThemeSettings();

        ob_start();
        
        if($this->login->logged_in()===false) {

            //Language is usually initialized by template engine. But template is not loaded on log in / install case
            $language = new language();

            //Run password reset through application to avoid security holes in the front controller
            if(isset($_GET['resetPassword']) === true) {
                include '../src/resetPassword.php';
            }else if(isset($_GET['install']) === true) {
                 include '../src/install.php';
            }else{
                include '../src/login.php';
            }    
        
        }else{

            $this->frontController->run();

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
