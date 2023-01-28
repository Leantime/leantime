<?php

namespace leantime\core;

use leantime\domain\services;
use leantime\domain\repositories;

class application
{
    use eventhelpers;

    private appSettings $settings;
    private services\auth $auth;
    private frontcontroller $frontController;
    private language $language;
    private services\projects $projectService;
    private repositories\setting $settingsRepo;
    private services\reports $reportService;

    private IncomingRequest $incomingRequest;

    private $publicActions = array(
        "auth.login",
        "auth.resetPw",
        "auth.userInvite",
        "install",
        "install.update",
        "errors.error404",
        "errors.error500",
        "api.i18n",
        "calendar.ical"
    );

    /**
     * constructor
     */
    public function __construct(IncomingRequest $incomingRequest)
    {

        //Set Session
        $session = session::getInstance();
        $this->auth = services\auth::getInstance($session->getSID());
        $this->settings = new appSettings();
        $this->frontController = frontcontroller::getInstance(ROOT, $incomingRequest);
        $this->projectService = new services\projects();
        $this->settingsRepo = new repositories\setting();
        $this->reportService = new services\reports();
        $this->incomingRequest = $incomingRequest;
    }

    /**
     * start - renders application and routes to correct template, writes content to output buffer
     *
     * @access public
     * @return void
     */
    public function start(): void
    {
        //Only run telemetry when logged in
        $telemetryResponse = false;

        $this->loadHeaders();

        //Check if Leantime is installed
        $this->checkIfInstalled();

        events::discover_listeners();

        self::dispatch_event("beginning", ['application' => $this]);

        //Filter to add additional public pages that don't require a login
        $this->publicActions = self::dispatch_filter("publicActions", $this->publicActions);



        //Dispatch public controllers
        if (in_array(frontController::getCurrentRoute(), $this->publicActions)) {

            $this->frontController::dispatch();

        } elseif ($this->incomingRequest->hasAPIKey()) {

            //Request is API request
            $apiKey = $this->incomingRequest->getAPIKey();
            $this->apiService = new services\Api();
            $apiUser = $this->apiService->getAPIKeyUser($apiKey);

            if ($apiUser === false) {
                echo json_encode("{error:Not a valid API Key}");
                exit();
            }

            $this->auth->setUserSession($apiUser);
            $this->projectService->setCurrentProject();

            if(str_starts_with(frontController::getCurrentRoute(), "api.jsonRPC")){
                $this->frontController::dispatch();
            }else{
                echo "{success}";
            }


        } elseif ($this->auth->logged_in() === true) {
            $this->cronExec();

            //Send telemetry if user is opt in and if it hasn't been sent that day
            $telemetryResponse = $this->reportService->sendAnonymousTelemetry();

            // Check if trying to access twoFA code page, or if trying to access any other action without verifying the code.
            if ($_SESSION['userdata']['twoFAEnabled'] && $_SESSION['userdata']['twoFAVerified'] === false) {
                    $this->redirectWithOrigin("twoFA.verify", $_GET['redirect'] ?? '');
            } else {
                $this->projectService->setCurrentProject();
            }

            $this->frontController::dispatch();

        //Not logged in and controller is not public. Redirect to auth
        } else {
            $this->redirectWithOrigin("auth.login", $this->incomingRequest->getRequestURI());
        }

        //Wait for telemetry if it was sent
        if ($telemetryResponse !== false) {
            try {
                $telemetryResponse->wait();
            } catch (\Exception $e) {
                error_log($e);
            }
        }

        self::dispatch_event("end", ['application' => $this]);
    }

    public function redirectWithOrigin($route, $origin): void
    {
        $redirectURL = '';
        if (strlen($origin) > 1) {
            $redirectURL = "?redirect=" . urlencode($origin);
        }

        if ($this->frontController::getCurrentRoute() !== $route) {
            $this->frontController::redirect(BASE_URL . "/" . str_replace(".", "/", $route) . "" . $redirectURL);
        }
    }

    /**
     * sets the headers
     *
     * @return void
     */
    private function loadHeaders(): void
    {

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
    private function cronExec()
    {

        $audit = new \leantime\domain\repositories\audit();

        if (!isset($_SESSION['last_cron_call'])) {
            $lastEvent = $audit->getLastEvent('cron');

            if (isset($lastEvent['date'])) {
                $lastCronEvent = strtotime($lastEvent['date']);
            } else {
                $lastCronEvent = 0;
            }
        } else {
            $lastCronEvent = $_SESSION['last_cron_call'];
        }

        // Using audit system to prevent too frequent cron executions
        $nowDate = time();
        $timeSince = abs($nowDate - $lastCronEvent);

        //Run every 5 min
        $cron_exec_increment = self::dispatch_filter('increment', 300);

        if ($timeSince >= $cron_exec_increment) {
            $_SESSION["do_cron"] = true;
            $_SESSION['last_cron_call'] = time();
        } else {
            unset($_SESSION["do_cron"]);
        }
    }

    /**
     * Checks if leantime is installed
     *
     * @return void
     */
    private function checkIfInstalled()
    {

        if (!isset($_SESSION['isInstalled']) || $_SESSION['isInstalled'] === false) {
            if ($this->settingsRepo->checkIfInstalled() === false && isset($_GET['install']) === false) {
                $_SESSION['isInstalled'] = false;

                //In case we have a previous session
                if (isset($_SESSION['userdata'])) {
                    unset($_SESSION['userdata']);
                }

                //Don't redirect on i18n call
                if (
                    $this->frontController::getCurrentRoute() !== "install" &&
                    $this->frontController::getCurrentRoute() !== "api.i18n"
                ) {
                    $this->frontController::redirect(BASE_URL . "/install");
                }
            } else {
                $_SESSION['isInstalled'] = true;
            }
        }


        if (isset($_SESSION['isInstalled']) && $_SESSION['isInstalled'] === true) {
            //Check if installed first. Depending on session state it might say installed after a user uninstalled
            if ($this->settingsRepo->checkIfInstalled() === false) {
                $_SESSION['isInstalled'] = false;

                //In case we have a previous session
                if (isset($_SESSION['userdata'])) {
                    unset($_SESSION['userdata']);
                }
                $this->frontController::redirect(BASE_URL . "/install");
            }

            $dbVersion = $this->settingsRepo->getSetting("db-version");

            if ($this->settings->dbVersion != $dbVersion) {
                $_SESSION['isUpdated'] = false;
            } else {
                $_SESSION['isUpdated'] = true;
            }

            if ($this->settings->dbVersion != $dbVersion && isset($_GET['update']) === false && isset($_GET['install']) === false) {
                //Don't redirect on i18n call
                if (
                    $this->frontController::getCurrentRoute() !== "install.update" &&
                    $this->frontController::getCurrentRoute() !== "api.i18n"
                ) {
                    $this->frontController::redirect(BASE_URL . "/install/update");
                }
            }
        }
    }
}
