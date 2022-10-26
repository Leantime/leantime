<?php

namespace leantime\core;

use PDO;
use PDOException;

class events
{

    protected static $eventRegistry = [];

    protected static $filterRegistry = [];

    public static function dispatch_event($eventName, $payload = [], $context = '')
    {

        $eventName = self::setHook($eventName, $context);

        if (!key_exists($eventName, self::$eventRegistry)) {
            return null;
        }

        $payload = self::defineParams($payload);

        //Sort registered listeners by priority
        self::sortByPriority('events', $eventName);

        foreach (self::$eventRegistry[$eventName] as $listener) {
            // class with handle function
            if (is_object($listener["handler"]) && method_exists($listener["handler"], "handle")) {
                $listener["handler"]->handle($eventName, $payload);
            // anonymous functions
            } elseif (is_callable($listener["handler"])) {
                $listener["handler"]($eventName, $payload);
            // function name as string
            } elseif (is_string($listener["handler"]) && function_exists($listener["handler"])) {
                call_user_func_array($listener["handler"], [$eventName, $payload]);
            }
        }
    }

    public static function dispatch_filter($filtername, $payload='', $available_params = [], $context = '') {

        $filtername = self::setHook($filtername, $context);

        if (!key_exists($filtername, self::$filterRegistry)) {
            return $payload;
        }

        $available_params = self::defineParams($available_params);

        //Sort registered listeners by priority
        self::sortByPriority('filters', $filtername);

        $filteredPayload = array();
        $i = 0;
        foreach (self::$filterRegistry[$filtername] as $listener) {
            if (is_object($listener["handler"]) && method_exists($listener["handler"], "handle")) {
                $filteredPayload = $listener["handler"]->handle(
                    $i == 0 ? $payload : $filteredPayload,
                    $available_params
                );
                $i++;
            } elseif (is_callable($listener["handler"])) {
                $filteredPayload = $listener["handler"](
                    $i == 0 ? $payload : $filteredPayload,
                    $available_params
                );
                $i++;
            }
        }

        return $filteredPayload;
    }

    public static function discover_listeners() {

        $modules = glob(ROOT."/../src/domain" . '/*' , GLOB_ONLYDIR);
        foreach($modules as $module){
            if(file_exists($module."/events/register.php")) {
                include $module."/events/register.php";
            }
        }

        $plugins = glob(ROOT."/../src/plugins" . '/*' , GLOB_ONLYDIR);
        foreach($plugins as $plugin){
            if(file_exists($plugin."/events/register.php")) {
                include $plugin . "/events/register.php";
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

    private static function setHook($hookName, $context)
    {
        if (empty($context)) {
            $contextArray = [];
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2];
    
            if (!empty($backtrace['class'])) {
                $contextArray[] = str_replace('\\', '.', $backtrace['class']);
            }
    
            if (!empty($backtrace['function'])) {
                $contextArray[] = $backtrace['function'];
            }
            
            if (!empty($contextArray)) {
                $context = implode('.', $contextArray);
            }
        }

        if (str_starts_with($context, 'leantime.')) {
            $context = str_replace('leantime.', '', $context);
        }

        if (!str_starts_with($hookName, $context)) {
            $hookName = $context . '.' . $hookName;
        }

        return $hookName;
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
        
        $finalParams = $default_params;
        array_push($finalParams, $paramAttr);

        return $finalParams;
    }

}