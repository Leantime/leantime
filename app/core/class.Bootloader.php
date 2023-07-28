<?php

namespace leantime\core;

use Illuminate\Contracts\Container\Container as IlluminateContainerContract;
use Psr\Container\ContainerInterface as PsrContainerContract;
use leantime\domain\services;
use leantime\domain\repositories;

class Bootloader
{
    use eventhelpers;

    /**
     * Bootloader instance
     *
     * @var static
     */
    protected static $instance;

    /**
     * Application instance
     *
     * @var \leantime\core\application
     */
    protected $app;

    /**
     * Public actions
     *
     * @var array
     */
    private $publicActions = array(
        "auth.login",
        "auth.resetPw",
        "auth.userInvite",
        "install",
        "install.update",
        "errors.error404",
        "errors.error500",
        "api.i18n",
        "calendar.ical",
        "oidc.login",
        "oidc.callback",
        "cron.run"
    );

    /**
     * Set the Bootloader instance
     *
     * @param \leantime\core\Bootloader $instance
     */
    public static function setInstance(?self $instance)
    {
        static::$instance = $instance;
    }

    /**
     * Get the Bootloader instance
     *
     * @return \leantime\core\Bootloader
     */
    public static function getInstance(?ContainerContract $app = null): self
    {
        return static::$instance ??= new static($app);
    }

    /**
     * Constructor
     *
     * @param \Illuminate\Contracts\Container\Container $app
     */
    public function __construct(?ContainerContract $app = null)
    {
        $this->app = $app;

        static::$instance ??= $this;
    }

    /**
     * Boot the Application.
     *
     * @return void
     */
    public function __invoke(): void
    {
        $this->boot();
    }

    /**
     * Boot the Application.
     *
     * @return void
     */
    public function boot(): void
    {
        if (! defined('LEANTIME_START')) {
            define('LEANTIME_START', microtime(true));
        }

        $app = $this->getApplication();

        if ($app->hasBeenBootstrapped()) {
            return;
        }

        $app->make(appSettings::class)->loadSettings();

        $request = $app->make(IncomingRequest::class);

        if (! defined('BASE_URL')) {
            define('BASE_URL', $config->appUrl ?? $request->getBaseUrl());
        }

        if (! defined('CURRENT_URL')) {
            define('CURRENT_URL', !empty($config->appUrl)
            ? $config->appUrl . $request->getRequestURI($config->appUrl)
            : $request->getFullURL());
        }

        $this->loadHeaders();

        $this->checkIfInstalled();

        $this->checkIfUpdated();

        events::discover_listeners();

        /**
         * The beginning of the application
         *
         * @param leantime\core\Bootloader $bootloader The bootloader object.
         */
        self::dispatch_event("beginning", ['bootloader' => $this]);

        $this->handleRequest();

        $app->setHasBeenBootstrapped(true);

        $this->handleTelemetryResponse();

        /**
         * The end of the application
         *
         * @param leantime\core\Bootloader $bootloader The bootloader object.
         */
        self::dispatch_event("end", ['bootloader' => $this]);
    }

    /**
     * Get the Application instance.
     *
     * @return \leantime\core\application
     */
    public function getApplication(): application
    {
        $this->app ??= application::getInstance();

        // app aliases
        $this->app->alias('app', application::class);
        $this->app->alias('app', IlluminateContainerContract::class);
        $this->app->alias('app', PsrContainerContract::class);

        // specify singletons
        $this->app->instance(environment::class, $this->app->make(environment::class));
        $this->app->instance(db::class, $this->app->make(db::class));
        $this->app->instance(frontcontroller::class, $this->app->make(frontcontroller::class));
        $this->app->instance(session::class, $this->app->make(session::class));
        $this->app->instance(language::class, $this->app->make(language::class));
        $this->app->instance(services\auth::class, $this->app->make(services\auth::class));
        $this->app->instance(services\oidc::class, $this->app->make(services\oidc::class));
        $this->app->instance(services\modulemanager::class, $this->app->make(services\modulemanager::class));

        return $this->app;
    }

    /**
     * Load headers
     *
     * @return void
     */
    private function loadHeaders(): void
    {
        $headers = self::dispatch_filter('headers', [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'X-Content-Type-Options' => 'nosniff',
            'Access-Control-Allow-Origin' => BASE_URL
        ]);

        foreach ($headers as $key => $value) {
            header($key . ': ' . $value);
        }
    }

    /**
     * Check if Leantime is installed
     *
     * @return bool
     */
    private function checkIfInstalled(): bool
    {
        $session_says = isset($_SESSION['isInstalled']) && $_SESSION['isInstalled'];
        $config_says = $this->app->make(repositories\setting::class)->checkIfInstalled();
        $frontController = $this->app->make(frontcontroller::class);

        if (! $session_says && $config_says) {
            $this->setInstalled();
            return true;
        }

        if (! $session_says && ! $config_says) {
            $this->setUninstalled();
            $this->redirectToInstall();
            return false;
        }

        if ($session_says && ! $config_says) {
            $this->setUninstalled();
            $this->redirectToInstall();
            return false;
        }

        return $session_says && $config_says;
    }

    /**
     * Set installed
     *
     * @return void
     */
    private function setInstalled(): void
    {
        $_SESSION['isInstalled'] = true;
    }

    private function setUninstalled(): void
    {
        $_SESSION['isInstalled'] = false;

        if (isset($_SESSION['userdata'])) {
            unset($_SESSION['userdata']);
        }
    }

    private function redirectToInstall(): void
    {
        $frontController = $this->app->make(frontcontroller::class);

        if (! in_array($frontController::getCurrentRoute(), ['install', 'api.i18n'])) {
            $frontController::redirect(BASE_URL . '/install');
        }
    }

    private function checkIfUpdated(): bool
    {
        $dbVersion = $this->app->make(repositories\setting::class)->getSetting('db-version');
        $settingsDbVersion = $this->app->make(appSettings::class)->dbVersion;

        if ($dbVersion == $settingsDbVersion) {
            $_SESSION['isUpdated'] = true;
            return true;
        }

        if (! isset($_GET['update']) && ! isset($_GET['install'])) {
            $this->redirectToInstall();
        }

        return false;
    }

    /**
     * Handle the request
     *
     * @todo Refactor into middleware and then dispatch
     * @return void
     */
    private function handleRequest(): void
    {
        $frontController = $this->app->make(frontcontroller::class);
        $incomingRequest = $this->app->make(IncomingRequest::class);

        // handle public request
        if (in_array($frontController->getCurrentRoute(), $this->publicActions)) {
            $frontController::dispatch();
            return;
        }

        // handle API request
        if ($incomingRequest->hasAPIKey()) {
            $apiKey = $incomingRequest->getAPIKey();
            $apiUser = $this->app->make(services\api::class)->getAPIKeyUser($apiKey);

            if (! $apiUser) {
                echo json_encode(['error' => 'Invalid API Key']);
                exit();
            }

            $this->app->make(services\auth::class)->setUserSession($apiUser);
            $this->app->make(services\projects::class)->setCurrentProject();

            if (! str_starts_with(strtolower($frontController->getCurrentRoute()), 'api.jsonrpc')) {
                echo json_encode(['error' => 'API endpoint not valid']);
                exit();
            }

            $frontController::dispatch();
            return;
        }

        // handle unathorized requests
        if (! $this->app->make(services\auth::class)->logged_in()) {
            $this->redirectWithOrigin('auth.login', $incomingRequest->getRequestURI(BASE_URL));
            return;
        }

        // Check if trying to access twoFA code page, or if trying to access any other action without verifying the code.
        if ($_SESSION['userdata']['twoFAEnabled'] && ! $_SESSION['userdata']['twoFAVerified']) {
            $this->redirectWithOrigin('twoFA.verify', $_GET['redirect'] ?? '');
            return;
        }

        // handle authorized requests
        $this->cronExec();

        //Send telemetry if user is opt in and if it hasn't been sent that day
        $this->telemetryResponse = $this->app->make(services\reports::class)->sendAnonymousTelemetry();

        $this->app->make(services\projects::class)->setCurrentProject();

        self::dispatch_event("logged_in", ['application' => $this]);

        $frontController::dispatch();
    }

    /**
     * Redirect with origin
     *
     * @param string $route
     * @param string $origin
     * @return void
     */
    public function redirectWithOrigin($route, $origin): void
    {
        $redirectURL = '';
        if (strlen($origin) > 1) {
            $redirectURL = "?redirect=" . urlencode($origin);
        }

        $frontController = $this->app->make(frontcontroller::class);

        if ($frontController::getCurrentRoute() !== $route) {
            $frontController::redirect(BASE_URL . "/" . str_replace(".", "/", $route) . "" . $redirectURL);
        }
    }

    /**
     * Cron exec
     *
     * @return void
     */
    private function cronExec(): void
    {
        $audit = $this->app->make(repositories\audit::class);

        $lastCronEvent = $_SESSION['last_cron_call'] ?? null;

        if (! isset($lastCronEvent)) {
            $lastEvent = $audit->getLastEvent('cron');
            $lastCronEvent = isset($lastEvent['date']) ? strtotime($lastEvent['date']) : 0;
        }

        // Using audit system to prevent too frequent cron executions
        $nowDate = time();
        $timeSince = abs($nowDate - $lastCronEvent);
        $cron_exec_increment = self::dispatch_filter('increment', 300); //Run every 5 min

        if ($timeSince < $cron_exec_increment) {
            unset($_SESSION['do_cron']);
            return;
        }

        $_SESSION['do_cron'] = true;
        $_SESSION['last_cron_call'] = time();
    }

    /**
     * Handle telemetry response
     *
     * @return void
     */
    private function handleTelemetryResponse(): void
    {
        if (! isset($this->telemetryResponse) || ! $this->telemetryResponse) {
            return;
        }

        try {
            $this->telemetryResponse->wait();
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
