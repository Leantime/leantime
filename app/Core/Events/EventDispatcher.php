<?php

namespace Leantime\Core\Events;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Traits\ReflectsClosures;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Frontcontroller;

/**
 * EventDispatcher class - Handles all events and filters
 *
 * @package    leantime
 * @subpackage core
 */
class EventDispatcher implements Dispatcher
{

    use ReflectsClosures;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Registry of all events added to a hook
     *
     * @var array
     */
    private static array $eventRegistry = [];

    /**
     * Registry of all filters added to a hook
     *
     * @var array
     */
    private static array $filterRegistry = [];

    /**
     * Registry of all hooks available
     *
     * @var array
     */
    private static array $available_hooks = [
        'filters' => [],
        'events' => [],
    ];

    /**
     * Create a new event dispatcher instance.
     *
     * @param  \Illuminate\Contracts\Container\Container|null  $container
     * @return void
     */
    public function __construct(\Illuminate\Contracts\Container\Container $container = null)
    {
        $this->container = $container ?: new Container;
    }

    /**
     * Dispatches an event to be executed somewhere
     *
     * @access public
     *
     * @param string $eventName
     * @param mixed  $payload
     * @param string $context
     *
     * @return void
     * @throws BindingResolutionException
     */
    public static function dispatch_event(
        string $eventName,
        mixed $payload = [],
        string $context = ''
    ): void {

        if(!empty($context)) {
            $eventName = "$context.$eventName";
        }

        if (!in_array($eventName, self::$available_hooks['events'])) {
            self::$available_hooks['events'][] = $eventName;
        }

        $matchedEvents = self::findEventListeners($eventName, self::$eventRegistry);
        if (count($matchedEvents) == 0) {
            return;
        }

        $payload = self::defineParams($payload);

        self::executeHandlers($matchedEvents, "events", $eventName, $payload);
    }

    public function dispatch(
        $event,
        $payload = [],
        $context = ''
    ) {

        //Leantime Events, simple string
        if(is_string($event)) {
            self::dispatch_event($event, $payload, $context);
        }

        //Laravel Events, objects
        if(is_object($event)){
            $eventClass = new \ReflectionClass($event);
            $eventName = $eventClass->getName();
            $payload[$eventName] = $event;
            self::dispatch_event($eventName, $payload, $context);
        }

    }

    public static function dispatch_laravel_event(
        $event,
        $payload = [],
        $context = ''
    ) {

        //Leantime Events, simple string
        if(is_string($event)) {
            self::dispatch_event($event, $payload, $context);
        }

        //Laravel Events, objects
        if(is_object($event)){
            $eventClass = new \ReflectionClass($event);
            $eventName = $eventClass->getName();
            $payload[$eventName] = $event;
            self::dispatch_event($eventName, $payload, $context);
        }

    }


    /**
     * Finds event listeners by event names,
     * Allows listeners with wildcards
     *
     * @access public
     *
     * @param string $eventName
     * @param array  $registry
     *
     * @return array
     */
    public static function findEventListeners(string $eventName, array $registry): array
    {
        $matches = [];

        foreach ($registry as $key => $value) {
            preg_match_all('/\{RGX:(.*?):RGX\}/', $key, $regexMatches);

            $key = strtr($key, [
                ...collect($regexMatches[0] ?? [])->mapWithKeys(fn ($match, $i) => [$match => "REGEX_MATCH_$i"])->toArray(),
                '*' => 'RANDOM_STRING',
                '?' => 'RANDOM_CHARACTER',
            ]);

            // escape the non regex characters
            $pattern = preg_quote($key, '/');

            $pattern = strtr($pattern, [
                'RANDOM_STRING' => '.*?', // 0 or more (lazy) - asterisk (*)
                'RANDOM_CHARACTER' => '.', // 1 character - question mark (?)
                ...collect($regexMatches[1] ?? [])->mapWithKeys(fn ($match, $i) => ["REGEX_MATCH_$i" => $match])->toArray(),
            ]);

            if (preg_match("/^$pattern$/", $eventName)) {
                $matches = array_merge($matches, $value);
            }
        }

        return $matches;
    }


    /**
     * Dispatches a filter to manipulate a variable somewhere
     *
     * @access public
     *
     * @param string $filtername
     * @param mixed  $payload
     * @param mixed  $available_params
     * @param mixed  $context
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    public static function dispatch_filter(
        string $filtername,
        mixed $payload = '',
        mixed $available_params = [],
        mixed $context = ''
    ): mixed {
        $filtername = "$context.$filtername";

        if (!in_array($filtername, self::$available_hooks['filters'])) {
            self::$available_hooks['filters'][] = $filtername;
        }

        $matchedEvents = self::findEventListeners($filtername, self::$filterRegistry);
        if (count($matchedEvents) == 0) {
            return $payload;
        }

        $available_params = self::defineParams($available_params);

        return self::executeHandlers($matchedEvents, "filters", $filtername, $payload, $available_params);
    }

    /**
     * Finds all the event and filter listeners and registers them
     * (should only be executed once at the beginning of the program)
     *
     * @access public
     *
     * @return void
     * @throws BindingResolutionException
     */
    public static function discoverListeners(): void
    {
        static $discovered;
        $discovered ??= false;

        if ($discovered) {
            return;
        }

        if(!config('debug')) {
            $modules = Cache::store('installation')->rememberForever('domainEvents', function () {
                return EventDispatcher::getDomainPaths();
            });

        }else{
            $modules = self::getDomainPaths();
        }

        foreach ($modules as $module) {
            //File exists is not expensive and builds it's own cache to speed up performance
            if (file_exists($moduleEventsPath = "$module/register.php")) {
                include_once $moduleEventsPath;
            }
        }

        if (
            isset(app(Environment::class)->plugins)
            && $configplugins = explode(',', app(Environment::class)->plugins)
        ) {
            //TODO: Do phar plugins get to be system plugins? Right now they dont
            foreach ($configplugins as $plugin) {
                if (file_exists($pluginEventsPath = APP_ROOT . "/app/Plugins/" . $plugin . "/register.php")) {
                    include_once $pluginEventsPath;
                }
            }
        }

        EventDispatcher::add_event_listener('leantime.core.middleware.installed.handle.after_install', function () {
            if (! session("isInstalled")) {
                return;
            }

            $pluginPath = APP_ROOT . "/app/Plugins/";
            $pluginService = app()->make(\Leantime\Domain\Plugins\Services\Plugins::class);
            $enabledPlugins = $pluginService->getEnabledPlugins();

            foreach ($enabledPlugins as $plugin) {
                //Catch issue when plugins are cached on load but autoloader is not quite done loading.
                //Only happens because the plugin objects are stored in session and the unserialize is not keeping up.
                //Clearing session cache in that case.
                //@TODO: Check on callstack to make sure autoload loads before sessions
                if (is_a($plugin, '__PHP_Incomplete_Class')) {
                    continue;
                }

                if ($plugin == null) {
                    continue;
                }

                if ($plugin->format == "phar") {
                    $pharPath = "phar://{$pluginPath}{$plugin->foldername}/{$plugin->foldername}.phar";

                    if (! file_exists($pharPath)) {
                        continue;
                    }

                    include_once $pharPath;

                    if (! file_exists("$pharPath/register.php")) {
                        continue;
                    }

                    include_once "$pharPath/register.php";

                    continue;
                }

                if (! file_exists($registerPath = "{$pluginPath}{$plugin->foldername}/register.php")) {
                    continue;
                }

                include_once $registerPath;
            }
        });

        $discovered = true;
    }

    public static function getDomainPaths() {

        $customModules = collect(glob(APP_ROOT . '/custom/Domain' . '/*', GLOB_ONLYDIR));
        $domainModules = collect(glob(APP_ROOT . "/app/Domain" . '/*', GLOB_ONLYDIR));

        $testers = $customModules->map(fn ($path) => str_replace('/custom/', '/app/', $path));

        $filteredModules = $domainModules->filter(fn ($path) => ! $testers->contains($path));

        return $customModules->concat($filteredModules)->all();
    }

    /**
     * Adds an event listener to be registered
     *
     * @access public
     *
     * @param string                 $eventName
     * @param string|callable|object $handler
     * @param int                    $priority
     *
     * @return void
     */
    public static function add_event_listener(
        string $eventName,
        string|callable|object $handler,
        int $priority = 10
    ): void {
        if (! key_exists($eventName, self::$eventRegistry)) {
            self::$eventRegistry[$eventName] = [];
        }
        self::$eventRegistry[$eventName][] = array("handler" => $handler, "priority" => $priority);
    }

    /**
     * Adds a filter listener to be registered
     *
     * @access public
     *
     * @param string                 $filtername
     * @param string|callable|object $handler
     * @param int                    $priority
     *
     * @return void
     */
    public static function add_filter_listener(
        string $filtername,
        string|callable|object $handler,
        int $priority = 10
    ): void {
        if (! key_exists($filtername, self::$filterRegistry)) {
            self::$filterRegistry[$filtername] = [];
        }
        self::$filterRegistry[$filtername][] = array("handler" => $handler, "priority" => $priority);
    }

    /**
     * Gets all registered listeners
     *
     * @access public
     *
     * @return array
     */
    public static function get_registries(): array
    {
        return [
            'events' => array_keys(self::$eventRegistry),
            'filters' => array_keys(self::$filterRegistry),
        ];
    }

    /**
     * Gets all available hooks
     *
     * @access public
     *
     * @return array
     */
    public static function get_available_hooks(): array
    {
        return self::$available_hooks;
    }

    /**
     * Sorts listeners by priority for a given hook and type
     *
     * @access private
     *
     * @param string $type
     * @param string $hookName
     *
     * @return void
     */
    private static function sortByPriority(string $type, string $hookName): void
    {
        if ($type !== 'filters' && $type !== 'events') {
            return;
        }

        $sorter = fn ($a, $b) => match (true) {
            $a['priority'] > $b['priority'] => 1,
            $a['priority'] == $b['priority'] => 0,
            default => -1,
        };

        if ($type == 'filters') {
            usort(self::$filterRegistry[$hookName], $sorter);
        } elseif ($type == 'events') {
            usort(self::$eventRegistry[$hookName], $sorter);
        }
    }

    /**
     * Adds the current_route to the event's/filter's available params
     *
     * @access private
     *
     * @param mixed $paramAttr
     *
     * @return array|object
     * @throws BindingResolutionException
     */
    private static function defineParams(mixed $paramAttr): array|object
    {
        // make this static so we only have to call once
        static $default_params;

        if (!isset($default_params)) {
            $default_params = [
                'current_route' => currentRoute(),
            ];
        }

        $finalParams = [];

        if (is_array($paramAttr)) {
            $finalParams = array_merge($default_params, $paramAttr);
            return $finalParams;
        }

        if (is_object($paramAttr) && get_class($paramAttr) == 'stdClass') {
            $finalParams = (object) array_merge($default_params, (array) $paramAttr);
            return $finalParams;
        }

        $finalParams = $default_params;
        array_push($finalParams, $paramAttr);

        return $finalParams;
    }

    /**
     * Executes all the handlers for a given hook
     *
     * @access private
     *
     * @param array        $registry
     * @param string       $registryType
     * @param string       $hookName
     * @param mixed        $payload
     * @param array|object $available_params
     *
     * @return array|object|null
     */
    private static function executeHandlers(
        array $registry,
        string $registryType,
        string $hookName,
        mixed $payload,
        array|object $available_params = []
    ): mixed {

        $isEvent = $registryType == "events";
        $filteredPayload = null;

        //sort matches by priority
        usort($registry, fn ($a, $b) => match (true) {
            $a['priority'] > $b['priority'] => 1,
            $a['priority'] == $b['priority'] => 0,
            default => -1,
        });

        foreach ($registry as $index => $listener) {
            $handler = $listener['handler'];

            // class with handle function
            if (is_object($handler) && method_exists($handler, "handle")) {
                if ($isEvent) {
                    $handler->handle($payload);
                    continue;
                }

                $filteredPayload = $handler->handle(
                    $index == 0 ? $payload : $filteredPayload,
                    $available_params
                );
                continue;
            }

            // anonymous functions
            if (is_callable($handler)) {
                if ($isEvent) {
                    $handler($payload);
                    continue;
                }

                $filteredPayload = $handler(
                    $index == 0 ? $payload : $filteredPayload,
                    $available_params
                );
                continue;
            }

            if (
                in_array(true, [
                // function name as string
                is_string($handler) && function_exists($handler),
                // class instance with method name
                is_array($handler) && is_object($handler[0]) && method_exists($handler[0], $handler[1]),
                // class name with method name
                is_array($handler) && class_exists($handler[0]) && method_exists($handler[0], $handler[1]),
                ])
            ) {
                if ($isEvent) {
                    call_user_func_array($handler, [$payload]);
                    continue;
                }

                $filteredPayload = call_user_func_array(
                    $handler,
                    [
                        $index == 0 ? $payload : $filteredPayload,
                        $available_params,
                    ]
                );
                continue;
            }

            if ($index == 0) {
                $filteredPayload = $payload;
            }
        }

        if (!$isEvent) {
            return $filteredPayload;
        }

        return null;
    }

    public static function getEventRegistry(): array
    {
        return self::$eventRegistry;
    }

    public static function getFilterRegistry(): array
    {
        return self::$filterRegistry;
    }



    //Laravel Compatibility
    public function listen($events, $listener = null)
    {
        if ($events instanceof Closure) {
            return collect($this->firstClosureParameterTypes($events))
                ->each(function ($event) use ($events) {
                    $this->listen($event, $events);
                });
        } elseif ($events instanceof QueuedClosure) {
            return collect($this->firstClosureParameterTypes($events->closure))
                ->each(function ($event) use ($events) {
                    $this->listen($event, $events->resolve());
                });
        } elseif ($listener instanceof QueuedClosure) {
            $listener = $listener->resolve();
        }

        foreach ((array) $events as $event) {
            self::add_event_listener($event, $listener);
        }
    }

    /**
     * Determine if a given event has listeners.
     *
     * @param  string  $eventName
     * @return bool
     */
    public function hasListeners($eventName) {
        return key_exists($eventName, self::$eventRegistry);

    }

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param  object|string  $subscriber
     * @return void
     */
    public function subscribe($subscriber)
    {
        $subscriber = $this->resolveSubscriber($subscriber);

        $events = $subscriber->subscribe($this);

        if (is_array($events)) {
            foreach ($events as $event => $listeners) {
                foreach (Arr::wrap($listeners) as $listener) {
                    if (is_string($listener) && method_exists($subscriber, $listener)) {
                        $this->listen($event, [get_class($subscriber), $listener]);

                        continue;
                    }

                    $this->listen($event, $listener);
                }
            }
        }
    }

    /**
     * Resolve the subscriber instance.
     *
     * @param  object|string  $subscriber
     * @return mixed
     */
    protected function resolveSubscriber($subscriber)
    {
        if (is_string($subscriber)) {
            return $this->container->make($subscriber);
        }

        return $subscriber;
    }

    /**
     * Dispatch an event until the first non-null response is returned.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return mixed
     */
    public function until($event, $payload = []) {
        throw new \Exception("Not implemented");
    }


    /**
     * Register an event and payload to be fired later.
     *
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function push($event, $payload = []) {
        throw new \Exception("Not implemented");
    }

    /**
     * Flush a set of pushed events.
     *
     * @param  string  $event
     * @return void
     */
    public function flush($event) {
        throw new \Exception("Not implemented");
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string  $event
     * @return void
     */
    public function forget($event) {
        throw new \Exception("Not implemented");
    }

    /**
     * Forget all of the queued listeners.
     *
     * @return void
     */
    public function forgetPushed() {
        throw new \Exception("Not implemented");
    }
}
