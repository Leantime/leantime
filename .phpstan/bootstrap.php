<?php

use Leantime\Infrastructure\Http\HttpKernel;

define('RESTRICTED', true);
define('ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__, 1));
define('LEAN_CLI', false);

require __DIR__.'/../vendor/autoload.php';

$app = require_once APP_ROOT . '/bootstrap/app.php';

$app->make(\Leantime\Infrastructure\Console\ConsoleKernel::class)->bootstrap();
