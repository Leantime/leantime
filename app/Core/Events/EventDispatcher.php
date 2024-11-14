<?php

namespace Leantime\Core\Events;

use Illuminate\Cache\Events\CacheEvent;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\QueuedClosure;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\ReflectsClosures;
use Illuminate\View\View;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Frontcontroller;
use Symfony\Component\HttpKernel\Event\ViewEvent;

/**
 * EventDispatcher class - Handles all events and filters
 */
class EventDispatcher implements Dispatcher
{
    use Macroable;
    use ReflectsClosures;

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
     * Dispatches an event to be executed somewhere
     *
     *
     * @param  string  $eventName
     *
     * @throws BindingResolutionException
     */
    public static function dispatch_event(
        $eventName,
        mixed $payload = [],
        string $context = ''
    ): void {

        if (! empty($context)) {
            $eventName = "$context.$eventName";
        }

        if (! in_array($eventName, self::$available_hooks['events'])) {
            self::$available_hooks['events'][] = $eventName;
        }

        $matchedEvents = self::findEventListeners($eventName, self::$eventRegistry);
        if (count($matchedEvents) == 0) {
            return;
        }

        $payload = self::defineParams($payload, $eventName);

        self::executeHandlers($matchedEvents, 'events', $eventName, $payload);

    }

    public function dispatch(
        $eventName,
        $payload = [],
        $context = ''
    ) {

        if ($eventName instanceof MessageLogged) {
            $this->dispatch_event($eventName->message, $payload, $context);

            return;
        }

        if ($eventName instanceof CacheEvent) {
            $this->dispatch_event(get_class($eventName), $eventName, $context);

            return;
        }

        if ($eventName instanceof ViewEvent) {
            $this->dispatch_event($eventName, $payload, $context);

            return;
        }

        if (is_object($eventName)) {

            $eventName = get_class($eventName);
            $this->dispatch_event($eventName, [$eventName], $context);

            return;
        }

        $this->dispatch_event($eventName, $payload, $context);
    }

    /**
     * Finds event listeners by event names,
     * Allows listeners with wildcards
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
     *
     *
     * @throws BindingResolutionException
     */
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

        //Call system plugins (defined via config)
        if (
            isset(app(Environment::class)->plugins)
            && $configplugins = explode(',', app(Environment::class)->plugins)
        ) {
            //TODO: Do phar plugins get to be system plugins? Right now they dont
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
        string $eventName,
        string|callable|object $handler,
        int $priority = 10
    ): void {
        if (! array_key_exists($eventName, self::$eventRegistry)) {
            self::$eventRegistry[$eventName] = [];
        }
        self::$eventRegistry[$eventName][] = ['handler' => $handler, 'priority' => $priority];
    }

    /**
     * Adds a filter listener to be registered
     */
    public static function add_filter_listener(
        string $filtername,
        string|callable|object $handler,
        int $priority = 10
    ): void {
        if (! array_key_exists($filtername, self::$filterRegistry)) {
            self::$filterRegistry[$filtername] = [];
        }
        self::$filterRegistry[$filtername][] = ['handler' => $handler, 'priority' => $priority];
    }

    public static function addFilterListener(
        string $filtername,
        string|callable|object $handler,
        int $priority = 10
    ): void {
        self::add_filter_listener($filtername, $handler, $priority);
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

    /**
     * Adds the current_route to the event's/filter's available params
     *
     *
     *
     * @throws BindingResolutionException
     */
    private static function defineParams(mixed $paramAttr, string $eventName): array|object
    {
        // make this static so we only have to call once
        static $default_params;

        if (! isset($default_params)) {
            $default_params = [
                'current_route' => Frontcontroller::getCurrentRoute(),
                'currentEvent' => '',
            ];
        }

        $default_params['currentEvent'] = $eventName;

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
     *
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

        $isEvent = $registryType == 'events';
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
            if (is_object($handler) && method_exists($handler, 'handle')) {

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

                if (class_exists($hookName) && is_array($payload)) {

                    if ($payload[0] instanceof CacheEvent) {

                        $payload = $payload[0];

                        $handler($payload);

                        continue;
                    }
                }

                if (is_array($payload) && isset($payload[0])) {

                    if ($payload[0] instanceof View) {

                        if ($handler instanceof \Closure) {
                            $info = new \ReflectionFunction($handler);
                            $numParams = $info->getNumberOfParameters();
                            $closure = $info->getClosure();
                            $filename = $info->getFileName();

                            if ($numParams == 2) {
                                $handler($hookName, $payload);

                                continue;
                            }

                        }

                        $handler($payload[0]);

                        continue;

                    }
                }

                if ($isEvent) {

                    if ($handler instanceof \Closure) {
                        $info = new \ReflectionFunction($handler);
                        $numParams = $info->getNumberOfParameters();
                        $closure = $info->getClosure();
                        $filename = $info->getFileName();

                    }

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

        if (! $isEvent) {
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
            $this->add_event_listener($event, $listener);
        }
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
        $list = array_map(fn ($item) => $item['handler'], $listeners);

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
