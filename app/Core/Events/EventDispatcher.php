<?php

namespace Leantime\Core\Events;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Events\QueuedClosure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Traits\ReflectsClosures;
use Leantime\Core\Configuration\Environment;

/**
 * EventDispatcher class - Handles all events and filters
 */
class EventDispatcher extends \Illuminate\Events\Dispatcher implements Dispatcher
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
     */
    protected array $eventRegistry = [];

    /**
     * Registry of all filters added to a hook
     */
    private array $filterRegistry = [];

    /**
     * Registry of all hooks available
     */
    private array $available_hooks = [
        'filters' => [],
        'events' => [],
    ];

    /**
     * Create a new event dispatcher instance.
     *
     * @return void
     */
    public function __construct(?\Illuminate\Contracts\Container\Container $container = null)
    {
        $this->container = $container ?: new Container;
    }

    /**
     * Adds an event listener to be registered
     */
    public function addEventListener(
        $eventName,
        string|callable|object $handler,
        int $priority = 10
    ): void {

        if (! array_key_exists($eventName, $this->eventRegistry)) {
            $this->eventRegistry[$eventName] = [];
        }
        $isWild = str_contains($eventName, '*');
        $this->eventRegistry[$eventName][] = [
            'handler' => $handler,
            'priority' => $priority,
            'isWild' => $isWild];
    }

    //Support laravel event listeners
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
            $this->addEventListener($event, $listener);
        }
    }

    /**
     * Dispatches a filter to manipulate a variable somewhere
     *
     *
     *
     * @throws BindingResolutionException
     */
    public function dispatchFilter(
        string $filtername,
        mixed $payload = '',
        mixed $available_params = [],
        mixed $context = ''
    ): mixed {
        $filtername = "$context.$filtername";

        if (! in_array($filtername, $this->available_hooks['filters'])) {
            $this->available_hooks['filters'][] = $filtername;
        }

        $matchedEvents = $this->findEventListeners($filtername, $this->filterRegistry);
        if (count($matchedEvents) == 0) {
            return $payload;
        }

        $available_params = $this->defineParams($available_params);

        return $this->executeHandlers($matchedEvents, 'filters', $filtername, $payload, $available_params);
    }

    /**
     * Finds all the event and filter listeners and registers them
     * (should only be executed once at the beginning of the program)
     *
     *
     * @throws BindingResolutionException
     */
    public function discoverListeners(): void
    {

        if (! app('config')['debug']) {
            $modules = Cache::store('installation')->rememberForever('domainEvents', function () {
                return $this->getDomainPaths();
            });

        } else {
            $modules = $this->getDomainPaths();
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
                if (file_exists($pluginEventsPath = APP_ROOT.'/app/Plugins/'.$plugin.'/register.php')) {
                    include_once $pluginEventsPath;
                }
            }
        }

        $this->addEventListener('leantime.core.middleware.installed.handle.after_install', function () {
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

    public function getDomainPaths()
    {

        $domainModules = collect(glob(APP_ROOT.'/app/Domain'.'/*', GLOB_ONLYDIR));

        return $domainModules->all();
    }

    /**
     * Adds a filter listener to be registered
     */
    public function addFilterListener(
        string $filtername,
        string|callable|object $handler,
        int $priority = 10
    ): void {

        if (! array_key_exists($filtername, $this->filterRegistry)) {
            $this->filterRegistry[$filtername] = [];
        }
        $this->filterRegistry[$filtername][] = ['handler' => $handler, 'priority' => $priority];
    }

    /**
     * Gets all registered listeners
     */
    public function getRegistries(): array
    {
        return [
            'events' => array_keys($this->eventRegistry),
            'filters' => array_keys($this->filterRegistry),
        ];
    }

    /**
     * Gets all available hooks
     */
    public function get_available_hooks(): array
    {
        return $this->available_hooks;
    }

    /**
     * Sorts listeners by priority for a given hook and type
     */
    private function sortByPriority(string $type, string $hookName): void
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
            usort($this->filterRegistry[$hookName], $sorter);
        } elseif ($type == 'events') {
            usort($this->eventRegistry[$hookName], $sorter);
        }
    }

    /**
     * Adds the current_route to the event's/filter's available params
     *
     *
     *
     * @throws BindingResolutionException
     */
    private function defineParams(mixed $paramAttr): array|object
    {

        if (! isset($default_params)) {
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
     *
     *
     * @return array|object|null
     */
    private function executeHandlers(
        array $registry,
        string $registryType,
        string $hookName,
        mixed $payload,
        array|object $available_params = [],
        bool $halt = false,
        bool $isWild = false
    ): mixed {

        $isEvent = $registryType == 'events';
        $filteredPayload = null;

        $registry = collect($registry)->sortBy('priority');

        //sort matches by priority
        /*usort($registry, fn ($a, $b) => match (true) {
            $a['priority'] > $b['priority'] => 1,
            $a['priority'] == $b['priority'] => 0,
            default => -1,
        });*/

        foreach ($registry as $index => $listener) {

            $handler = $this->makeListener($listener['handler'], $listener['isWild']);

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
                if ($isEvent) {
                    $handler($hookName, $payload);

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

    public function getEventRegistry(): array
    {
        return $this->eventRegistry;
    }

    public function getFilterRegistry(): array
    {
        return $this->filterRegistry;
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
     * Determine if a given filter has a listener
     *
     * @param  string  $eventName
     * @return bool
     */
    public function hasFilters($filtername)
    {
        return array_key_exists($filtername, $this->filterRegistry);

    }

    /**
     * Register an event and payload to be fired later.
     *
     * @param  string  $event
     * @param  object|array  $payload
     * @return void
     */
    public function push($event, $payload = [])
    {
        parent::push($event, $payload);
    }

    /**
     * Flush a set of pushed events.
     *
     * @param  string  $event
     * @return void
     */
    public function flush($event)
    {
        parent::flush($event);
    }

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param  object|string  $subscriber
     * @return void
     */
    public function subscribe($subscriber)
    {
        parent::subscribe($subscriber);
    }

    /**
     * Resolve the subscriber instance.
     *
     * @param  object|string  $subscriber
     * @return mixed
     */
    protected function resolveSubscriber($subscriber)
    {
        return parent::resolveSubscriber($subscriber);
    }

    /**
     * Dispatch an event until the first non-null response is returned.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return mixed
     */
    public function until($event, $payload = [])
    {
        return $this->dispatch($event, $payload, true);
    }

    public function dispatch(
        $event,
        $payload = [],
        $halt = false
    ) {

        // When the given "event" is actually an object we will assume it is an event
        // object and use the class as the event name and this event itself as the
        // payload to the handler, which makes object based events quite simple.
        [$isEventObject, $event, $payload] = [
            is_object($event),
            ...$this->parseEventAndPayload($event, $payload),
        ];

        // If the event is not intended to be dispatched unless the current database
        // transaction is successful, we'll register a callback which will handle
        // dispatching this event on the next successful DB transaction commit.
        if ($isEventObject &&
            $payload[0] instanceof ShouldDispatchAfterCommit &&
            ! is_null($transactions = $this->resolveTransactionManager())) {
            $transactions->addCallback(
                fn () => $this->invokeListeners($event, $payload, $halt)
            );

            return null;
        }

        return $this->invokeListeners($event, $payload, $halt);

    }

    protected function invokeListeners($event, $payload, $halt = false)
    {
        if ($this->shouldBroadcast($payload)) {
            $this->broadcastEvent($payload[0]);
        }

        $responses = [];

        $matchedEvents = $this->findEventListeners($event, $this->eventRegistry);
        if (count($matchedEvents) == 0) {
            return $payload;
        }

        return $this->executeHandlers($matchedEvents, 'events', $event, $payload, [], $halt);

        /*

        foreach ($this->getListeners($event) as $listener) {
            $response = $listener($event, $payload);

            // If a response is returned from the listener and event halting is enabled
            // we will just return this response, and not call the rest of the event
            // listeners. Otherwise we will add the response on the response list.
            if ($halt && ! is_null($response)) {
                return $response;
            }

            // If a boolean false is returned from a listener, we will stop propagating
            // the event to any further listeners down in the chain, else we keep on
            // looping through the listeners and firing every one in our sequence.
            if ($response === false) {
                break;
            }

            $responses[] = $response;
        }

        return $halt ? null : $responses;
        */
    }

    /**
     * Dispatches an event to be executed somewhere
     *
     *
     *
     * @throws BindingResolutionException
     */
    public function dispatchEvent(
        string $eventName,
        mixed $payload = [],
        string $context = ''
    ): void {

        if (! empty($context)) {
            $eventName = "$context.$eventName";
        }

        $this->dispatch($eventName, $payload);

        //        if (! in_array($eventName, self::$available_hooks['events'])) {
        //            self::$available_hooks['events'][] = $eventName;
        //        }
        //
        //        $matchedEvents = self::findEventListeners($eventName, self::$eventRegistry);
        //        if (count($matchedEvents) == 0) {
        //            return;
        //        }
        //
        //        $payload = self::defineParams($payload);
        //
        //        self::executeHandlers($matchedEvents, 'events', $eventName, $payload);
    }

    /**
     * Parse the given event and payload and prepare them for dispatching.
     *
     * @param  mixed  $event
     * @param  mixed  $payload
     * @return array
     */
    protected function parseEventAndPayload($event, $payload)
    {
        return parent::parseEventAndPayload($event, $payload);
    }

    /**
     * Determine if the payload has a broadcastable event.
     *
     * @return bool
     */
    protected function shouldBroadcast(array $payload)
    {
        return parent::shouldBroadcast($payload);
    }

    /**
     * Check if the event should be broadcasted by the condition.
     *
     * @param  mixed  $event
     * @return bool
     */
    protected function broadcastWhen($event)
    {
        return parent::broadcastWhen($event);
    }

    /**
     * Broadcast the given event class.
     *
     * @param  \Illuminate\Contracts\Broadcasting\ShouldBroadcast  $event
     * @return void
     */
    protected function broadcastEvent($event)
    {
        parent::broadcastEvent($event);
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
        $list = array_map(fn($item) => $item['handler'], $listeners);

        return $list;
    }

    /**
     * Finds event listeners by event names,
     * Allows listeners with wildcards
     */
    public function findEventListeners(string $eventName, array $registry): array
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
