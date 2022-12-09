<?php

namespace leantime\core;

require_once ROOT . '/../vendor/autoload.php';

spl_autoload_register(__NAMESPACE__ . "\\leantimeAutoloader", true, true);

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
function leantimeAutoloader($class) {

    $parts = getLeantimeClassPath($class);
    $path = $parts['path'];
    $class = $parts['class'];

    // Check if a customized version of the requested class exists
    if (!empty($path)) {
        foreach (['class', 'trait'] as $prefix) {
            if ($class == "config") {
                require_once( ROOT . "/../config/configuration.php");
            } elseif (file_exists(ROOT . "/../custom/$path/$prefix.$class.php")) {
                require_once(ROOT . "/../custom/$path/$prefix.$class.php");
                break;
            } elseif (file_exists(ROOT . "/../src/$path/$prefix.$class.php")) {
                require_once(ROOT . "/../src/$path/$prefix.$class.php");
                break;
            }
        }
    }
}

require_once '../vendor/autoload.php';

function getLeantimeClassPath($class) {
    $mvcFolder = $module = $path = "";

    $classArray = explode('\\', $class);
    $classPartsCount = count($classArray);

    if ($classPartsCount == 3) {
        $class = $classArray[2];
        $srcFolder = $classArray[1];

        $path = "{$srcFolder}";
    }

    //domain
    if ($classPartsCount == 4) {
        $class = $classArray[3];
        $srcFolder = $classArray[1];
        $mvcFolder = $classArray[2];

        $path = "{$srcFolder}/{$class}/{$mvcFolder}";
    }

    if ($classPartsCount == 5) {
        $class = $classArray[4];
        $srcFolder = $classArray[1];
        $mvcFolder = $classArray[2];
        $module = $classArray[3];

        $path = "{$srcFolder}/{$module}/{$mvcFolder}";
    }

    return [
        'path' => $path,
        'class' => $class
    ];
}
