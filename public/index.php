<?php

define('RESTRICTED', TRUE);
define('ROOT', dirname(__FILE__));
define('APP_ROOT', dirname(__FILE__, 2));
define('LEAN_CLI', false);

require_once APP_ROOT . '/app/core/class.autoload.php';
require_once APP_ROOT . '/config/appSettings.php';

//dd($_GET);

leantime\core\Bootloader::getInstance()->boot();
