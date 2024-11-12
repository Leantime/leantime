<?php

/**
 * downloads.php - For Handling Downloads.
 */
define('RESTRICTED', true);
define('ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__, 1));
define('LEAN_CLI', false);

$domain = $_SERVER['SERVER_NAME'];

$module = filter_var($_GET['module'], FILTER_SANITIZE_URL) ?? '';
$encName = filter_var($_GET['encName'], FILTER_SANITIZE_URL) ?? '';
$ext = filter_var($_GET['ext'], FILTER_SANITIZE_URL) ?? '';
$realName = filter_var($_GET['realName'], FILTER_SANITIZE_URL) ?? '';

$query = '?module='.$module.'&encName='.$encName.'&ext='.$ext.'&realName='.$realName.'';
header('Location: //'.$_SERVER['HTTP_HOST'].'/files/get'.$query);
