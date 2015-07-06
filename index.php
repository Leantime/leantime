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


$application = new application();
$application->start();

ob_end_flush();
