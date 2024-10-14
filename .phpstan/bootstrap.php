<?php

use Leantime\Core\Http\HttpKernel;

define('RESTRICTED', true);
define('ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__, 1));
define('LEAN_CLI', false);

if (! file_exists($composer = APP_ROOT.'/vendor/autoload.php')) {
    throw new RuntimeException('Please run "composer install".');
}
require $composer;

$app = require_once APP_ROOT . '/bootstrap/app.php';

$app->make(HttpKernel::class)->bootstrap();
