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

    $mvcFolder = "";
    $module = "";

    $classArray = explode('\\', $class);

    //namespace structure
    //For core
    // leantime \ core \ CLASS

    //For Domain
    // leantime \ domain \ MVC FOLDER \ CLASS
    // leantime \ domain \ MVC FOLDER \ MODULE \ CLASS

    //For Plugins
    // leantime \ plugins \ plugin \ MVC FOLDER \ CLASS


    if(count($classArray) == 3) {
        $class = $classArray[2];
        $srcFolder = $classArray[1];

    }

    if(count($classArray) == 4) {
        $class = $classArray[3];
        $srcFolder = $classArray[1];
        $mvcFolder = $classArray[2];
    }

    if(count($classArray) == 5) {
        $class = $classArray[4];
        $srcFolder = $classArray[1];
        $mvcFolder = $classArray[2];
        $module = $classArray[3];
    }

    $paths = array();
    $paths[] = "../src/core/class.{$class}.php";
    $paths[] = "../src/domain/{$class}/{$mvcFolder}/class.{$class}.php";
    $paths[] = "../src/domain/{$module}/{$mvcFolder}/class.{$class}.php";

    $paths[] = "../src/plugins/{$class}/class.{$class}.php";
    $paths[] = "../src/plugins/{$class}/{$mvcFolder}/class.{$class}.php";
    $paths[] = "../src/plugins/{$module}/{$mvcFolder}/class.{$class}.php";





    $paths[] = "../src/resources/libs/{$class}/class.{$class}.php";

    foreach($paths as &$path){

        if(file_exists($path) === true) {
            if((require_once $path) !== false) { return;
            }
        }
            
    }

}
require_once ROOT.'/../vendor/autoload.php';
