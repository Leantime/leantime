<?php

namespace Leantime\Core\Bootstrap;

use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Container\Container as IlluminateContainerContract;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Console\CliRequest;
use Leantime\Core\Console\ConsoleKernel;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Exceptions\ExceptionHandler;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\HttpKernel;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Modulemanager\Services\Modulemanager as ModulemanagerService;
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

    /**
     * The deferred services and their providers.
     *
     * @var array
     */
    protected $deferredServices = [];

    /**
     * Array to store laravel service providers
     *
     * @var array
     */
    protected $serviceProviders = [];

    /**
     * Array to store already loaded providers
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The array of booting callbacks.
     *
     * @var callable[]
     */
    protected $bootingCallbacks = [];

    /**
     * The array of booted callbacks.
     *
     * @var callable[]
     */
    protected $bootedCallbacks = [];

    protected $isRunningInConsole = null;

    /**
     * Constructor method for the class.
     *
     * Initializes the object and sets up the required dependencies and bindings.
     * This method is automatically called when a new instance of the class is created.
     *
     * @return void
     */
    public function __construct()
    {

        $this->registerCoreBindings();
        $this->registerCoreAliases();
        $this->registerBaseServiceProviders();
        $this->bindRequest();
        $this->setUrlPathConstants();

        Facade::setFacadeApplication($this);

        EventDispatcher::discover_listeners();

        $this->boot();
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
     **/
    public function environment()
    {
        return config('env');
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

    /**
     * Register the core bindings for the application.
     *
     * This method sets the application instance, sets up singletons for some core classes,
     * and registers the ModulemanagerService as a singleton.
     *
     * @return void
     */
    protected function registerCoreBindings(): void
    {
        static::setInstance($this);

        $this->singleton(Application::class, fn() => Application::getInstance());

        $this->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, ExceptionHandler::class);

        $this->singleton(Frontcontroller::class, Frontcontroller::class);
        $this->singleton(\Illuminate\Filesystem\Filesystem::class, fn () => new \Illuminate\Filesystem\Filesystem());

        $this->singleton(ModulemanagerService::class, ModulemanagerService::class);
    }

    /**
     * Register core aliases for the application.
     *
     * @return void
     */
    private function registerCoreAliases(): void
    {

        $this->alias(Application::class, 'app');
        $this->alias(Application::class, IlluminateContainerContract::class);
        $this->alias(Application::class, PsrContainerContract::class);
        $this->alias(Application::class, Container::class);

        $this->alias(\Illuminate\Filesystem\Filesystem::class, 'files');
        $this->alias(ConsoleKernel::class, ConsoleKernelContract::class);
        $this->alias(HttpKernel::class, HttpKernelContract::class);

        $this->alias(ExceptionHandler::class, 'exceptions');


        $this->alias(\Illuminate\Encryption\Encrypter::class, "encrypter");

        $this->alias(\Leantime\Core\Events\EventDispatcher::class, 'events');
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {

        $this->register(new \Leantime\Core\Providers\Environment($this));

        $this->register(new \Leantime\Core\Providers\Logging($this));
        $this->register(new \Leantime\Core\Providers\Events($this));
        $this->register(new \Leantime\Core\Providers\Redis($this));

        $this->register(new \Leantime\Core\Providers\Cache($this));
        $this->register(new \Leantime\Core\Providers\Session($this));

        $this->register(new \Leantime\Core\Providers\Auth($this));

        $this->register(new \Leantime\Core\Providers\RateLimiter($this));
        $this->register(new \Leantime\Core\Providers\Db($this));
        $this->register(new \Leantime\Core\Providers\Language($this));

        $this->register(new \Leantime\Core\Providers\Theme($this));
    }

    /**
     * Set the URL path constants.
     *
     * @return void
     */
    protected function setUrlPathConstants()
    {

        $config = $this->make(Environment::class);
        $request = $this->make(IncomingRequest::class);

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
    }

    /**
     * Bind the incoming request to the application.
     *
     * This method parses the headers and creates an instance of the appropriate request class based on the headers.
     * The request class is then bound to the container as a singleton.
     *
     * @return void
     */
    public function bindRequest()
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

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string $provider
     * @param  bool                                       $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register(\Illuminate\Support\ServiceProvider|string $provider, bool $force = false)
    {
        if (($registered = $this->getProvider($provider)) && ! $force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        $provider->register();

        // If there are bindings / singletons set as properties on the provider we
        // will spin through them and register them with the application, which
        // serves as a convenience layer while registering a lot of bindings.
        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }

        if (property_exists($provider, 'singletons')) {
            foreach ($provider->singletons as $key => $value) {
                $key = is_int($key) ? $value : $key;

                $this->singleton($key, $value);
            }
        }

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->isBooted()) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param  \Illuminate\Support\ServiceProvider|string $provider
     * @return \Illuminate\Support\ServiceProvider|null
     */
    public function getProvider(\Illuminate\Support\ServiceProvider|string $provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return $this->serviceProviders[$name] ?? null;
    }

    /**
     * Get the registered service provider instances if any exist.
     *
     * @param  \Illuminate\Support\ServiceProvider|string $provider
     * @return array
     */
    public function getProviders(\Illuminate\Support\ServiceProvider|string $provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return Arr::where($this->serviceProviders, fn ($value) => $value instanceof $name);
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @param  string $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    public function resolveProvider(string $provider)
    {
        return new $provider($this);
    }

    /**
     * Mark the given provider as registered.
     *
     * @param  \Illuminate\Support\ServiceProvider $provider
     * @return void
     */
    protected function markAsRegistered(\Illuminate\Support\ServiceProvider $provider)
    {
        $class = get_class($provider);

        $this->serviceProviders[$class] = $provider;

        $this->loadedProviders[$class] = true;
    }

    /**
     * Load and boot all of the remaining deferred providers.
     *
     * @return void
     */
    public function loadDeferredProviders()
    {
        // We will simply spin through each of the deferred providers and register each
        // one and boot them if the application has booted. This should make each of
        // the remaining services available to this application for immediate use.
        foreach ($this->deferredServices as $service => $provider) {
            $this->loadDeferredProvider($service);
        }

        $this->deferredServices = [];
    }

    /**
     * Load the provider for a deferred service.
     *
     * @param  string $service
     * @return void
     */
    public function loadDeferredProvider(string $service)
    {
        if (! $this->isDeferredService($service)) {
            return;
        }

        $provider = $this->deferredServices[$service];

        // If the service provider has not already been loaded and registered we can
        // register it with the application and remove the service from this list
        // of deferred services, since it will already be loaded on subsequent.
        if (! isset($this->loadedProviders[$provider])) {
            $this->registerDeferredProvider($provider, $service);
        }
    }

    /**
     * Register a deferred provider and service.
     *
     * @param  string      $provider
     * @param  string|null $service
     * @return void
     */
    public function registerDeferredProvider(string $provider, string|null $service = null)
    {
        // Once the provider that provides the deferred service has been registered we
        // will remove it from our local list of the deferred services with related
        // providers so that this container does not try to resolve it out again.
        if ($service) {
            unset($this->deferredServices[$service]);
        }

        $this->register($instance = new $provider($this));

        if (! $this->isBooted()) {
            $this->booting(function () use ($instance) {
                $this->bootProvider($instance);
            });
        }
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string $abstract
     * @param  array  $parameters
     * @return mixed
     */
    public function make($abstract, $parameters = [])
    {
        $this->loadDeferredProviderIfNeeded($abstract = $this->getAlias($abstract));

        return parent::make($abstract, $parameters);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string $abstract
     * @param  array  $parameters
     * @param  bool   $raiseEvents
     * @return mixed
     */
    protected function resolve($abstract, $parameters = [], $raiseEvents = true)
    {
        $this->loadDeferredProviderIfNeeded($abstract = $this->getAlias($abstract));

        return parent::resolve($abstract, $parameters, $raiseEvents);
    }

    /**
     * Load the deferred provider if the given type is a deferred service and the instance has not been loaded.
     *
     * @param  string $abstract
     * @return void
     */
    protected function loadDeferredProviderIfNeeded($abstract)
    {
        if ($this->isDeferredService($abstract) && ! isset($this->instances[$abstract])) {
            $this->loadDeferredProvider($abstract);
        }
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return $this->isDeferredService($abstract) || parent::bound($abstract);
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->isBooted()) {
            return;
        }

        // Once the application has booted we will also fire some "booted" callbacks
        // for any listeners that need to do work after this initial booting gets
        // finished. This is useful when ordering the boot-up processes we run.
        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Boot the given service provider.
     *
     * @param  \Illuminate\Support\ServiceProvider $provider
     * @return void
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        $provider->callBootingCallbacks();

        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }

        $provider->callBootedCallbacks();
    }

    /**
     * Register a new boot listener.
     *
     * @param  callable $callback
     * @return void
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param  callable $callback
     * @return void
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $callback($this);
        }
    }

    /**
     * Get the service providers that have been loaded.
     *
     * @return array<string, bool>
     */
    public function getLoadedProviders()
    {
        return $this->loadedProviders;
    }

    /**
     * Determine if the given service provider is loaded.
     *
     * @param  string $provider
     * @return bool
     */
    public function providerIsLoaded($provider)
    {
        return isset($this->loadedProviders[$provider]);
    }

    /**
     * Get the application's deferred services.
     *
     * @return array
     */
    public function getDeferredServices()
    {
        return $this->deferredServices;
    }

    /**
     * Set the application's deferred services.
     *
     * @param  array $services
     * @return void
     */
    public function setDeferredServices($services)
    {
        $this->deferredServices = $services;
    }

    /**
     * Add an array of services to the application's deferred services.
     *
     * @param  array $services
     * @return void
     */
    public function addDeferredServices(array $services)
    {
        $this->deferredServices = array_merge($this->deferredServices, $services);
    }

    /**
     * Determine if the given service is a deferred service.
     *
     * @param  string $service
     * @return bool
     */
    public function isDeferredService(string $service)
    {
        return isset($this->deferredServices[$service]);
    }

    /**
     * Call the booting callbacks for the application.
     *
     * @param  callable[]  $callbacks
     * @return void
     */
    protected function fireAppCallbacks(array &$callbacks)
    {
        $index = 0;

        while ($index < count($callbacks)) {
            $callbacks[$index]($this);

            $index++;
        }
    }

    public function runningUnitTests() {
        return false;
    }

    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function flush()
    {
        parent::flush();

        $this->buildStack = [];
        $this->loadedProviders = [];
        $this->bootedCallbacks = [];
        $this->bootingCallbacks = [];
        $this->deferredServices = [];
        $this->reboundCallbacks = [];
        $this->serviceProviders = [];
        $this->resolvingCallbacks = [];
        $this->terminatingCallbacks = [];
        $this->beforeResolvingCallbacks = [];
        $this->afterResolvingCallbacks = [];
        $this->globalBeforeResolvingCallbacks = [];
        $this->globalResolvingCallbacks = [];
        $this->globalAfterResolvingCallbacks = [];
    }

    public function storagePath($path) {

        if($path == "framework/cache") {
            $path = "cache";
        }

        return APP_ROOT."/".$path;
    }

    public function runningInConsole() {

        if ($this->isRunningInConsole === null) {

            if(defined('LEAN_CLI') && LEAN_CLI) {
                $this->isRunningInConsole = true;
            }

            if(\PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg') {
                $this->isRunningInConsole = true;
            }
        }

        return $this->isRunningInConsole;

    }
}
