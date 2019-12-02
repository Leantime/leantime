<?php

namespace leantime\core;

use leantime\domain\services;

class application
{

    private $config;
    private $settings;
    private $login;
    private $frontController;
    private $language;
    private $projectService;


    public function __construct(config $config,
                                settings $settings,
                                login $login,
                                FrontController $frontController,
                                language $language,
                                services\projects $projectService)
    {

        $this->config = $config;
        $this->settings = $settings;
        $this->login = $login;
        $this->frontController = $frontController;
        $this->language = $language;
        $this->projectService = $projectService;

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
            $language = $this->language;

            //Run password reset through application to avoid security holes in the front controller
            if(isset($_GET['resetPassword']) === true) {
                include '../src/resetPassword.php';
            }else if(isset($_GET['install']) === true) {
                 include '../src/install.php';
            }else{
                include '../src/login.php';
            }    
        
        }else{

            //Set current/default project
            $this->projectService->setCurrentProject();

            //Run frontcontroller
            $frontController->run();
        }

        $toRender = ob_get_clean();
        echo $toRender;
            
    }

    public function overrideThemeSettings() {

        $settings = new \leantime\domain\repositories\setting();

        if(isset($_SESSION["companysettings.logoPath"]) === false) {
            $logoPath = $settings->getSetting("companysettings.logoPath");
            if ($logoPath !== false) {
                $_SESSION["companysettings.logoPath"] = $logoPath;
            }else{
                $_SESSION["companysettings.logoPath"] = $this->config->logoPath;
            }
        }

        if(isset($_SESSION["companysettings.mainColor"]) === false) {
            $mainColor = $settings->getSetting("companysettings.mainColor");
            if ($mainColor !== false) {
                $_SESSION["companysettings.mainColor"] = $mainColor;
            }else{
                $_SESSION["companysettings.mainColor"] = $this->config->mainColor;
            }
        }

        if(isset($_SESSION["companysettings.sitename"]) === false) {
            $sitename = $settings->getSetting("companysettings.sitename");
            if ($sitename !== false) {
                $_SESSION["companysettings.sitename"] = $sitename;
            }else{
                $_SESSION["companysettings.sitename"] = $this->config->sitename;
            }
        }
    }
}
