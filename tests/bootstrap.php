<?php

require_once __DIR__ . '/../vendor/autoload.php';

define('APP_ROOT', realpath(__DIR__ . '/..'));
define('ROOT', realpath(__DIR__ . '/..'));
define('BASE_URL', 'http://localhost');
define('LEAN_CLI', true);

$app = require_once __DIR__ . '/../bootstrap/app.php';
