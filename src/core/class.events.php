<?php

namespace leantime\core;

use PDO;
use PDOException;
use leantime\core\plugins;

class events
{

    protected static $eventRegistry = [];

    protected static $filterRegistry = [];

    protected static $available_hooks = [
        'filters' => [],
        'events' => []
    ];

    public static function dispatch_event($eventName, $payload = [], $context = '')
    {

        $eventName = self::setHook($eventName, $context);

        if (!in_array($eventName, self::$available_hooks['events'])) {
            self::$available_hooks['events'][] = $eventName;
        }

        if (!key_exists($eventName, self::$eventRegistry)) {
            return null;
        }

        $payload = self::defineParams($payload);

        //Sort registered listeners by priority
        self::sortByPriority('events', $eventName);

        self::executeHandlers(self::$eventRegistry, $eventName, $payload);
    }

    public static function dispatch_filter($filtername, $payload='', $available_params = [], $context = '') {

        $filtername = self::setHook($filtername, $context);

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

    public static function discover_listeners() {

        $modules = glob(ROOT."/../src/domain" . '/*' , GLOB_ONLYDIR);
        foreach($modules as $module){
            if(file_exists($module."/events/register.php")) {
                include $module."/events/register.php";
            }
        }

        $enabledPlugins = (new plugins)->getEnabledPlugins();
        $plugins = glob(ROOT."/../src/plugins" . '/*' , GLOB_ONLYDIR);
        foreach($plugins as $pluginLocation){
            $plugin = strtolower(substr($pluginLocation, strrpos($pluginLocation, '/') + 1));

            if(file_exists($pluginLocation."/register.php")
                && in_array($plugin, array_keys($enabledPlugins))
                && $enabledPlugins[$plugin] == true
            ) {
                include $pluginLocation . "/register.php";
            }
        }

    }

    public static function add_event_listener($eventName, $handler, $priority = 10)
    {
        if ( ! key_exists($eventName, self::$eventRegistry) ) {
            self::$eventRegistry[$eventName] = [];
        }
        self::$eventRegistry[$eventName][] = array("handler"=> $handler, "priority" => $priority);
    }


    public static function add_filter_listener($filtername, $handler, $priority = 10)
    {
        if ( ! key_exists($filtername, self::$filterRegistry) ) {
            self::$filterRegistry[$filtername] = [];
        }
        self::$filterRegistry[$filtername][] = array("handler"=> $handler, "priority" => $priority);
    }

    public static function get_registries()
    {
        return [
            'events' => array_keys(self::$eventRegistry),
            'filters' => array_keys(self::$filterRegistry)
        ];
    }

    public static function get_available_hooks()
    {
        return self::$available_hooks;
    }

    private static function setHook($hookName, $context)
    {
        if (!empty($context)) {
            return $context . '.' . $hookName;
        }

        $contextArray = [];
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $class = $backtrace[2]['class'];
        $function = $backtrace[2]['function'];
        preg_match('/(domain|plugins)\/(.+?)\//', $backtrace[1]['file'], $filematches);
        $filecontext = isset($filematches[1]) ? $filematches[1]: '';
        $filename = isset($filematches[2]) ? $filematches[2] : '';

        if (!empty($class)) {
            $contextArray[] = str_replace('\\', '.', $class);
        }

        if (!empty($function)) {
            $contextArray[] = $function;
        }

        if (!empty($contextArray)) {
            $context = implode('.', $contextArray);
        }

        if (str_starts_with($context, 'leantime.')) {
            $context = str_replace('leantime.', '', $context);
        }

        if (!empty($filename)
            && !empty($filecontext)
            && str_starts_with($context, $filecontext)
        ) {
            $context = str_replace("$filecontext.", "$filecontext.$filename.", $context);
        }

        return $context . '.' . $hookName;
    }

    private static function sortByPriority($type, $hookName)
    {
        if ($type !== 'filters' && $type !== 'events') {
            return false;
        }

        $sorter = function ($a, $b) {
            return $a['priority'] > $b['priority'];
        };

        if ($type == 'filters') {
            usort(self::$filterRegistry[$hookName], $sorter);
        } else if ($type == 'events') {
            usort(self::$eventRegistry[$hookName], $sorter);
        }
    }

    private static function defineParams($paramAttr)
    {
        // make this static so we only have to call once
        static $default_params;

        if (!isset($default_params)) {
            $default_params = [
                'context' => frontcontroller::getCurrentRoute()
            ];
        }

        $finalParams = [];

        if (is_array($paramAttr)) {
            $finalParams = array_merge($default_params, $paramAttr);
            return $finalParams;
        }

        if (is_object($paramAttr)) {
            $finalParams = array_merge($default_params, (array) $paramAttr);
            return $finalParams;
        }

        $finalParams = $default_params;
        array_push($finalParams, $paramAttr);

        return $finalParams;
    }

    private static function executeHandlers($registry, $hookName, $payload, $available_params = [])
    {

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

            if (in_array(true, [
                // function name as string
                is_string($handler) && function_exists($handler),
                // class instance with method name
                is_array($handler) && is_object($handler[0]) && method_exists($handler[0], $handler[1]),
                // class name with method name
                is_array($handler) && class_exists($handler[0]) && method_exists($handler[0], $handler[1])
            ])) {
                if ($isEvent) {
                    call_user_func_array($handler, [$payload]);
                    continue;
                }

                $filteredPayload = call_user_func_array(
                    $handler,
                    [
                        $index == 0 ? $payload : $filteredPayload,
                        $available_params
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
