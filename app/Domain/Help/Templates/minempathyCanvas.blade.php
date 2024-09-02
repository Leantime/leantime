<?php

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'minempathy';
require file_exists($customFile = APP_ROOT . '/custom/Domain/Canvas/Templates/helper.inc.php')
    ? $customFile
    : str_replace(APP_ROOT . '/custom/', APP_ROOT . '/app/', $customFile);
