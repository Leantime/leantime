<?php
namespace leantime\core;

class config
{
	/* General */
	public $sitename = "LeanTime"; //Name of your site, can be changed later
	public $language = "en"; //Default language
    public $mainColor = "1b75bb"; //Default color, can be changed later
    public $logoPath = "/logo.png"; //Default logo path, can be changed later
    public $appUrl = ""; //Base URL, trailing slash not needed

    /* Database */
    // public $dbHost="localhost"; //Database host
	// public $dbUser=""; //Database username
	// public $dbPassword=""; //Database password
	// public $dbDatabase=""; //Database name

	public $dbHost = 'localhost';                         //Database host
    public $dbUser = '';                                  //Database username
    public $dbPassword = '';                              //Database password
    public $dbDatabase = '';                              //Database name
    public $dbPort = '3306'; 
	

	/*icFile Path*/
	public $icsFilePath='/home/callaw3/callaw3.dreamhosters.com/pm/public/event.ics';

        /* Fileupload */
	public $userFilePath= "userfiles/"; //Local relative path to store uploaded files (if not using S3)
								
	public $useS3 = false; //Set to true if you want to use S3 instead of local files
	public $s3Key = ""; //S3 Key
	public $s3Secret = ""; //S3 Secret
	public $s3Bucket = ""; //Your S3 bucket
	public $s3Region = ""; //S3 region
	public $s3FolderName = ""; //Foldername within S3 (can be emtpy)
								
	/* Sessions */
	public $sessionpassword = "3evBlq9zdUEuzKvVJHWWx3QzsQhturBApxwcws2m"; //Salting sessions. Replace with a strong password
    public $sessionExpiration = 28800; //How many seconds after inactivity should we logout?  28800seconds = 8hours

	/* Email */
	public $email = ""; //Return email address
	public $useSMTP = false; //Use SMTP? If set to false, the default php mail() function will be used
	public $smtpHosts = ""; //SMTP host
	public $smtpUsername =""; //SMTP username
	public $smtpPassword = ""; //SMTP password
	public $smtpAutoTLS = true; //SMTP Enable TLS encryption automatically if a server supports it
	public $smtpSecure =""; //SMTP Security protocol (usually one of: TLS, SSL, STARTTLS)
	public $smtpPort = ""; //Port (usually one of 25, 465, 587, 2526)

}