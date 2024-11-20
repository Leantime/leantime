<?php

require_once __DIR__.'/../vendor/autoload.php';

define('ROOT', realpath(__DIR__.'/..'));
define('APP_ROOT', realpath(__DIR__.'/..'));
define('BASE_URL', 'http://localhost');

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Leantime\Core\Console\ConsoleKernel::class)->bootstrap();

return $app;
