<?php

namespace leantime\core;

use leantime\domain\services;
use leantime\domain\repositories;
use leantime\core\eventhelpers;

class application
{

    use eventhelpers;

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
        "auth.resetPw",
        "auth.userInvite",
        "install",
        "install.update",
        "errors.error404",
        "api.i18n",
        "calendar.ical"
    );

    /**
     * constructor
     */
    public function __construct()
    {

        //Set Session
        $session = session::getInstance();
        $this->auth = services\auth::getInstance($session->getSID());
        $this->settings = new appSettings();
        $this->frontController = frontcontroller::getInstance(ROOT);
        $this->projectService = new services\projects();
        $this->settingsRepo = new repositories\setting();
        $this->reportService = new services\reports();

    }

    /**
     * start - renders application and routes to correct template, writes content to output buffer
     *
     * @access public
     * @return void
     */
    public function start()
    {
        //Only run telemetry when logged in
        $telemetryResponse = false;

        $this->loadHeaders();

        //Check if Leantime is installed
        $this->checkIfInstalled();

        events::discover_listeners();

        self::dispatch_event("beginning", ['application' => $this]);

        //Allow a limited set of actions to be public
        if($this->auth->logged_in()===true) {

            //Run Cron
            $this->cronExec();

            //Send telemetry if user is opt in and if it hasn't been sent that day
            $telemetryResponse = $this->reportService->sendAnonymousTelemetry();

            // Check if trying to access twoFA code page, or if trying to access any other action without verifying the code.
            if($_SESSION['userdata']['twoFAEnabled'] && $_SESSION['userdata']['twoFAVerified'] === false){

                if($this->frontController::getCurrentRoute() !== "twoFA.verify"
                    && $this->frontController::getCurrentRoute() !== "auth.logout"
                    && $this->frontController::getCurrentRoute() !== "api.i18n") {

                    $redirectURL = "";
                    if(isset($_GET['redirect'])) {
                        $redirectURL = "?redirect=".$_GET['redirect'];
                    }

                    $this->frontController::redirect(BASE_URL."/twoFA/verify".$redirectURL);
                }

            }else{

                //House keeping when logged in.
                //Set current/default project
                $this->projectService->setCurrentProject();

            }

        }else{


            if(!in_array(frontController::getCurrentRoute(), $this->publicActions)) {

                $redirectURL = '';
                if(strlen($this->settings->getRequestURI()) > 1){
                    $redirectURL = "?redirect=".urlencode($this->settings->getRequestURI());
                }

                if ($this->frontController::getCurrentRoute() !== "auth.login") {
                    $this->frontController::redirect(BASE_URL . "/auth/login".$redirectURL);
                }

            }

        }

        //Dispatch controller
        $this->frontController::dispatch();

        //Wait for telemetry if it was sent
        if($telemetryResponse !== false){

            try {

                $telemetryResponse->wait();

            }catch(\Exception $e){

                error_log($e, 0);

            }

        }

        self::dispatch_event("end", ['application' => $this]);

    }

    /**
     * sets the headers
     *
     * @return void
     */
    private function loadHeaders() {

        $headers = self::dispatch_filter('headers', [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'X-Content-Type-Options' => 'nosniff'
        ]);

        foreach ($headers as $key => $value) {
            header("${key}: ${value}");
        }

    }

    /**
     * executes audits on cron
     *
     * @return void
     */
    private function cronExec() {

        $audit = new \leantime\domain\repositories\audit();

        if(!isset($_SESSION['last_cron_call'])) {

            $lastEvent = $audit->getLastEvent('cron');

            if(isset($lastEvent['date'])) {
                $lastCronEvent = strtotime($lastEvent['date']);
            }else{
                $lastCronEvent = 0;
            }

        }else{
            $lastCronEvent = $_SESSION['last_cron_call'];
        }

        // Using audit system to prevent too frequent cron executions
        $nowDate = time();
        $timeSince = abs($nowDate - $lastCronEvent);

        //Run every 5 min
        $cron_exec_increment = self::dispatch_filter('increment', 300);

        if ($timeSince >= $cron_exec_increment)
        {
            $_SESSION["do_cron"] = true;
            $_SESSION['last_cron_call'] = time();

        } else {
            unset ($_SESSION["do_cron"]);
        }

    }

    /**
     * Checks if leantime is installed
     *
     * @return void
     */
    private function checkIfInstalled() {

        if(!isset($_SESSION['isInstalled']) || $_SESSION['isInstalled'] === false) {

            if ($this->settingsRepo->checkIfInstalled() === false && isset($_GET['install']) === false) {

                $_SESSION['isInstalled'] = false;

                //In case we have a previous session
                if(isset($_SESSION['userdata'])){
                    unset($_SESSION['userdata']);
                }

                //Don't redirect on i18n call
                if($this->frontController::getCurrentRoute() !== "install" &&
                    $this->frontController::getCurrentRoute() !== "api.i18n") {
                    $this->frontController::redirect(BASE_URL . "/install");
                }

            }else{
                $_SESSION['isInstalled'] = true;
            }
        }


        if(isset($_SESSION['isInstalled']) && $_SESSION['isInstalled'] === true) {

            //Check if installed first. Depending on session state it might say installed after a user uninstalled
            if ($this->settingsRepo->checkIfInstalled() === false){
                $_SESSION['isInstalled'] = false;

                //In case we have a previous session
                if(isset($_SESSION['userdata'])){
                    unset($_SESSION['userdata']);
                }
                $this->frontController::redirect(BASE_URL . "/install");
            }

            $dbVersion = $this->settingsRepo->getSetting("db-version");

            if ($this->settings->dbVersion != $dbVersion){
                $_SESSION['isUpdated'] = false;
            }else{
                $_SESSION['isUpdated'] = true;
            }

            if ($this->settings->dbVersion != $dbVersion && isset($_GET['update']) === false && isset($_GET['install']) === false) {

                //Don't redirect on i18n call
                if($this->frontController::getCurrentRoute() !== "install.update" &&
                    $this->frontController::getCurrentRoute() !== "api.i18n"){

                    $this->frontController::redirect(BASE_URL . "/install/update");
                }

            }
        }

    }
}
