<?php

/**
 * __autoload - includes class
 *
 * @param  $class
 * @return
 */
 
spl_autoload_register("leantimeAutoloader", true, true);

function leantimeAutoloader($class)
{

    $namespace = "";
    $classArray = explode('\\', $class);
    // print_r($classArray);

    if(count($classArray) >0) {
        $class = $classArray[count($classArray) - 1];
        // echo "Class: ".$class."\n";
    }

    if(count($classArray) >1) {
        $namespace = $classArray[count($classArray) - 2];
        // echo "Namespace: ".$namespace."\n";
    }

    $paths = array();
    $paths[] = "../src/{$namespace}/class.{$class}.php";
    $paths[] = "../src/core/{$namespace}/class.{$class}.php";
    $paths[] = "../src/domain/{$class}/{$namespace}/class.{$class}.php";
    $paths[] = "../src/resources/libs/{$class}/class.{$class}.php";

    foreach($paths as &$path){

        if(file_exists($path) === true) {
            if((require_once $path) !== false) { return;
            }
        }
            
    }

}
require_once ROOT.'/../vendor/autoload.php';
