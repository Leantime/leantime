<?php

namespace leantime\core;

use Illuminate\Contracts\Container\Container as IlluminateContainerContract;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandlerContract;
use Psr\Container\ContainerInterface as PsrContainerContract;
use Symfony\Component\ErrorHandler\Debug as SymfonyDebug;
use leantime\domain\services;
use leantime\domain\repositories;

/**
 * Bootloader
 *
 * @package leantime
 * @subpackage core
 */
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
        "cron.run",
    );

    /**
     * Set the Bootloader instance
     *
     * @param \leantime\core\Bootloader $instance
     * @return void
     */
    public static function setInstance(?self $instance): void
    {
        static::$instance = $instance;
    }

    /**
     * Get the Bootloader instance
     *
     * @param ?\Psr\Container\ContainerInterface $app
     * @return \leantime\core\Bootloader
     */
    public static function getInstance(?PsrContainerContract $app = null): self
    {
        return static::$instance ??= new static($app);
    }

    /**
     * Constructor
     *
     * @param ?\Psr\Container\ContainerInterface $app
     * @return self
     */
    public function __construct(?PsrContainerContract $app = null)
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

        $config = $app->make(environment::class);

        $this->setErrorHandler($config->debug ?? 0);

        $app->make(appSettings::class)->loadSettings();

        $request = $app->make(IncomingRequest::class);

        if (! defined('BASE_URL')) {
            define('BASE_URL', $config->appUrl ?? $request->getSchemeAndHttpHost());
        }

        if (! defined('CURRENT_URL')) {
            define('CURRENT_URL', !empty($config->appUrl)
            ? $config->appUrl . $request->getRequestUri()
            : $request->getFullUrl());
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

        $this->bindRequest();

        // specify singletons
        $this->app->singleton(PsrContainerContract::class, application::class);
        $this->app->singleton(environment::class, environment::class);
        $this->app->singleton(db::class, db::class);
        $this->app->singleton(frontcontroller::class, frontcontroller::class);
        $this->app->instance(session::class, $this->app->make(session::class));
        $this->app->singleton(language::class, language::class);
        $this->app->singleton(services\auth::class, services\auth::class);
        $this->app->singleton(services\oidc::class, services\oidc::class);
        $this->app->singleton(services\modulemanager::class, services\modulemanager::class);

        // point contracts to container
        $this->app->alias(IlluminateContainerContract::class, PsrContainerContract::class);
        $this->app->alias(application::class, PsrContainerContract::class);

        /**
         * Filter on container right after initial bindings.
         *
         * @param leantime\core\Bootloader $bootloader The bootloader object.
         * @return \Illuminate\Contracts\Container\Container $container The container object.
         */
        $this->app = self::dispatch_filter("initialized", $this->app, ['bootloader' => $this]);

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
            'Access-Control-Allow-Origin' => BASE_URL,
        ]);

        foreach ($headers as $key => $value) {
            header($key . ': ' . $value);
        }
    }

    /**
     * Check if Leantime is installed
     *
     * @return boolean
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

    /**
     * Set uninstalled
     *
     * @return void
     */
    private function setUninstalled(): void
    {
        $_SESSION['isInstalled'] = false;

        if (isset($_SESSION['userdata'])) {
            unset($_SESSION['userdata']);
        }
    }

    /**
     * Redirect to install
     *
     * @return void
     */
    private function redirectToInstall(): void
    {
        $frontController = $this->app->make(frontcontroller::class);

        if (! in_array($frontController::getCurrentRoute(), ['install', 'api.i18n'])) {
            $frontController::redirect(BASE_URL . '/install');
        }
    }

    /**
     * Check if Leantime is updated
     *
     * @return boolean
     */
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
        if (in_array($frontController::getCurrentRoute(), $this->publicActions)) {
            $frontController::dispatch();
            return;
        }

        // handle API request
        if ($incomingRequest instanceof ApiRequest) {
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

        // REMOVE THIS
        $username = $incomingRequest->headers->get('username');
        $password = $incomingRequest->headers->get('password');

        if (! empty($username) && ! empty($password)) {
            $loggedIn = $this->app->make(services\auth::class)->login($username, $password);
        }
        // END REMOVE THIS

        // handle unathorized requests
        if (! $this->app->make(services\auth::class)->logged_in()) {
            $this->redirectWithOrigin('auth.login', $incomingRequest->getRequestUri());
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
    public function redirectWithOrigin(string $route, string $origin): void
    {
        $destination = BASE_URL . '/' . ltrim(str_replace('.', '/', $route), '/');
        $queryParams = !empty($origin) ? '?' . http_build_query(['redirect' => $origin]) : '';
        $frontController = $this->app->make(frontcontroller::class);

        if ($frontController::getCurrentRoute() == $route) {
            return;
        }

        $frontController::redirect($destination . $queryParams);
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

    private function setErrorHandler(int $debug): void
    {
        if ($debug == 0) {
            return;
        }

        SymfonyDebug::enable();
    }

    /**
     * Bind request
     *
     * @return void
     */
    private function bindRequest(): void
    {
        $incomingRequest = IncomingRequest::createFromGlobals();

        $incomingRequest = $this->app->instance(IncomingRequest::class, match (true) {
            $incomingRequest->isHtmx() => HtmxRequest::createFromGlobals(),
            $incomingRequest->hasApiKey() => ApiRequest::createFromGlobals(),
            default => $incomingRequest,
        });

        $incomingRequest->overrideGlobals();
    }
}
