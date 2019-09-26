<?php
namespace leantime\core;

class config
{
    /* General */
    public $sitename = "Leantime";
    public $language = "en";
    public $mainColor = "1b75bb";
    public $logoPath = "/images/logo.png";

    /* Database */
    public $dbHost="localhost"; //Comes from client db config
    public $dbUser="root";
    public $dbPassword="";
    public $dbDatabase="leantimeos";

    /* Fileupload */
    public $userFilePath= "userfiles/";

    public $useS3 = false;
    public $s3Key = "";
    public $s3Secret = "";
    public $s3Bucket = "";
    public $s3Region = "";
    public $s3FolderName = "";

    /* Sessions */
    public $sessionpassword = "3evBlq9zdUEuzKvVJHWWx3QzsQhturBApxwcws2m"; //Replace with a strong password
    public $sessionExpiration = 28800; //How many seconds after inactivity should we logout?  28800seconds = 8hours

    /* Email */
    public $email = "";
    public $useSMTP = true;
    public $smtpHosts = "";		  // Specify main and backup SMTP servers
    public $smtpUsername ="";
    public $smtpPassword = "";
    public $smtpSecure ="";
    public $smtpPort = "";

}
