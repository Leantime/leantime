<?php
/**
 * Own autoload - Not a class, but for class handling
 * includes a class by "new" operator
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */

/**
 * __autoload - includes class
 *
 * @param $class
 * @return
 */
 
spl_autoload_register("leantimeAutoloader",null,true);

function leantimeAutoloader($class){
	
	$paths = array();
	$paths[] = "core/class.{$class}.php";
	$paths[] = "includes/modules/{$class}/model/class.{$class}.php";
	$paths[] ="includes/libs/{$class}/class.{$class}.php";

	foreach($paths as &$path){

		if(file_exists($path) === true) {
			if((include_once $path) !== false){ return; }
		}
			
	}

}
