<?php

define('RESTRICTED', true);
define('ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__, 1));
define('LEAN_CLI', false);

if (! file_exists($composer = APP_ROOT . '/vendor/autoload.php')) {
    throw new RuntimeException('Please run "composer install".');
}

require $composer;

// ini_set('session.save_handler', 'redis');
// ini_set('session.save_path', $_SERVER['LEAN_REDIS_URL']);


Leantime\Core\Bootloader::getInstance()->boot();
