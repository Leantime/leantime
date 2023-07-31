<?php

define('RESTRICTED', TRUE);
define('ROOT', dirname(__FILE__));
define('APP_ROOT', dirname(__FILE__, 2));

require_once APP_ROOT . '/app/core/class.autoload.php';
require_once APP_ROOT . '/config/appSettings.php';

$config = \leantime\core\environment::getInstance();
$settings = new leantime\core\appSettings();
$settings->loadSettings($config);
$incomingRequest = new leantime\core\IncomingRequest();

if(isset($config->appUrl) && $config->appUrl != ""){
    define('BASE_URL', $config->appUrl);
    define('CURRENT_URL', $config->appUrl.$settings->getRequestURI($config->appUrl));
} else{
    define('BASE_URL', $incomingRequest->getBaseURL());
    define('CURRENT_URL', $incomingRequest->getFullURL());
}

//Bootstrap application
$application = new leantime\core\application($incomingRequest);
$application->start();
