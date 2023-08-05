<?php

namespace leantime\core;

use PDO;
use PDOException;
use leantime\core\plugins;

/**
 * Events class - Handles all events and filters
 *
 * @package    leantime
 * @subpackage core
 */
class events
{
    /**
     * Registry of all events added to a hook
     *
     * @var array
     */
    private static $eventRegistry = [];

    /**
     * Registry of all filters added to a hook
     *
     * @var array
     */
    private static $filterRegistry = [];

    /**
     * Registry of all hooks available
     *
     * @var array
     */
    private static $available_hooks = [
        'filters' => [],
        'events' => [],
    ];

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
     */
    public static function dispatch_event(
        string $eventName,
        mixed $payload = [],
        string $context = ''
    ): void {
        $eventName = "$context.$eventName";

        if (!in_array($eventName, self::$available_hooks['events'])) {
            self::$available_hooks['events'][] = $eventName;
        }

        if (!key_exists($eventName, self::$eventRegistry)) {
            return;
        }

        $payload = self::defineParams($payload);

        //Sort registered listeners by priority
        self::sortByPriority('events', $eventName);

        self::executeHandlers(self::$eventRegistry, $eventName, $payload);
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

        if (!key_exists($filtername, self::$filterRegistry)) {
            return $payload;
        }

        $available_params = self::defineParams($available_params);

        //Sort registered listeners by priority
        self::sortByPriority('filters', $filtername);

        return self::executeHandlers(self::$filterRegistry, $filtername, $payload, $available_params);
    }

    /**
     * Finds all the event and filter listeners and registers them
     * (should only be executed once at the beginning of the program)
     *
     * @access public
     *
     * @return void
     */
    public static function discover_listeners(): void
    {
        $modules = glob(ROOT . "/../app/domain" . '/*', GLOB_ONLYDIR);
        foreach ($modules as $module) {
            if (file_exists($module . "/events/register.php")) {
                include $module . "/events/register.php";
            }
        }

        $pluginPath = ROOT . "/../app/plugins/";

        $enabledPlugins = [];
        if ($_SESSION['isInstalled'] === true && $_SESSION['isUpdated'] === true) {
            $pluginService = app()->make(\leantime\domain\services\plugins::class);
            $enabledPlugins = $pluginService->getEnabledPlugins();
        }

        foreach ($enabledPlugins as $plugin) {
            if($plugin != null) {
                if (file_exists($pluginPath . $plugin->foldername . "/register.php")) {
                    include $pluginPath . $plugin->foldername . "/register.php";
                }
            }
        }
    }

    /**
     * Adds an event listener to be registered
     *
     * @access public
     *
     * @param string                 $eventName
     * @param string|callable|object $handler
     * @param integer                $priority
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
     * @param integer                $priority
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

        $sorter = function ($a, $b) {
            if ($a['priority'] > $b['priority']) {
                return 1;
            } elseif ($a['priority'] == $b['priority']) {
                return 0;
            } else {
                return -1;
            }
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
     */
    private static function defineParams(mixed $paramAttr): array|object
    {
        // make this static so we only have to call once
        static $default_params;

        if (!isset($default_params)) {
            $default_params = [
                'current_route' => frontcontroller::getCurrentRoute(),
            ];
        }

        $finalParams = [];

        if (is_array($paramAttr)) {
            $finalParams = array_merge($default_params, $paramAttr);
            return $finalParams;
        }

        if (is_object($paramAttr)) {
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
     * @param string       $hookName
     * @param mixed        $payload
     * @param array|object $available_params
     *
     * @return array|object|null
     */
    private static function executeHandlers(
        array $registry,
        string $hookName,
        mixed $payload,
        array|object $available_params = []
    ): array|object|null {
        $isEvent = $registry == self::$eventRegistry ? true : false;
        $filteredPayload = null;

        foreach ($registry[$hookName] as $index => $listener) {
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
        }

        if (!$isEvent) {
            return $filteredPayload;
        }

        return null;
    }
}
