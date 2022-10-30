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
    $path = "";

    $classArray = explode('\\', $class);

    //namespace structure
    
    //For core
    // leantime \ core \ CLASS

    //For Domain
    // leantime \ domain \ MVC FOLDER \ CLASS
    // leantime \ domain \ MVC FOLDER \ MODULE \ CLASS

    //For Plugins
    // leantime \ plugin \ MVC FOLDER \ CLASS


    $classPartsCount = count($classArray);

    if($classPartsCount == 3) {
        $class = $classArray[2];
        $srcFolder = $classArray[1];

        $path = "{$srcFolder}/class.{$class}.php";

    }

    //domain
    if($classPartsCount == 4) {
        $class = $classArray[3];
        $srcFolder = $classArray[1];
        $mvcFolder = $classArray[2];

        $path = "{$srcFolder}/{$class}/{$mvcFolder}/class.{$class}.php";
    }


    if($classPartsCount == 5) {
        $class = $classArray[4];
        $srcFolder = $classArray[1];
        $mvcFolder = $classArray[2];
        $module = $classArray[3];

        $path = "{$srcFolder}/{$module}/{$mvcFolder}/class.{$class}.php";
    }
	
	// Check if a customized version of the requested class exists
	if(!empty($path)) {
        
		if(file_exists('../custom/'.$path)) {
            
			require_once('../custom/'.$path);
            
		}elseif(file_exists('../src/'.$path)) {
            
			require_once('../src/'.$path);
            
		}
        
	}
}

require_once ROOT.'/../vendor/autoload.php';
