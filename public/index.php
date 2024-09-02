<?php

define('RESTRICTED', true);
define('ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__, 1));
define('LEAN_CLI', false);

if (! file_exists($composer = APP_ROOT . '/vendor/autoload.php')) {
    throw new RuntimeException('Please run "composer install".');
}
require $composer;


//Get the application once.
//Loads everything up once and then let's the bootloader manage it
$app = require_once APP_ROOT."/app/Core/Bootstrap/App.php";

\Leantime\Core\Bootloader::getInstance()->boot($app);
