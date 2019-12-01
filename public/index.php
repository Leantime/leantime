<?php

define('RESTRICTED', TRUE);
define('ROOT', dirname(__FILE__));

include_once '../config/settings.php';
include_once '../src/core/class.autoload.php';
include_once '../config/configuration.php';

$login = new leantime\core\login(leantime\core\session::getSID());

ob_start();

$loginContent = '';

if($login->logged_in()!==true){
	$loginContent = ob_get_clean();
	ob_start();
}

//Bootstrap application
$application = new leantime\core\application(
                        new leantime\core\config(),
                        $settings,
                        $login,
                        leantime\core\FrontController::getInstance(ROOT),
                        new  leantime\core\language(),
                        new leantime\domain\services\projects());

$application->start();

if(ob_get_length() > 0) {
    ob_end_flush();
}


