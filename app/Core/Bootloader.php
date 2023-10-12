<?php

namespace Leantime\Core;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container as IlluminateContainerContract;
use Psr\Container\ContainerInterface as PsrContainerContract;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Leantime\Domain\Modulemanager\Services\Modulemanager as ModulemanagerService;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Audit\Repositories\Audit as AuditRepository;
use Symfony\Component\ErrorHandler\Debug;
use Illuminate\Support\Facades\Facade;

/**
 * Bootloader
 *
 * @package leantime
 * @subpackage core
 */
class Bootloader
{
    use Eventhelpers;

    /**
     * Bootloader instance
     *
     * @var static
     */
    protected static Bootloader $instance;

    /**
     * Application instance
     *
     * @var Application|PsrContainerContract|null
     */
    protected Application|null|PsrContainerContract $app;

    /**
     * Public actions
     *
     * @var array
     */
    private array $publicActions = array(
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
     * Telemetry response
     *
     * @var bool|PromiseInterface
     */
    private bool|PromiseInterface $telemetryResponse;

    /**
     * Set the Bootloader instance
     *
     * @param Bootloader|null $instance
     * @return void
     */
    public static function setInstance(?self $instance): void
    {
        static::$instance = $instance;
    }

    /**
     * Get the Bootloader instance
     *
     * @param PsrContainerContract|null $app
     * @return Bootloader
     */
    public static function getInstance(?PsrContainerContract $app = null): self
    {
        return static::$instance ??= new static($app);
    }

    /**
     * Constructor
     *
     * @param PsrContainerContract|null $app
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
     * @throws BindingResolutionException
     */
    public function __invoke(): void
    {
        $this->boot();
    }

    /**
     * Boot the Application.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if (! defined('LEANTIME_START')) {
            define('LEANTIME_START', microtime(true));
        }

        $app = $this->getApplication();

        if ($app::hasBeenBootstrapped()) {
            return;
        }

        $config = $app->make(Environment::class);

        $this->setErrorHandler($config->debug ?? 0);



        $request = $app->make(IncomingRequest::class);

        if (! defined('BASE_URL')) {
            if (isset($config->appUrl) && !empty($config->appUrl)) {
                define('BASE_URL', $config->appUrl);
            } else {
                define('BASE_URL', $request->getSchemeAndHttpHost());
            }
        }

        if (! defined('CURRENT_URL')) {
            define('CURRENT_URL', BASE_URL . $request->getRequestUri());
        }

        $this->loadHeaders();

        $this->checkIfInstalled();

        self::dispatch_event('after_install');

        $this->checkIfUpdated();


        self::dispatch_event("beginning", ['bootloader' => $this]);

        $this->handleRequest();

        $app::setHasBeenBootstrapped();

        $this->handleTelemetryResponse();

        self::dispatch_event("end", ['bootloader' => $this]);
    }

    /**
     * Get the Application instance.
     *
     * @return Application
     * @throws BindingResolutionException
     */
    public function getApplication(): Application
    {
        $this->app ??= Application::getInstance();

        // attach container to container
        $this->app->bind(Application::class, fn () => Application::getInstance());
        $this->app->alias(Application::class, IlluminateContainerContract::class);
        $this->app->alias(Application::class, PsrContainerContract::class);

        Facade::setFacadeApplication($this->app);

        $this->bindRequest();

        // Setup Configuration
        $this->app->singleton(Environment::class, Environment::class);
        $this->app->alias(Environment::class, \Illuminate\Contracts\Config\Repository::class);
        $this->app->alias(Environment::class, alias: "config");

        $this->app->singleton(AppSettings::class, AppSettings::class);
        $this->app->make(AppSettings::class)->loadSettings();

        Events::discover_listeners();

        self::dispatch_event('config_initialized');

        // specify singletons/instances
        $this->app->singleton(Db::class, Db::class);
        $this->app->singleton(Frontcontroller::class, Frontcontroller::class);
        $this->app->instance(Session::class, $this->app->make(Session::class));

        self::dispatch_event('session_initialized');

        $this->app->singleton(Language::class, Language::class);
        $this->app->singleton(AuthService::class, AuthService::class);
        $this->app->singleton(OidcService::class, OidcService::class);
        $this->app->singleton(ModulemanagerService::class, ModulemanagerService::class);

        $this->app = self::dispatch_filter("initialized", $this->app, ['bootloader' => $this]);

        Application::setInstance($this->app);

        return $this->app;
    }

    /**
     * Load headers
     *
     * @return void
     * @throws BindingResolutionException
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
     * @return bool
     * @throws BindingResolutionException
     */
    private function checkIfInstalled(): bool
    {
        $session_says = isset($_SESSION['isInstalled']) && $_SESSION['isInstalled'];
        $config_says = $this->app->make(SettingRepository::class)->checkIfInstalled();
        $frontController = $this->app->make(Frontcontroller::class);

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
     * @throws BindingResolutionException
     */
    private function redirectToInstall(): void
    {
        $frontController = $this->app->make(Frontcontroller::class);

        if (! in_array($frontController::getCurrentRoute(), ['install', 'api.i18n'])) {
            $frontController::redirect(BASE_URL . '/install');
        }
    }

    /**
     * Redirect to update
     *
     * @return void
     * @throws BindingResolutionException
     */
    private function redirectToUpdate(): void
    {
        $frontController = $this->app->make(Frontcontroller::class);

        if (! in_array($frontController::getCurrentRoute(), ['install.update', 'api.i18n'])) {
            $frontController::redirect(BASE_URL . '/install/update');
        }
    }

    /**
     * Check if Leantime is updated
     *
     * @return bool
     * @throws BindingResolutionException
     */
    private function checkIfUpdated(): bool
    {
        $dbVersion = $this->app->make(SettingRepository::class)->getSetting('db-version');
        $settingsDbVersion = $this->app->make(AppSettings::class)->dbVersion;

        $_SESSION['isUpdated'] = $dbVersion == $settingsDbVersion;

        self::dispatch_event('system_update', ['dbVersion' => $dbVersion, 'settingsDbVersion' => $settingsDbVersion]);

        if ($_SESSION['isUpdated']) {
            return true;
        }

        if (! isset($_GET['act']) || ($_GET['act'] !== "install" && $_GET['act'] !== "install.update")) {
            $this->redirectToUpdate();
        }

        return false;
    }

    /**
     * Handle the request
     *
     * @return void
     * @throws BindingResolutionException
     * @todo Refactor into middleware and then dispatch
     */
    private function handleRequest(): void
    {
        $frontController = $this->app->make(Frontcontroller::class);
        $incomingRequest = $this->app->make(IncomingRequest::class);

        $this->publicActions = self::dispatch_filter("publicActions", $this->publicActions, ['bootloader' => $this]);

        // handle public request
        if (in_array($frontController::getCurrentRoute(), $this->publicActions)) {
            $frontController::dispatch();
            return;
        }

        // handle API request
        if ($incomingRequest instanceof ApiRequest) {
            self::dispatch_event("before_api_request", ['application' => $this]);

            $apiKey = $incomingRequest->getAPIKey();
            $apiUser = $this->app->make(ApiService::class)->getAPIKeyUser($apiKey);

            if (! $apiUser) {
                echo json_encode(['error' => 'Invalid API Key']);
                exit();
            }

            $this->app->make(AuthService::class)->setUserSession($apiUser);
            $this->app->make(ProjectService::class)->setCurrentProject();

            if (! str_starts_with(strtolower($frontController->getCurrentRoute()), 'api.jsonrpc')) {
                echo json_encode(['error' => 'API endpoint not valid']);
                exit();
            }

            $frontController::dispatch();
            return;
        }

        // handle unathorized requests
        if (! $this->app->make(AuthService::class)->logged_in()) {
            $this->redirectWithOrigin('auth.login', $incomingRequest->getRequestUri());
            return;
        }

        // Check if trying to access twoFA code page, or if trying to access any other action without verifying the code.
        if ($_SESSION['userdata']['twoFAEnabled'] && ! $_SESSION['userdata']['twoFAVerified']) {
            $this->redirectWithOrigin('twoFA.verify', $_GET['redirect'] ?? '');
        }

        // handle authorized requests
        $this->cronExec();

        //Send telemetry if user is opt in and if it hasn't been sent that day
        $this->telemetryResponse = $this->app->make(ReportService::class)->sendAnonymousTelemetry();

        $this->app->make(ProjectService::class)->setCurrentProject();

        self::dispatch_event("logged_in", ['application' => $this]);

        $frontController::dispatch();
    }

    /**
     * Redirect with origin
     *
     * @param string $route
     * @param string $origin
     * @return void
     * @throws BindingResolutionException
     */
    public function redirectWithOrigin(string $route, string $origin): void
    {
        $destination = BASE_URL . '/' . ltrim(str_replace('.', '/', $route), '/');
        $queryParams = !empty($origin)  && $origin !== '/' ? '?' . http_build_query(['redirect' => $origin]) : '';
        $frontController = $this->app->make(Frontcontroller::class);

        if ($frontController::getCurrentRoute() == $route) {
            return;
        }

        $frontController::redirect($destination . $queryParams);
    }

    /**
     * Cron exec
     *
     * @return void
     * @throws BindingResolutionException
     */
    private function cronExec(): void
    {
        $audit = $this->app->make(AuditRepository::class);

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
        } catch (Exception $e) {
            error_log($e);
        }
    }

    /**
     * @param int $debug
     * @return void
     */
    private function setErrorHandler(int $debug): void
    {
        if ($debug == 0) {
            return;
        }

        Debug::enable();
    }

    /**
     * Bind request
     *
     * @return void
     */
    private function bindRequest(): void
    {
        $headers = array_map(
            fn ($val) => in_array($val, ['false', 'true'])
                ? filter_var($val, FILTER_VALIDATE_BOOLEAN)
                : $val,
            getallheaders()
        );

        if (isset($headers['Hx-Request'])) {
            $incomingRequest = $this->app->instance(IncomingRequest::class, HtmxRequest::createFromGlobals());
        } elseif (isset($headers['X-Api-Key'])) {
            $incomingRequest = $this->app->instance(IncomingRequest::class, ApiRequest::createFromGlobals());
        } else {
            $incomingRequest = $this->app->instance(IncomingRequest::class, IncomingRequest::createFromGlobals());
        }

        $incomingRequest->overrideGlobals();
    }
}
