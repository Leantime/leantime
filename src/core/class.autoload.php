<?php

/**
 * __autoload - includes class
 *
 * @param  $class
 * @return
 */
 
spl_autoload_register("leantimeAutoloader", null, true);

function leantimeAutoloader($class)
{

    $namespace = "";
    $classArray = explode('\\', $class);

    if(count($classArray) >0) {
        $class = $classArray[count($classArray) - 1];
    }

    if(count($classArray) >1) {
        $namespace = $classArray[count($classArray) - 2];
    }

    $paths = array();
    $paths[] = "../src/{$namespace}/class.{$class}.php";
    $paths[] = "../src/domain/{$class}/{$namespace}/class.{$class}.php";
    $paths[] = "../src/resources/libs/{$class}/class.{$class}.php";



    foreach($paths as &$path){

        if(file_exists($path) === true) {
            if((include_once $path) !== false) { return; 
            }
        }
            
    }

}
require_once '../vendor/autoload.php';
