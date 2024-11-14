<?php

define('RESTRICTED', true);

require __DIR__.'/../vendor/autoload.php';

//Get the application once.
//Loads everything up once and then let's the bootloader manage it
$app = require_once __DIR__.'/../bootstrap/app.php';

\Leantime\Core\Bootloader::getInstance()->boot($app);
