<?php
 
define('VERSION', '0.6');
define('RESTRICTED', TRUE);
define('PUBLIC', TRUE);
define('MYFILE', basename($_SERVER['PHP_SELF'], ".d"));
define('TEMPLATE', 'zypro');

$root = dirname(__FILE__);
define('ROOT', $root);

include_once 'config/settings.php';
include_once 'core/class.autoload.php';
include_once 'config/configuration.php';

$login = new login(session::getSID());

ob_start();

if($login->logged_in()!==true){
	
	$login->showLogin();
	
	$loginContent = ob_get_clean();
	ob_start();
	
}else{
	$loginContent = '';
}

try {
	
	$main = frontcontroller::getInstance($root);

}catch(Exception $e){
		
	echo $e->language;
	
}

if (is_object($main)) {
	
	if(isset($_GET['export']) === true){

		include('includes/modules/general/templates/export.tpl.php');
		
	}else{
			
		include('includes/modules/general/templates/main.tpl.php');

	}
}

ob_end_flush();
