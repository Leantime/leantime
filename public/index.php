<?php

define('RESTRICTED', TRUE);
define('ROOT', dirname(__FILE__));
define('APP_ROOT', dirname(__FILE__, 2));
define('LEAN_CLI', false);

if (! file_exists($composer = APP_ROOT . '/vendor/autoload.php')) {
    throw new RuntimeException('Please run "composer install".');
}

require $composer;

Leantime\Core\Bootloader::getInstance()->boot();
