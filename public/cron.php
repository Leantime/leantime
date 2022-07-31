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
    if ( $_GET['mode'] && $_GET['mode'] == "debug" )
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

// Fake template to be replaced by something better
// TODO : Rework email templating system
function doFormatMail ($messageToSendToUser)
{
    $outputHTML="Leantime has news for you...<br/>\n";
    foreach ($messageToSendToUser as $chunk)
    {
         $outputHTML.="<div style=\"border: 1px solid grey; margin: 3px; padding: 3px;\">\n";
         $outputHTML.="<div style=\"float : right\">".$chunk['thedate']."</div>\n";
         $outputHTML.="<div>".$chunk['message']."</div>\n";
         $outputHTML.="</div>\n";
    }
    return $outputHTML;
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

// Release the semaphore for next thread
flock($fp, LOCK_UN);
fclose($fp);

if(ob_get_length() > 0) {
    ob_end_flush();
}
