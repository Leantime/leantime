<?php

require_once __DIR__.'/../vendor/autoload.php';

$testEnv = __DIR__.'/../.dev/test.env';
if (file_exists($testEnv)) {
    \Dotenv\Dotenv::createImmutable(dirname($testEnv), basename($testEnv))->load();
}

// Set testing environment
putenv('APP_ENV=testing');

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Leantime\Core\Console\ConsoleKernel::class)->bootstrap();

return $app;
