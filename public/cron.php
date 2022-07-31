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

if ( $_GET['mode'] == "debug" )
{
    $DEBUG_CRON=true;
} else
{
    $DEBUG_CRON=false;
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
    $color = $settingsRepo->getSetting("companysettings.primaryColor");
    $sitename = $settingsRepo->getSetting("companysettings.sitename");

    if (strpos($logoPath, 'http') === 0) {
        $_SESSION["companysettings.logoPath"] =  $logoPath;
    }else{
        $_SESSION["companysettings.logoPath"] =  BASE_URL.$logoPath;
    }
    // echo for DEBUG PURPOSE
    //debug_print($_SESSION["companysettings.logoPath"]);

    $_SESSION["companysettings.mainColor"] = $color;
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

// NEW Queuing messaging system
$queue = new repositories\queue();

// We need users and settings and a mailer
$users = new repositories\users();
$settingsRepo = new leantime\domain\repositories\setting();
$mailer = new \leantime\core\mailer();

$messages=$queue->listMessageInQueue();

$allMessagesToSend=array();
$n=0;
foreach ($messages as $message) 
{
    $n++;
    $currentUserId=$message['userId'];

    $allMessagesToSend[$currentUserId][$message['msghash']]=Array(
        'thedate'=>$message['thedate'],
        'message'=> $message['message'],
	'projectId'=>$message['projectId']
    );
    // DONE here : here we need a message id to allow deleting messages of the queue when they are sent
    // and here we need to group the messages in an array to know which messages are grouped to group-delete them
    $allMessagesToDelete[$currentUserId][]=$message['msghash'];
}
foreach ($allMessagesToSend as $currentUserId => $messageToSendToUser)
{
    $theuser=$users->getUser($currentUserId);
    $recipient=$theuser['username'];

    // DONE : Deal with users parameters to allow them define a maximum (and minimum ?) frequency to receive mails
    // TODO : Update profile form to allow each user to edit his own messageFrequency option
    $lastMessageDate = strtotime($settingsRepo->getSetting("usersettings.".$theuser['id'].".lastMessageDate"));
    $nowDate = time();
    // echo for DEBUG PURPOSE
    debug_print( "Last message to ".$recipient." was on ".date('Y-m-d H:i:s', $lastMessageDate));
    $timeSince = abs($nowDate - $lastMessageDate);
    // echo for DEBUG PURPOSE
    debug_print("Time elapsed since : ".$timeSince);

    $messageFrequency=$settingsRepo->getSetting("usersettings.".$theuser['id'].".messageFrequency");
    // Check if there is a default value in DB
    if ( $messageFrequency == "" )
    {
        $messageFrequency=$settingsRepo->getSetting("usersettings.default.messageFrequency");
    }
    // Last security to avoid flooding people.
    if ( $messageFrequency == "" )
    {
        $messageFrequency=3600;
	$settingsRepo->saveSetting("usersettings.default.messageFrequency", 3600);
    }
    // echo for DEBUG PURPOSE
    debug_print( "The message frequency for ".$recipient." : ".$messageFrequency);

    if ($timeSince < $messageFrequency ) 
    {
        // echo for DEBUG PURPOSE
        debug_print( "Elapsed time not enough for ".$recipient." : skipping till ".date("Y-m-d H:i:s", $lastMessageDate+$messageFrequency));
	continue;
    }

    // TODO here : set up a true templating system to format the messages
    $formattedHTML=doFormatMail($messageToSendToUser);

    // TODO Tranlastion needed somewhere ? 
    
    // DONE : Send the message with PHPMailer here
    $mailer->setSubject("Leantime notification");
    $mailer->setHtml($formattedHTML);
    $to = array($recipient);
    $mailer->sendMail($to, "Leantime System");

    // Delete the corresponding messages from the queue when the mail is sent
    // TODO here : only delete these if the send was successful
    // echo for DEBUG PURPOSE
    debug_print( "Messages send (about to delete) :");
    print_r($allMessagesToDelete[$currentUserId]);
    $queue->deleteMessageInQueue($allMessagesToDelete[$currentUserId]);

    // Store the last time a mail was sent to $recipient email
    $thedate=date('Y-m-d H:i:s');
    $settingsRepo->saveSetting("usersettings.".$theuser['id'].".lastMessageDate", $thedate);
  
}
// echo for DEBUG PURPOSE
debug_print( "cron end");

// Release the semaphore for next thread
flock($fp, LOCK_UN);
fclose($fp);

if(ob_get_length() > 0) {
    ob_end_flush();
}
