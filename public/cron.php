<?php

// Semaphone part is meant to prevent concurrent execution
// I dropped the idea of using real semaphores because a lot of 
// providers don't allow it.
// TODO : Maybe create a semaphore class with a fallback on file locks
// TODO : Ensure no locks remain there for long (would block cron)
// TODO : make a javascript more clever doing periodic calls
//        + push here a json structure with information so that javascript call does not come back too often
//        if there are a lot of people connected

// Get the semaphore
$fp = fopen("../resources/logs/cronlock.txt", "w+");

// If semaphore can not be created, exit
if (!flock($fp, LOCK_EX)) {
    exit;
}

define('RESTRICTED', FALSE);
define('ROOT', dirname(__FILE__));

include_once '../config/configuration.php';
include_once '../config/appSettings.php';
include_once '../src/core/class.autoload.php';

use leantime\domain\repositories;
use leantime\domain\services;

$config = new leantime\core\config();
$settings = new leantime\core\appSettings();
$settings->loadSettings($config->defaultTimezone);

// NEW Audit system
$audit = new leantime\domain\repositories\audit();

$lastEvent = $audit->getLastEvent('cron');

if(isset($lastEvent['date'])) {
    $lastCronEvent = strtotime($lastEvent['date']);
}else{
    $lastCronEvent = 0;
}

// Using audit system to prevent too frequent executions
$nowDate = time();
$timeSince = abs($nowDate - $lastCronEvent);
if ($timeSince < 300)
{
    echo "Last cron execution was on ".$lastEvent['date']. " plz come back later";
    exit;
}

// Storing audit cron event
$audit->storeEvent("cron", "Cron started");

// TODO  check if using the session class in cron is a better idea
session_start();

if(isset($config->appUrl) && $config->appUrl != ""){
    define('BASE_URL', $config->appUrl);
    define('CURRENT_URL', $config->appUrl.$settings->getRequestURI($config->appUrl));
} else{
    define('BASE_URL', $settings->getBaseURL());
    define('CURRENT_URL', $settings->getFullURL());
}

ob_start();

if ( isset($_GET['mode']))
{
    if ( $_GET['mode'] == "debug" )
    {
        $DEBUG_CRON=true;
    } else
    {
        $DEBUG_CRON=false;
    }
}

function debug_print ($message)
{
    global $DEBUG_CRON;
    if ( $DEBUG_CRON == true )
    {
        print($message."\n");
    }
}

// setting manually session values to theme the emails.
// Could not get the repoSettings class to work
// DONE : Find a better solution
function overrideThemeSettingsMinimal()
{
    date_default_timezone_set('Europe/Paris');

    $settingsRepo = new leantime\domain\repositories\setting();
    $logoPath = $settingsRepo->getSetting("companysettings.logoPath");
    $color = $settingsRepo->getSetting("companysettings.primarycolor");
    $color2 = $settingsRepo->getSetting("companysettings.secondarycolor");
    $sitename = $settingsRepo->getSetting("companysettings.sitename");

    if (strpos($logoPath, 'http') === 0) {
        $_SESSION["companysettings.logoPath"] =  $logoPath;
    }else{
        $_SESSION["companysettings.logoPath"] =  BASE_URL.$logoPath;
    }
    // echo for DEBUG PURPOSE
    //debug_print($_SESSION["companysettings.logoPath"]);

    $_SESSION["companysettings.mainColor"] = $color;
    $_SESSION["companysettings.primarycolor"] = $color;
    $_SESSION["companysettings.secondarycolor"] = $color2;
    // echo for DEBUG PURPOSE
    //debug_print($_SESSION["companysettings.mainColor"]);

    $_SESSION["companysettings.sitename"] = $sitename;
    // echo for DEBUG PURPOSE
    //debug_print($_SESSION["companysettings.sitename"]);

}

//Bootstrap application
// echo for DEBUG PURPOSE
debug_print( "cron start");

overrideThemeSettingsMinimal();

$queueService = new services\queue();

$queueService->processQueue();

// echo for DEBUG PURPOSE
debug_print( "cron end");

// Cleaning old audit events
$audit->pruneEvents();

// Release the semaphore for next thread
flock($fp, LOCK_UN);
fclose($fp);

if(ob_get_length() > 0) {
    ob_end_flush();
}
