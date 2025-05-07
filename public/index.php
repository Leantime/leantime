<?php

define('RESTRICTED', true);
define('LEANTIME_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/* Load Leantime helper functions before laravel */
require __DIR__.'/../app/helpers.php';
require __DIR__.'/../vendor/autoload.php';

// Get the application once.
// Loads everything up once and then let's the bootloader manage it
$app = require_once __DIR__.'/../bootstrap/app.php';

// Pass app into leantime bootloader
\Leantime\Infrastructure\Application\Bootloader::getInstance()->boot($app);
