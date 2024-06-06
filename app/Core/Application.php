<?php

namespace Leantime\Core;

use Illuminate\Cache\MemcachedConnector;
use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Container\Container as IlluminateContainerContract;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Modulemanager\Services\Modulemanager as ModulemanagerService;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Psr\Container\ContainerInterface as PsrContainerContract;

/**
 * Application Class - IoC Container for the application
 *
 * @package    leantime
 * @subpackage core
 */
class Application extends Container
{
    /**
     * Application bootstrap status
     *
     * @var bool
     */
    private static bool $bootstrapped = false;


    public function __construct(){

        $this->registerCoreBindings();
        $this->registerCoreAliases();
        $this->bindRequest();

        Facade::setFacadeApplication($this);

    }
    /**
     * Check if application has been bootstrapped
     *
     * @return bool
     */
    public static function hasBeenBootstrapped(): bool
    {
        return self::$bootstrapped;
    }

    /**
     * Set the application as having been bootstrapped
     *
     * @return Application
     */
    public static function setHasBeenBootstrapped(): self
    {
        self::$bootstrapped = true;

        return self::getInstance();
    }

    /**
     * Get the application namespace
     *
     * @param bool $includeSuffix
     * @return string
     *
     * @see \Illuminate\Contracts\Foundation\Application::getNamespace()
     */
    public function getNamespace(bool $includeSuffix = true): string
    {
        static $namespace;

        if (! $namespace) {
            $namespace = substr(__NAMESPACE__, 0, strpos(__NAMESPACE__, '\\') + 1);
        }

        return ! $includeSuffix ? rtrim($namespace, '\\') : $namespace;
    }

    /**
     * Checks whether the application is down for maintenance
     * @return bool
     * @todo should return true if application is updating to a new version
     **/
    public function isDownForMaintenance()
    {
        return false;
    }

    /**
     * Gets the current environment
     * @return string
     * @todo implement, should be set in env
     **/
    public function environment()
    {
        return 'production';
    }

    /**
     * Gets the base path of the application
     *
     * @return string
     **/
    public function basePath()
    {
        return APP_ROOT;
    }

    protected function registerCoreBindings(): void
    {
        static::setInstance($this);

        $this->singleton(Application::class, fn() => Application::getInstance());

        $this->singleton(Environment::class, Environment::class);

        $this->singleton(AppSettings::class, AppSettings::class);
        $this->singleton(Db::class, Db::class);
        $this->singleton(Frontcontroller::class, Frontcontroller::class);
        $this->singleton(Language::class, Language::class);
        $this->singleton(AuthService::class, AuthService::class);
        $this->singleton(OidcService::class, OidcService::class);
        $this->singleton(ModulemanagerService::class, ModulemanagerService::class);
        $this->singleton(\Illuminate\Filesystem\Filesystem::class, fn () => new \Illuminate\Filesystem\Filesystem());
        $this->singleton(\Illuminate\Encryption\Encrypter::class, function ($app) {
            $configKey = $app['config']->sessionPassword;

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

        $this->singleton(\Illuminate\Session\SessionManager::class, function ($app) {

            $app['config']['session'] = array(
                'driver' => "file",
                'lifetime' =>  $app['config']->sessionExpiration,
                'expire_on_close' => false,
                'encrypt' => false,
                'files' => APP_ROOT . '/cache/sessions',
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

        $this->singleton('session.store', fn($app) => $app['session']->driver());


        /**
         * @todo the following should eventually automatically turn caches into redis if available,
         *  then memcached if available,
         *  then fileStore
         */
        $this->singleton(\Illuminate\Cache\CacheManager::class, function ($app) {

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
        $this->singleton('cache.store', fn ($app) => $app['cache']->driver());
        $this->singleton('cache.psr6', fn ($app) => new \Symfony\Component\Cache\Adapter\Psr16Adapter($app['cache.store']));
        $this->singleton('memcached.connector', fn () => new MemcachedConnector());

    }

    /**
     * Configure the real-time facade namespace.
     *
     * @param  string  $namespace
     * @return void
     */
    public function provideFacades($namespace)
    {

    }

    private function registerCoreAliases(): void
    {

        $this->alias(Application::class, 'app');
        $this->alias(Application::class, IlluminateContainerContract::class);
        $this->alias(Application::class, PsrContainerContract::class);
        $this->alias(Application::class, Container::class);

        $this->alias(Environment::class, 'config');
        $this->alias(Environment::class, \Illuminate\Contracts\Config\Repository::class);

        $this->alias(\Illuminate\Filesystem\Filesystem::class, 'files');
        $this->alias(ConsoleKernel::class, ConsoleKernelContract::class);
        $this->alias(HttpKernel::class, HttpKernelContract::class);

        $this->alias(\Illuminate\Cache\CacheManager::class, 'cache');
        $this->alias(\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class);

        $this->alias(\Illuminate\Session\SessionManager::class, 'session');


        $this->alias(\Illuminate\Encryption\Encrypter::class, "encrypter");

    }


    public function clearCache(): void
    {

        $currentVersion = $this->app->make(AppSettings::class)->appVersion;
        $cachedVersion = Cache::store('installation')->rememberForever('version', fn () => $currentVersion);

        if ($currentVersion == $cachedVersion) {
            return;
        }

        Cache::store('installation')->flush();

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

        $this->singleton(IncomingRequest::class, function () use ($headers) {

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
