<?php

define('RESTRICTED', true);
define('ROOT', dirname(__FILE__));

require_once '../config/configuration.php';
require_once '../config/appSettings.php';
require_once '../src/core/class.autoload.php';

$config = new leantime\core\config();
$settings = new leantime\core\appSettings();
$settings->loadSettings($config->defaultTimezone, $config->debug ?? 0);

if (isset($config->appUrl) && $config->appUrl != "") {
    define('BASE_URL', $config->appUrl);
    define('CURRENT_URL', $config->appUrl . $settings->getRequestURI($config->appUrl));
} else {
    define('BASE_URL', $settings->getBaseURL());
    define('CURRENT_URL', $settings->getFullURL());
}

$login = leantime\core\login::getInstance(leantime\core\session::getSID());

ob_start();

$loginContent = '';

if ($login->logged_in() !== true) {
    $loginContent = ob_get_clean();
    ob_start();
}

//Bootstrap application
$application = new leantime\core\application(
    $config,
    $settings,
    $login,
    leantime\core\frontcontroller::getInstance(ROOT),
    new leantime\core\language(),
    new leantime\domain\services\projects(),
    new leantime\domain\repositories\setting()
);

$application->start();

if (ob_get_length() > 0) {
    ob_end_flush();
}
