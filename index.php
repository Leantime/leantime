<?php
 
define('VERSION', '0.6');
define('RESTRICTED', TRUE);
define('PUBLIC', TRUE);
//define('JHDABRECHNUNG_PUBLIC', True);
define('MYFILE', basename($_SERVER['PHP_SELF'], ".d"));
define('TEMPLATE', 'zypro');

//define('MODULE', 'JHDABRECHNUNG');

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


//Auslesen des Wurzelverzeichnisses
$root = dirname(__FILE__);
define('ROOT', $root);


// Instanzieren des Front Controllers
$main = frontcontroller::getInstance($root);



if (is_object($main)) {
	
	if(isset($_GET['export']) === true){

		include('includes/modules/general/templates/export.tpl.php');

	}else{

		$_SESSION['ww']['filename']='index.php';
			
		include('includes/modules/general/templates/main.tpl.php');

	}
}

ob_end_flush();

?>
