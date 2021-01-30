<?php

namespace leantime\core;

use leantime\domain\services;
use leantime\domain\repositories;

class application
{

    private $config;
    private $settings;
    private $login;
    private $frontController;
    private $language;
    private $projectService;
    private $settingsRepo;


    public function __construct(config $config,
                                settings $settings,
                                login $login,
                                frontcontroller $frontController,
                                language $language,
                                services\projects $projectService,
                                repositories\setting $settingRepo)
    {

        $this->config = $config;
        $this->settings = $settings;
        $this->login = $login;
        $this->frontController = $frontController;
        $this->language = $language;
        $this->projectService = $projectService;
        $this->settingsRepo = $settingRepo;

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

        $this->loadHeaders();

        ob_start();

        if($this->login->logged_in()===false) {

            //Language is usually initialized by template engine. But template is not loaded on log in / install case
            $language = $this->language;

            //Run password reset through application to avoid security holes in the front controller
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
            // Check if trying to access twoFA code page, or if trying to access any other action without verifying the code.
            if(isset($_GET['twoFA']) === true) {
                if($_SESSION['userdata']['twoFAVerified'] != true) {
                    $language = $this->language;
                    include '../src/twoFA.php';
                }
            }else if($_SESSION['userdata']['twoFAEnabled'] && $_SESSION['userdata']['twoFAVerified'] === false){
               $login->redirect2FA($_SERVER['REQUEST_URI']);
            }

            //Set current/default project
            $this->projectService->setCurrentProject();

            //Run frontcontroller
            $frontController->run();
        }

        $toRender = ob_get_clean();
        echo $toRender;
            
    }

    public function loadHeaders() {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
    }

    public function overrideThemeSettings() {

        if(isset($_SESSION["companysettings.logoPath"]) === false) {

            $logoPath = $this->settingsRepo->getSetting("companysettings.logoPath");

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
            $mainColor = $this->settingsRepo->getSetting("companysettings.mainColor");
            if ($mainColor !== false) {
                $_SESSION["companysettings.mainColor"] = $mainColor;
            }else{
                $_SESSION["companysettings.mainColor"] = $this->config->mainColor;
            }
        }

        if(isset($_SESSION["companysettings.sitename"]) === false) {
            $sitename = $this->settingsRepo->getSetting("companysettings.sitename");
            if ($sitename !== false) {
                $_SESSION["companysettings.sitename"] = $sitename;
            }else{
                $_SESSION["companysettings.sitename"] = $this->config->sitename;
            }
        }

        if(isset($_SESSION["companysettings.language"]) === false || $_SESSION["companysettings.language"] == false) {
            $language = $this->settingsRepo->getSetting("companysettings.language");
            if ($language !== false) {
                $_SESSION["companysettings.language"] = $language;
            }else{
                $_SESSION["companysettings.language"] = $this->config->language;
            }
        }

        //Only run this if the user is not logged in (db should be updated/installed before user login)
        if($this->login->logged_in()===false) {

            if($this->settingsRepo->checkIfInstalled() === false && isset($_GET['install']) === false){
                header("Location:".BASE_URL."/install");
                exit();
            }

            $dbVersion = $this->settingsRepo->getSetting("db-version");
            if ($this->settings->dbVersion != $dbVersion && isset($_GET['update']) === false && isset($_GET['install']) === false) {
                header("Location:".BASE_URL."/update");
                exit();
            }
        }
    }
}
