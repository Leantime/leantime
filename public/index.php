<?php

define('RESTRICTED', TRUE);
define('ROOT', dirname(__FILE__));

require_once '../src/core/class.autoload.php';
require_once '../config/configuration.php';
require_once '../config/appSettings.php';


$config = new leantime\core\environment();
$settings = new leantime\core\appSettings();
$settings->loadSettings($config->defaultTimezone, $config->debug ?? 0);

if(isset($config->appUrl) && $config->appUrl != ""){
    define('BASE_URL', $config->appUrl);
    define('CURRENT_URL', $config->appUrl.$settings->getRequestURI($config->appUrl));
} else{
    define('BASE_URL', $settings->getBaseURL());
    define('CURRENT_URL', $settings->getFullURL());
}

//Bootstrap application
$application = new leantime\core\application();

$application->start();
