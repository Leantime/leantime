<?php

namespace Leantime\Core\Events;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\QueuedClosure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\ReflectsClosures;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Frontcontroller;

/**
 * EventDispatcher class - Handles all events and filters
 */
class EventDispatcher implements Dispatcher
{
    use Macroable;
    use ReflectsClosures;

    /**
     * Cache for pattern matching results
     */
    private static array $patternMatchCache = [];

    /**
     * Registry of all events added to a hook
     */
    private static array $eventRegistry = [];

    /**
     * Registry of all filters added to a hook
     */
    private static array $filterRegistry = [];

    /**
     * Registry of all hooks available
     */
    private static array $available_hooks = [
        'filters' => [],
        'events' => [],
    ];

    /**
     * Finds event listeners by event names,
     * Allows listeners with wildcards
     */
    public static function findEventListeners(string $eventName, array $registry): array
    {
        // Check cache first
        $cacheKey = $eventName.'_'.md5(serialize(array_keys($registry)));
        if (isset(self::$patternMatchCache[$cacheKey])) {
            return self::$patternMatchCache[$cacheKey];
        }

        $matches = [];
        $patterns = [];

        foreach ($registry as $key => $value) {
            // Skip if we've already compiled this pattern
            if (! isset($patterns[$key])) {
                preg_match_all('/\{RGX:(.*?):RGX\}/', $key, $regexMatches);
                $pattern = self::compilePattern($key, $regexMatches);
                $patterns[$key] = $pattern;
            } else {
                $pattern = $patterns[$key];
            }

            if (preg_match("/^$pattern$/", $eventName)) {
                $matches = array_merge($matches, $value);
            }
        }

        // Cache the result
        self::$patternMatchCache[$cacheKey] = $matches;

        return $matches;
    }

    /**
     * Compiles a pattern for matching
     */
    private static function compilePattern(string $key, array $regexMatches): string
    {
        $key = strtr($key, [
            ...collect($regexMatches[0] ?? [])->mapWithKeys(fn ($match, $i) => [$match => "REGEX_MATCH_$i"])->toArray(),
            '*' => 'RANDOM_STRING',
            '?' => 'RANDOM_CHARACTER',
        ]);

        $pattern = preg_quote($key, '/');

        return strtr($pattern, [
            'RANDOM_STRING' => '.*?',
            'RANDOM_CHARACTER' => '.',
            ...collect($regexMatches[1] ?? [])->mapWithKeys(fn ($match, $i) => ["REGEX_MATCH_$i" => $match])->toArray(),
        ]);
    }

    public function dispatch(
        $event,
        $payload = [],
        $halt = false
    ) {

        $this->dispatch_event($event, $payload, '');
    }

    public static function dispatch_filter(
        string $filtername,
        mixed $payload = '',
        mixed $available_params = [],
        mixed $context = ''
    ): mixed {
        $filtername = "$context.$filtername";

        if (! in_array($filtername, self::$available_hooks['filters'])) {
            self::$available_hooks['filters'][] = $filtername;
        }

        $matchedEvents = self::findEventListeners($filtername, self::$filterRegistry);
        if (count($matchedEvents) == 0) {
            return $payload;
        }

        $available_params = self::defineParams($available_params, $filtername);

        return self::executeHandlers($matchedEvents, 'filters', $filtername, $payload, $available_params);
    }

    public static function dispatch_event(
        $event,
        mixed $payload = [],
        string $context = ''
    ): void {

        // Laravel events can be objects. Let's get those into the right format
        // Event comes out as string, either as class string or regular old string
        // No-op for leantime events
        [$event, $payload] = [
            ...self::parseEventAndPayload($event, $payload),
        ];

        if (! empty($context)) {
            $event = "$context.$event";
        }

        if (! in_array($event, self::$available_hooks['events'])) {
            self::$available_hooks['events'][] = $event;
        }

        $matchedEvents = self::findEventListeners($event, self::$eventRegistry);
        if (count($matchedEvents) == 0) {
            return;
        }

        $payload['leantime'] = self::defineParams($payload, $event);
        $payload['laravel'] = $payload;

        self::executeHandlers($matchedEvents, 'events', $event, $payload);

    }

    /**
     * Adds the current_route to the event's/filter's available params
     *
     * @throws BindingResolutionException
     */
    private static function defineParams(mixed $paramAttr, string $eventName): array|object
    {
        // make this static so we only have to call once
        // static $default_params;

        $default_params = [
            'current_route' => Frontcontroller::getCurrentRoute(),
            'currentEvent' => $eventName,
        ];

        if (! is_array($paramAttr)) {
            $paramAttr = [$paramAttr];
        }

        $paramAttr = array_merge($default_params, $paramAttr);

        return $paramAttr;

        // Not entirely sure about all of this...
        //

        // $finalParams = [];

        if (is_array($paramAttr)) {
            $default_params = array_merge($default_params, $paramAttr);

            return $default_params;
        }

        if (is_object($paramAttr)) {
            $default_params['payload'] = $paramAttr;

            return $default_params;
        }

        return $default_params;

        //
        //        if (is_object($paramAttr) && get_class($paramAttr) == 'stdClass') {
        //            $finalParams = (object) array_merge($default_params, (array) $paramAttr);
        //
        //            return $finalParams;
        //        }

        //        $finalParams = $default_params;
        //        array_push($finalParams, $paramAttr);
        //
        //        return $finalParams;
    }

    /**
     * Parse the given event and payload and prepare them for dispatching.
     *
     * @param  mixed  $event
     * @param  mixed  $payload
     * @return array
     */
    protected static function parseEventAndPayload($event, $payload)
    {
        if (is_object($event)) {
            [$payload, $event] = [[$event], get_class($event)];
        }

        return [$event, Arr::wrap($payload)];
    }

    /**
     * Executes all the handlers for a given hook
     *
     * @return array|object|null
     */
    private static function executeHandlers(
        array $registry,
        string $registryType,
        string|object $event,
        mixed $payload,
        array|object $available_params = []
    ): mixed {

        $isEvent = ($registryType === 'events');
        $filteredPayload = null;

        try {
            // sort matches by priority
            usort($registry, fn ($a, $b) => match (true) {
                $a['priority'] > $b['priority'] => 1,
                $a['priority'] == $b['priority'] => 0,
                default => -1,
            });

            foreach ($registry as $index => $listener) {

                $handler = $listener['listener'];

                // Part 1: Handle Events
                if ($isEvent) {

                    // parsing listener to determine whether we;re dealing with a closure, class, object, string etc
                    $parsedListener = self::makeListener($handler);

                    if ($listener['source'] == 'laravel') {
                        $parsedListener($event, $payload['laravel']);

                        continue;
                    }

                    $parsedListener($event, [$payload['leantime']]);

                    continue;
                }

                // Part 2: Handle Filters
                if ($index === 0) {
                    $filteredPayload = $payload;
                }

                $filteredPayload = $handler($filteredPayload, $available_params);

                continue;

                //                // Handle Laravel style events
                //                //payload has an actual object
                //                //Those will never be filters
                //                if (self::isLaravelEvent($payload)) {
                //                    self::handleLaravelEvent($handler, $payload[0]);
                //                    continue;
                //                }
                //
                //
                //                if (self::isHandleableObject($handler)) {
                //                    self::handleLaravelEvent($handler, $payload[0]);
                //                }
                //
                //                // Handle class with handle method
                //                if (self::isHandleableClass($handler)) {
                //
                //                    if ($isEvent) {
                //
                //                        $handler->handle($payload);
                //                        continue;
                //                    }
                //
                //                    $filteredPayload = $handler->handle(
                //                        $index == 0 ? $payload : $filteredPayload,
                //                        $available_params
                //                    );
                //
                //                    continue;
                //                }
                //
                //                // Handle Closures and callable functions
                //                if (is_callable($handler)) {
                //
                //                    if ($isEvent) {
                //                        self::executeCallable($handler, $payload, $available_params, $index, $isEvent);
                //                        continue;
                //                    }
                //
                //                    $result = self::executeCallable($handler,  $index == 0 ? $payload : $filteredPayload, $available_params, $index, $isEvent);
                //                    if ($result !== null) {
                //                        $filteredPayload = $result;
                //                    }
                //                    continue;
                //                }

            }
        } catch (\TypeError $e) {

            if (! isset($filteredPayload) && $index === 0) {
                $filteredPayload = $payload;
            }
        }

        return $isEvent ? null : $filteredPayload;
    }

    private static function executeLeantimeEvent(array $registry,
        string $registryType,
        string $hookName,
        mixed $payload,
        array|object $available_params = [])
    {

        // Won't be a filter, cause it's an event

        // sort matches by priority
        usort($registry, fn ($a, $b) => match (true) {
            $a['priority'] > $b['priority'] => 1,
            $a['priority'] == $b['priority'] => 0,
            default => -1,
        });

        foreach ($registry as $index => $listener) {
            // Leantime events are strings.
            // the handler can be either ca closure a class string or a callable

        }

    }

    private static function isLaravelEvent($payload): bool
    {
        if (isset($payload[0]) && is_object($payload[0])) {
            return true;
        }

        return false;
    }

    private static function isHandleableClass($handler): bool
    {

        // Option Handler is a class and we just need to call the handle string
        return is_object($handler) && method_exists($handler, 'handle');
    }

    private static function isHandleableObject($handler): bool
    {

        // Option $handler is an array
        if (is_array($handler) && is_object($handler[0]) && method_exists($handler[0], $handler[1] ?? 'handle')) {
            return true;
        }

        return false;

    }

    private static function handleLaravelEvent($handler, $payload): void
    {

        if (! is_object($payload) && class_exists($payload)) {
            $payload = app()->make($payload);
        }

        if (is_callable($handler)) {
            $handler($payload);
        } elseif (self::isHandleableClass($handler)) {
            $handler->handle($payload);
        }
    }

    private static function executeCallable($handler, $payload, $available_params, $index, $isEvent)
    {
        // Handle Laravel style events with reflection
        if ($handler instanceof \Closure) {
            $reflection = new \ReflectionFunction($handler);
            $parameters = $reflection->getParameters();

            if (count($parameters) === 1 && is_object($payload)) {
                $handler($payload);

                return null;
            }
        }

        if ($isEvent) {
            $handler($payload);

            return null;
        }

        return $handler(
            $payload,
            $available_params
        );
    }

    /**
     * Finds all the event and filter listeners and registers them
     * (should only be executed once at the beginning of the program)
     *
     *
     * @throws BindingResolutionException
     */
    public static function discoverListeners(): void
    {
        static $discovered;
        $discovered ??= false;

        if ($discovered) {
            return;
        }

        if ((bool) config('debug') === false) {

            $modules = Cache::store('installation')->rememberForever('domainEvents', function () {
                return EventDispatcher::getDomainPaths();
            });

        } else {
            $modules = self::getDomainPaths();
        }

        foreach ($modules as $module) {
            if (file_exists($moduleEventsPath = "$module/register.php")) {
                include_once $moduleEventsPath;
            }
        }

        // Call system plugins (defined via config)
        if (
            isset(app(Environment::class)->plugins)
            && $configplugins = explode(',', app(Environment::class)->plugins)
        ) {
            // TODO: Do phar plugins get to be system plugins? Right now they dont
            foreach ($configplugins as $plugin) {
                if (file_exists($pluginEventsPath = APP_ROOT.'/app/Plugins/'.$plugin.'/register.php')) {
                    include_once $pluginEventsPath;
                }
            }
        }

        EventDispatcher::add_event_listener('leantime.core.middleware.loadplugins.handle.pluginsStart', function () {

            if (! session('isInstalled')) {
                return;
            }

            $pluginPath = APP_ROOT.'/app/Plugins/';
            $pluginService = app()->make(\Leantime\Domain\Plugins\Services\Plugins::class);
            $enabledPlugins = $pluginService->getEnabledPlugins();

            foreach ($enabledPlugins as $plugin) {

                // Catch issue when plugins are cached on load but autoloader is not quite done loading.
                // Only happens because the plugin objects are stored in session and the unserialize is not keeping up.
                // Clearing session cache in that case.
                // @TODO: Check on callstack to make sure autoload loads before sessions
                if (is_a($plugin, '__PHP_Incomplete_Class')) {
                    continue;
                }

                if ($plugin == null) {
                    continue;
                }

                if ($plugin->format == 'phar') {
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

    public static function getDomainPaths()
    {

        $customModules = collect(glob(APP_ROOT.'/custom/Domain'.'/*', GLOB_ONLYDIR));
        $domainModules = collect(glob(APP_ROOT.'/app/Domain'.'/*', GLOB_ONLYDIR));

        $testers = $customModules->map(fn ($path) => str_replace('/custom/', '/app/', $path));

        $filteredModules = $domainModules->filter(fn ($path) => ! $testers->contains($path));

        return $customModules->concat($filteredModules)->all();
    }

    /**
     * Adds an event listener to be registered
     */
    public static function add_event_listener(
        $event,
        $listener,
        int $priority = 10,
        $listenerSource = 'leantime'
    ): void {

        // Some backwards compatibility rules
        if (str_starts_with($event, 'leantime.core.template.tpl')) {
            $eventParts = explode('.', $event);

            $count = count($eventParts);

            $event = 'leantime.*.'.($eventParts[$count - 2] ?? '').'.'.($eventParts[$count - 1] ?? '');
        }

        if ($event == 'leantime.core.*.afterFooterOpen') {
            $event = 'leantime.*.afterFooterOpen';
        }

        if (! array_key_exists($event, self::$eventRegistry)) {
            self::$eventRegistry[$event] = [];
        }

        // Laravel adds the listener directly without having priority. Keep that in mind!!
        self::$eventRegistry[$event][] = ['listener' => $listener, 'priority' => $priority, 'source' => $listenerSource];
    }

    public static function addEventListener($event, $listener, $priority = 10, $source = 'leantime')
    {
        self::add_event_listener($event, $listener, $priority, $source);
    }

    public static function add_filter_listener(
        $filtername,
        $listener,
        int $priority = 10,
        $listenerSource = 'leantime'
    ): void {
        if (! array_key_exists($filtername, self::$filterRegistry)) {
            self::$filterRegistry[$filtername] = [];
        }
        self::$filterRegistry[$filtername][] = ['listener' => $listener, 'priority' => $priority, 'source' => $listenerSource];
    }

    public static function addFilterListener(
        $filtername,
        $listener,
        int $priority = 10
    ): void {
        self::add_filter_listener($filtername, $listener, $priority);
    }

    // Laravel listen. They can do whatever.
    public function listen($events, $listener = null)
    {

        if ($events instanceof \Closure) {
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
            $this->add_event_listener($event, $listener, 10, 'laravel');
        }
    }

    // Different options for events and listeners

    // Event itself is object
    // Event itself is class string
    // Event itself is just string

    // Listener options

    // 2 Listener is closure
    // 3 Listener is callable (array)
    // 4 Listener is class string (call handle)
    /**
     * Register an event listener with the dispatcher.
     *
     * @param  \Closure|string|array  $listener
     * @param  bool  $wildcard
     * @return \Closure
     */
    public static function makeListener($listener, $wildcard = false)
    {

        if (is_string($listener) && ! function_exists($listener)) {
            return self::createClassListener($listener, $wildcard);
        }

        if (is_array($listener) && isset($listener[0]) && is_string($listener[0])) {
            return self::createClassListener($listener, $wildcard);
        }

        // If listener is a closure, we're preparing a closure to call the closure...
        return function ($event, $payload) use ($listener, $wildcard) {
            if ($wildcard) {
                return $listener($event, $payload);
            }

            return $listener(...array_values($payload));
        };
    }

    public static function createClassListener($listener, $wildcard = false)
    {
        return function ($event, $payload) use ($listener, $wildcard) {
            if ($wildcard) {
                return call_user_func(self::createClassCallable($listener), $event, $payload);
            }

            $callable = self::createClassCallable($listener);

            return $callable(...array_values($payload));
        };
    }

    /**
     * Create the class based event callable.
     * Covers options 3+4
     *
     * @param  array|string  $listener
     * @return callable
     */
    protected static function createClassCallable($listener)
    {
        [$class, $method] = is_array($listener)
            ? $listener
            : self::parseClassCallable($listener);

        if (! method_exists($class, $method)) {
            $method = '__invoke';
        }

        //        if ($this->handlerShouldBeQueued($class)) {
        //            return $this->createQueuedHandlerCallable($class, $method);
        //        }

        $listener = app()->make($class);

        //        return $this->handlerShouldBeDispatchedAfterDatabaseTransactions($listener)
        //            ? $this->createCallbackForListenerRunningAfterCommits($listener, $method)
        //            : [$listener, $method];

        return [$listener, $method];
    }

    /**
     * Parse the class listener into class and method.
     *
     * @param  string  $listener
     * @return array
     */
    protected static function parseClassCallable($listener)
    {
        return Str::parseCallback($listener, 'handle');
    }

    /**
     * Gets all registered listeners
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
     */
    public static function get_available_hooks(): array
    {
        return self::$available_hooks;
    }

    /**
     * Sorts listeners by priority for a given hook and type
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

    public static function getEventRegistry(): array
    {
        return self::$eventRegistry;
    }

    public static function getFilterRegistry(): array
    {
        return self::$filterRegistry;
    }

    /**
     * Determine if a given event has listeners.
     *
     * @param  string  $eventName
     * @return bool
     */
    public function hasListeners($eventName)
    {
        return array_key_exists($eventName, $this->eventRegistry);

    }

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param  object|string  $subscriber
     * @return void
     */
    public function subscribe($subscriber) {}

    /**
     * Dispatch an event until the first non-null response is returned.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return mixed
     */
    public function until($event, $payload = [])
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Get all of the listeners for a given event name.
     *
     * @param  string  $eventName
     * @return array
     */
    public function getListeners($eventName)
    {
        $listeners = $this->findEventListeners($eventName, $this->getEventRegistry());
        $list = array_map(fn ($item) => $item['listener'], $listeners);

        return $list;
    }

    /**
     * Register an event and payload to be fired later.
     *
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function push($event, $payload = [])
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Flush a set of pushed events.
     *
     * @param  string  $event
     * @return void
     */
    public function flush($event)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string  $event
     * @return void
     */
    public function forget($event)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Forget all of the queued listeners.
     *
     * @return void
     */
    public function forgetPushed()
    {
        throw new \Exception('Not implemented');
    }
}
