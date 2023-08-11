<?php

namespace leantime\core;

require_once APP_ROOT . '/vendor/autoload.php';

if (! function_exists(__NAMESPACE__ . '\\leantimeAutoloader')) {
    /**
     * the leantime autoloader function
     *
     * Note:
     * Possible Namespace Structures
     *  - leantime \ base \ CLASS|TRAIT
     *  - leantime \ core \ CLASS|TRAIT
     *  - leantime \ domain \ MVC FOLDER \ CLASS|TRAIT
     *  - leantime \ domain \ MVC FOLDER \ MODULE \ CLASS|TRAIT
     *  - leantime \ plugin \ MVC FOLDER \ CLASS|TRAIT
     *
     * @param string $class
     *
     * @return void
     */
    function leantimeAutoloader(string $class)
    {
        $originClass = $class;
        $parts = getLeantimeClassPath($class);
        $path = $parts['path'];
        $class = $parts['class'];

        if (
            str_starts_with($originClass, 'leantime\\views')
            && file_exists(APP_ROOT . "/app/$path/$class.php")
        ) {
            require_once(APP_ROOT . "/app/$path/$class.php");
        }

        // Check if a customized version of the requested class exists
        if (!empty($path)) {
            foreach (['class', 'trait', 'interface'] as $prefix) {
                if ($class == "appSettings") {
                    require_once(ROOT . "/../config/appSettings.php");
                    return;
                } elseif ($class == "config") {
                    if (file_exists(ROOT . "/../config/configuration.php")) {
                        require_once(ROOT . "/../config/configuration.php");
                    } else {
                        require_once(ROOT . "/../app/core/class.defaultConfiguration.php");
                    }
                    return;
                } elseif (file_exists(ROOT . "/../custom/$path/$prefix.$class.php")) {
                    require_once(ROOT . "/../custom/$path/$prefix.$class.php");
                    return;
                } elseif (file_exists(ROOT . "/../app/$path/$prefix.$class.php")) {
                    require_once(ROOT . "/../app/$path/$prefix.$class.php");
                    return;
                }
            }

            if (file_exists(APP_ROOT . "/app/$path/$class.php")) {
                require_once(APP_ROOT . "/app/$path/$class.php");
                return;
            }
        }
    }
}

if (! function_exists(__NAMESPACE__ . 'getLeantimeClassPath')) {
    /**
     * getLeantimeClassPath
     *
     * @param string $class
     * @return array
     */
    function getLeantimeClassPath(string $class): array
    {
        $mvcFolder = $module = $path = "";

        $classArray = explode('\\', $class);
        $classPartsCount = count($classArray);

        if (str_starts_with($class, 'leantime\\views')) {
            $class = $classArray[3];

            return [
                'path' => "{$classArray[1]}/{$classArray[2]}",
                'class' => $class,
            ];
        }

        switch ($classPartsCount) {
            case 3:
                $class = $classArray[2];
                $srcFolder = $classArray[1];

                $path = "{$srcFolder}";
                break;

            case 4:
                $class = $classArray[3];
                $srcFolder = $classArray[1];
                $mvcFolder = $classArray[2];

                $path = "{$srcFolder}/{$class}/{$mvcFolder}";
                break;

            case 5:
                $class = $classArray[4];
                $srcFolder = $classArray[1];
                $mvcFolder = $classArray[2];
                $module = $classArray[3];

                $path = "{$srcFolder}/{$module}/{$mvcFolder}";
                break;
        }

        return [
            'path' => $path,
            'class' => $class,
        ];
    }
}

spl_autoload_register(__NAMESPACE__ . "\\leantimeAutoloader", true, true);
