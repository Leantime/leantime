<?php

namespace Leantime\Core;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container as IlluminateContainerContract;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Redis\RedisManager;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Modulemanager\Services\Modulemanager as ModulemanagerService;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Psr\Container\ContainerInterface as PsrContainerContract;
use Symfony\Component\ErrorHandler\Debug;

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
        "errors.error403",
        "errors.error404",
        "errors.error500",
        "errors.error501",
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
        return static::$instance ??= new self($app);
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
     * Execute the Application lifecycle.
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

        $app->make(AppSettings::class)->loadSettings();

        $this->clearCache();

        Events::discover_listeners();

        $app = self::dispatch_filter("initialized", $app, ['bootloader' => $this]);

        $config = $app->make(Environment::class);

        $this->setErrorHandler($config->debug ?? 0);

        self::dispatch_event('config_initialized');


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

        self::dispatch_event("beginning", ['bootloader' => $this]);

        if ($app::hasBeenBootstrapped()) {
            return;
        }

        $time = microtime(true) - LEANTIME_START;

        $this->handleRequest();

        $app::setHasBeenBootstrapped();

        self::dispatch_event("end", ['bootloader' => $this]);
    }

    /**
     * Get the Application instance and bind important services
     *
     * @return Application
     * @throws BindingResolutionException
     * @todo Break this up into Service Providers
     */
    public function getApplication(): Application
    {
        $this->app ??= Application::getInstance();

        $this->registerCoreBindings();
        $this->registerCoreAliases();
        $this->bindRequest();

        Facade::setFacadeApplication($this->app);

        Application::setInstance($this->app);

        return $this->app;
    }

    private function registerCoreBindings(): void
    {
        $this->app->bind(Application::class, fn () => Application::getInstance());
        $this->app->singleton(Environment::class, Environment::class);
        $this->app->singleton(AppSettings::class, AppSettings::class);
        $this->app->singleton(Db::class, Db::class);
        $this->app->singleton(Frontcontroller::class, Frontcontroller::class);
        $this->app->singleton(Language::class, Language::class);
        $this->app->singleton(AuthService::class, AuthService::class);
        $this->app->singleton(OidcService::class, OidcService::class);
        $this->app->singleton(ModulemanagerService::class, ModulemanagerService::class);
        $this->app->singleton(\Illuminate\Filesystem\Filesystem::class, fn () => new \Illuminate\Filesystem\Filesystem());
        $this->app->singleton(\Illuminate\Encryption\Encrypter::class, function ($app) {
            $configKey = app()->make(Environment::class)->sessionPassword;

            if (strlen($configKey) > 32) {
                $configKey = substr($configKey, 0, 32);
            }

            if (strlen($configKey) < 32) {
                $configKey =  str_pad($configKey, 32, "x", STR_PAD_BOTH);
            }

            $app['config']['app_key'] = $configKey;

            $encrypter = new \Illuminate\Encryption\Encrypter($app['config']['app_key'], "AES-256-CBC");
            return $encrypter;
        });

        $this->app->singleton(\Illuminate\Session\SessionManager::class, function ($app) {

            $app['config']['session'] = array(
                'driver' => "file",
                'connection' => 'default',
                'lifetime' =>  $app['config']->sessionExpiration,
                'expire_on_close' => false,
                'encrypt' => true,
                'files' => APP_ROOT . '/cache/sessions',
                'store' => null,
                'lottery' => [2, 100],
                'cookie' => "ltid",
                'path' => '/',
                'domain' => is_array(parse_url(BASE_URL)) ? parse_url(BASE_URL)['host'] : null,
                'secure' => true,
                'http_only' => true,
                'same_site' => "Strict",
            );

            $sessionManager = new \Illuminate\Session\SessionManager($app);

            return $sessionManager;
        });

        $this->app->singleton('session.store', fn($app) => $app['session']->driver());



        /**
         * @todo the following should eventually automatically turn caches into redis if available,
         *  then memcached if available,
         *  then fileStore
         **/
        $this->app->singleton(\Illuminate\Cache\CacheManager::class, function ($app) {

            //installation cache is per server
            $app['config']['cache.stores.installation'] = [
                'driver' => 'file',
                'connection' => 'default',
                'path' => APP_ROOT . '/cache/installation',
            ];

            //Instance is per company id
            $instanceStore = fn () =>
                $app['config']['cache.stores.instance'] = [
                    'driver' => 'file',
                    'connection' => 'default',
                    'path' => APP_ROOT . "/cache/" . $app->make(SettingsService::class)->getCompanyId(),
                ];

            if ($app->make(IncomingRequest::class) instanceof CliRequest) {
                if (empty($app->make(SettingsService::class)->getCompanyId())) {
                    throw new \RuntimeException('You can\'t run this CLI command until you have installed Leantime.');
                }

                $instanceStore();
            } else {
                //Initialize instance cache store only after install was successfull
                Events::add_event_listener(
                    'leantime.core.middleware.installed.handle.after_install',
                    function () use ($instanceStore) {
                        if (! session("isInstalled")) {
                            return;
                        }
                        $instanceStore();
                    }
                );
            }

            $cacheManager = new \Illuminate\Cache\CacheManager($app);
            //Setting the default does not mean that is exists already.
            //Installation store is always available
            //Instance store is only available post after_install event
            $cacheManager->setDefaultDriver('instance');

            return $cacheManager;
        });
        $this->app->singleton('cache.store', fn ($app) => $app['cache']->driver());
        $this->app->singleton('cache.psr6', fn ($app) => new \Symfony\Component\Cache\Adapter\Psr16Adapter($app['cache.store']));
        $this->app->singleton('memcached.connector', fn () => new MemcachedConnector());
    }

    private function registerCoreAliases(): void
    {
        $this->app->alias(Application::class, 'app');
        $this->app->alias(Application::class, IlluminateContainerContract::class);
        $this->app->alias(Application::class, PsrContainerContract::class);
        $this->app->alias(Environment::class, 'config');
        $this->app->alias(Environment::class, \Illuminate\Contracts\Config\Repository::class);
        $this->app->alias(\Illuminate\Filesystem\Filesystem::class, 'files');
        $this->app->alias(ConsoleKernel::class, ConsoleKernelContract::class);
        $this->app->alias(HttpKernel::class, HttpKernelContract::class);

        $this->app->alias(\Illuminate\Cache\CacheManager::class, 'cache');
        $this->app->alias(\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class);

        $this->app->alias(\Illuminate\Session\SessionManager::class, 'session');

        $this->app->alias(\Illuminate\Encryption\Encrypter::class, "encrypter");

    }


    private function clearCache(): void
    {
        $currentVersion = app()->make(AppSettings::class)->appVersion;
        $cachedVersion = Cache::store('installation')->rememberForever('version', fn () => $currentVersion);

        if ($currentVersion == $cachedVersion) {
            return;
        }

        Cache::store('installation')->flush();
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
        if (! ($request = $this->app->make(IncomingRequest::class)) instanceof CliRequest) {
            /** @var HttpKernel $kernel */
            $kernel = $this->app->make(HttpKernel::class);

            $response = $kernel->handle($request)->send();

            $kernel->terminate($request, $response);
        } else {
            /** @var ConsoleKernel $kernel */
            $kernel = $this->app->make(ConsoleKernel::class);

            $status = $kernel->handle(
                $input = new \Symfony\Component\Console\Input\ArgvInput(),
                new \Symfony\Component\Console\Output\ConsoleOutput()
            );

            $kernel->terminate($input, $status);

            exit($status);
        }
    }

    /**
     * @param int $debug
     * @return void
     */
    private function setErrorHandler(int $debug): void
    {
        $incomingRequest = app(IncomingRequest::class);
        app()->bind(\Illuminate\Contracts\Debug\ExceptionHandler::class, \Leantime\Core\ExceptionHandler::class);

        if (
            $debug == 0
            || $incomingRequest instanceof HtmxRequest
            || $incomingRequest instanceof ApiRequest
        ) {
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
        $headers = collect(getallheaders())
            ->mapWithKeys(fn ($val, $key) => [
                strtolower($key) => match (true) {
                    in_array($val, ['false', 'true']) => filter_var($val, FILTER_VALIDATE_BOOLEAN),
                    preg_match('/^[0-9]+$/', $val) => filter_var($val, FILTER_VALIDATE_INT),
                    default => $val,
                },
            ])
            ->all();

        $this->app->singleton(IncomingRequest::class, function () use ($headers) {
            $request = match (true) {
                isset($headers['hx-request']) => HtmxRequest::createFromGlobals(),
                isset($headers['x-api-key']) => ApiRequest::createFromGlobals(),
                defined('LEAN_CLI') && LEAN_CLI => CliRequest::createFromGlobals(),
                default => IncomingRequest::createFromGlobals(),
            };

            do_once('overrideGlobals', fn () => $request->overrideGlobals());

            return $request;
        });
    }
}
