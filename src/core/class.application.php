<?php

namespace leantime\core;

use leantime\domain\services;

class application
{
    
    /**
     * @access private
     * @var    string - array of scripts to render (currently only CSS and Javascript)
     */
    private static $sections = array();

    private $config;
    private $settings;
    private $login;
    private $frontController;
    private $projectService;

    public function __construct(login $login)
    {
        $this->config = new config();// Used in template
        $this->settings = new settings(); //Used in templates to show app version
        $this->login = $login;
        $this->frontController = frontcontroller::getInstance(ROOT);

    }

    /**
     * start - renders applicaiton and routes to correct template, writes content to output buffer
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

            //Hard coded routes for a few pages that can be access without login
            if(isset($_GET['resetPassword']) === true) {
                include '../src/resetPassword.php';
            }else if(isset($_GET['install']) === true) {
                 include '../src/install.php';
            }else if(isset($_GET['update']) === true) {
                include '../src/update.php';
            }else{
                include '../src/login.php';
            }    
        
        }else{

            $this->projectService = new services\projects();

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

                if (strpos($logoPath, 'http') === 0) {
                    $_SESSION["companysettings.logoPath"] =  $logoPath;
                }else{
                    $_SESSION["companysettings.logoPath"] =  BASE_URL.$logoPath;
                }

            }else{

                if (strpos($this->config->logoPath, 'http') === 0) {
                    $_SESSION["companysettings.logoPath"] = $this->config->logoPath;
                }else{
                    $_SESSION["companysettings.logoPath"] = BASE_URL.$this->config->logoPath;
                }

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

        //Only run this if the user is not logged in (db should be updated/installed before user login)
        if($this->login->logged_in()===false) {

            if($settings->checkIfInstalled() === false && isset($_GET['install']) === false){
                header("Location:".BASE_URL."/install");
                exit();
            }

            $dbVersion = $settings->getSetting("db-version");
            if ($this->settings->dbVersion != $dbVersion && isset($_GET['update']) === false && isset($_GET['install']) === false) {
                header("Location:".BASE_URL."/update");
                exit();
            }
        }


    }

}
