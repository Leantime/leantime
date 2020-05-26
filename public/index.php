<?php

define('RESTRICTED', TRUE);
define('ROOT', dirname(__FILE__));

include_once '../config/configuration.php';
include_once '../config/settings.php';
include_once '../src/core/class.autoload.php';

$config = new leantime\core\config();

if(isset($config->appUrl) && $config->appUrl != ""){
    define('BASE_URL', $config->appUrl);
    define('CURRENT_URL', $config->appUrl.$settings->getRequestURI($config->appUrl));
} else{
    define('BASE_URL', $settings->getBaseURL());
    define('CURRENT_URL', $settings->getFullURL());
}

$login = leantime\core\login::getInstance(leantime\core\session::getSID());

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
                        leantime\core\frontcontroller::getInstance(ROOT),
                        new leantime\core\language(),
                        new leantime\domain\services\projects(),
                        new leantime\domain\repositories\setting());

$application->start();

if(ob_get_length() > 0) {
    ob_end_flush();
}