<?php

define('RESTRICTED', TRUE);
define('ROOT', dirname(__FILE__));

include_once '../config/settings.php';
include_once '../src/core/class.autoload.php';
include_once '../config/configuration.php';

$config = new leantime\core\config();
if(isset($config->appUrl) && $config->appUrl != ""){
    define('BASE_URL', $config->appUrl);
} else{
    define('BASE_URL', $settings->getBaseURL());
}
define('CURRENT_URL', $settings->getFullURL());

$login = new leantime\core\login(leantime\core\session::getSID());

ob_start();

$loginContent = '';

if($login->logged_in()!==true){
	$loginContent = ob_get_clean();
	ob_start();
}

$application = new leantime\core\application($login);
$application->start();

if(ob_get_length() > 0) {
    ob_end_flush();
}

