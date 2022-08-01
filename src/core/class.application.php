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
    private $reportService;

    private $publicActions = array(
        "auth.login",
        "auth.passwordReset",
        "install.go",
        "install.update",
        "general.error404"
    );


    public function __construct(config $config,
                                appSettings $settings,
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
        $this->reportService = new services\reports();

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
        $language = $this->language;
        $telemetryResponse = false;

        //Override theme settings
        $this->overrideThemeSettings();

        $this->loadHeaders();

        ob_start();

        if($this->login->logged_in()===false) {

           //Allow a limited set of actions to be public
           if(in_array($frontController->fullAction, $this->publicActions)){
               $frontController->run();
           }

           /*
                //Run password reset through application to avoid security holes in the front controller
                if(isset($_GET['act']) && $_GET['act'] == 'api.i18n') {
                    $frontController->run();
                }elseif(isset($_GET['resetPassword']) === true) {
                    require ROOT.'/../src/resetPassword.php';
                }elseif(isset($_GET['install']) === true) {
                    require ROOT.'/../src/install.php';
                }elseif(isset($_GET['update']) === true) {
                    require ROOT.'/../src/update.php';
                }else{
                    require ROOT.'/../src/login.php';
                }
            */


        }else{

            // Check if trying to access twoFA code page, or if trying to access any other action without verifying the code.
            if(isset($_GET['twoFA']) === true) {
                if($_SESSION['userdata']['twoFAVerified'] != true) {
                    require ROOT.'/../src/twoFA.php';
                }
            }elseif($_SESSION['userdata']['twoFAEnabled'] && $_SESSION['userdata']['twoFAVerified'] === false){
               $login->redirect2FA($_SERVER['REQUEST_URI']);
            }

            //Send telemetry if user is opt in and if it hasn't been sent that day
            $telemetryResponse = $this->reportService->sendAnonymousTelemetry();

            //Set current/default project
            $this->projectService->setCurrentProject();

            //Run frontcontroller
            $frontController->run();
        }

        $toRender = ob_get_clean();

        echo $toRender;

        //Wait for telemetry if it was sent
        if($telemetryResponse !== false){

            try {

                $telemetryResponse->wait();

            }catch(\Exception $e){

                error_log($e->getMessage(), 0);

            }

        }
            
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


        if(isset($_SESSION["companysettings.primarycolor"]) === false) {

            $_SESSION["companysettings.primarycolor"] = "#1b75bb";
            $_SESSION["companysettings.secondarycolor"] = "#81B1A8";

            //Old setting
            $mainColor = $this->settingsRepo->getSetting("companysettings.mainColor");
            if ($mainColor !== false) {
                $_SESSION["companysettings.primarycolor"] = "#".$mainColor;
                $_SESSION["companysettings.secondarycolor"] = "#".$mainColor;
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

        }else{
            if(!str_starts_with($_SESSION["companysettings.primarycolor"], "#")){
                $_SESSION["companysettings.primarycolor"] = "#".$_SESSION["companysettings.primarycolor"];
                $_SESSION["companysettings.secondarycolor"] = "#".$_SESSION["companysettings.primarycolor"];
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
                if(!isset($_GET['act']) || $_GET['act'] != 'api.i18n') {
                    header("Location:" . BASE_URL . "/install");
                    exit();
                }
            }

            $dbVersion = $this->settingsRepo->getSetting("db-version");
            if ($this->settings->dbVersion != $dbVersion && isset($_GET['update']) === false && isset($_GET['install']) === false) {
                if(!isset($_GET['act']) || $_GET['act'] != 'api.i18n') {
                    header("Location:".BASE_URL."/update");
                    exit();
                }
            }
        }
    }
}
