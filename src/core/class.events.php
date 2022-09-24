<?php

namespace leantime\core;

use PDO;
use PDOException;

class events
{

    protected static $eventRegistry = [];

    protected static $filterRegistry = [];


    public static function dispatch_event($eventName, $payload ='')
    {
        if ( ! key_exists($eventName, self::$eventRegistry) ) {
            return null;
        }

        //Sort registered listeners by priority
        //var_dump(self::$eventRegistry);
        usort(self::$eventRegistry[$eventName], function($a, $b) {return $a['priority'] > $b['priority'];});

        foreach (self::$eventRegistry[$eventName] as $listener) {
            $listener["handler"]->handle($eventName, $payload);
        }
    }

    public static function dispatch_filter($filtername, $payload='') {

        if ( ! key_exists($filtername, self::$filterRegistry) ) {
            return null;
        }

        //Sort registered listeners by priority
        usort(self::$filterRegistry[$filtername], function($a, $b) {return $a['priority'] > $b['priority'];});

        $filteredPayload = array();
        $i = 0;

        foreach (self::$filterRegistry[$filtername] as $listener) {
            if($i == 0) {
                $filteredPayload = $listener["handler"]->handle($payload);
            }else{
                $filteredPayload = $listener["handler"]->handle($filteredPayload);
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

}