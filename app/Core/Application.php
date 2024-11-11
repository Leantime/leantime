<?php

namespace Leantime\Core;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Foundation\Mix;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Routing\RoutingServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Console\ConsoleKernel;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\HttpKernel;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Providers\Events;
use Leantime\Core\Providers\Logging;


/**
 * Class Application
 *
 * Represents an application.
 */
class Application extends \Illuminate\Foundation\Application
{
    use DispatchesEvents;

    /**
     * Application bootstrap status
     *
     * @var bool
     */
    private static bool $bootstrapped = false;

    /**
     * Constructor for the class.
     *
     * @param  string  $basePath  The base path for the application.
     * @return void
     */
    public function __construct($basePath = null)
    {

        $this->publicPath = 'public/';
        $this->namespace = 'Leantime\\';

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->appPath = $basePath."/app/Core";

        //Larevel stores cache in bootstrap folder
        //Cache files are in root in Leantime not in bootstrap
        //Env vars are not available in config

        //putenv('APP_EVENTS_CACHE=cache/events.php');
        //putenv('APP_CONFIG_CACHE=cache/config.php');
        //putenv('APP_ROUTES_CACHE=cache/routes.php');
        //putenv('APP_SERVICES_CACHE=cache/services.php');
        //putenv('APP_PACKAGES_CACHE=cache/packages.php');

        //Our folder structure is different and we shall not bow to the bourgeoisie
        $this->useAppPath($this->basePath.'/app');
        $this->useConfigPath($this->basePath.'/config');
        $this->useEnvironmentPath($this->basePath.'/config');
        $this->useBootstrapPath($this->basePath.'/bootstrap');
        $this->usePublicPath($this->basePath.'/public');
        $this->useStoragePath($this->basePath.'/storage');
        $this->useLangPath($this->basePath.'/app/Language');

        $this->registerBaseBindings();

        //Loading some config vars so we can run events
        //$this->register(new \Leantime\Core\Providers\Environment($this));

        $this->registerBaseServiceProviders();
        //$this->registerCoreContainerAliases();

        //Overriding some of the aliases
        $this->registerLeantimeAliases();

    }

    /**
     * Register the base service providers.
     *
     * This method is used to register the base service providers required for the application.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        //Loading some config vars so we can run events
        //$this->register(new \Leantime\Core\Providers\Environment($this));

        $this->register(new \Leantime\Core\Providers\Events($this));
        $this->register(new LogServiceProvider($this));
        $this->register(new RoutingServiceProvider($this));

        //Todo: Add event and see if that works here.

    }

    public function registerLeantimeAliases()
    {

        foreach ([
            'app' => [self::class, \Illuminate\Foundation\Application::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class, \Psr\Container\ContainerInterface::class],
            'cache' => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
            'cache.store' => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class, \Psr\SimpleCache\CacheInterface::class],
            'cache.psr6' => [\Symfony\Component\Cache\Adapter\Psr16Adapter::class, \Symfony\Component\Cache\Adapter\AdapterInterface::class, \Psr\Cache\CacheItemPoolInterface::class],
            'config' => [Environment::class, \Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
            'encrypter' => [\Illuminate\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\StringEncrypter::class],
            'events' => [\Leantime\Core\Events\EventDispatcher::class, \Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
            'request' => [IncomingRequest::class, ApiRequest::class, Console\CliRequest::class, \Illuminate\Http\Request::class,  \Symfony\Component\HttpFoundation\Request::class],
            'redis' => [\Illuminate\Redis\RedisManager::class, \Illuminate\Contracts\Redis\Factory::class],
            'redis.connection' => [\Illuminate\Redis\Connections\Connection::class, \Illuminate\Contracts\Redis\Connection::class],
            'session' => [\Illuminate\Session\SessionManager::class],
            'session.store' => [\Illuminate\Session\Store::class, \Illuminate\Contracts\Session\Session::class],
            'router' => [Router::class, \Illuminate\Contracts\Routing\Registrar::class, \Illuminate\Contracts\Routing\BindingRegistrar::class],
            'view' => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }

        $this->alias(Application::class, \Illuminate\Contracts\Foundation\Application::class);

        //$this->alias(DispatchesEvents::class, 'events');
        //$this->alias(Environment::class, 'config');
    }

    //Boot with Leantime event dispatcher


    /**
     * Boot the application.
     *
     * @return void
     */
    public function boot()
    {

        //We need to discover events a lot earlier than Laravel wants us to.
        //So we're just doing it.
        \Illuminate\Support\Facades\Event::discoverListeners();

        //Calling the first event
        self::dispatchEvent('beforeBootingServiceProviders');

        parent::boot();

        self::dispatchEvent('afterBootingServiceProviders');

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
}
