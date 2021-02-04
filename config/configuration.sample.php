<?php
namespace leantime\core;

class config
{
  /* General */
  public $sitename = "Leantime"; //Name of your site, can be changed later
  public $language = "en-US"; //Default language
  public $mainColor = "1b75bb"; //Default color, can be changed later
  public $logoPath = "/images/logo.png"; //Default logo path, can be changed later
  public $appUrl = ""; //Base URL, trailing slash not needed

  /* Database */
  public $dbHost="localhost"; //Database host
  public $dbUser=""; //Database username
  public $dbPassword=""; //Database password
  public $dbDatabase=""; //Database name

  /* Fileupload */
  public $userFilePath= "userfiles/"; //Local relative path to store uploaded files (if not using S3)

  public $dbBackupPath= "backupdb/"; //Local relative path to store backup files, need permission 0777
            
  public $useS3 = false; //Set to true if you want to use S3 instead of local files
  public $s3Key = ""; //S3 Key
  public $s3Secret = ""; //S3 Secret
  public $s3Bucket = ""; //Your S3 bucket
  public $s3UsePathStyleEndpoint = false; // false => https://[bucket].[endpoint] ; true => https://[endpoint]/[bucket]
  public $s3Region = ""; //S3 region
  public $s3FolderName = ""; //Foldername within S3 (can be emtpy)
  public $s3EndPoint = ""; //S3 EndPoint S3 Compatible (https://sfo2.digitaloceanspaces.com)
  
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

  function __construct(){
    /* General */
    $this->sitename = $this->configEnvironmentHelper("LEAN_SITENAME", $this->sitename);
    $this->language = $this->configEnvironmentHelper("LEAN_LANGUAGE", $this->language);
    $this->mainColor = $this->configEnvironmentHelper("LEAN_MAIN_COLOR", $this->mainColor);
    $this->logoPath = $this->configEnvironmentHelper("LEAN_LOGO_PATH", $this->logoPath);
    $this->appUrl = $this->configEnvironmentHelper("LEAN_APP_URL", $this->appUrl);

    /* Database */
    $this->dbHost = $this->configEnvironmentHelper("LEAN_DB_HOST", $this->dbHost);
    $this->dbUser = $this->configEnvironmentHelper("LEAN_DB_USER", $this->dbUser);
    $this->dbPassword = $this->configEnvironmentHelper("LEAN_DB_PASSWORD", $this->dbPassword);
    $this->dbDatabase = $this->configEnvironmentHelper("LEAN_DB_DATABASE", $this->dbDatabase);

    /* Fileupload */
    $this->userFilePath= $this->configEnvironmentHelper("LEAN_USER_FILE_PATH", $this->userFilePath);
              
    $this->useS3 = $this->configEnvironmentHelper("LEAN_USE_S3", $this->useS3, "boolean");
    $this->s3EndPoint = $this->configEnvironmentHelper("LEAN_S3_END_POINT", $this->s3EndPoint);
    $this->s3Key = $this->configEnvironmentHelper("LEAN_S3_KEY", $this->s3Key);
    $this->s3Secret = $this->configEnvironmentHelper("LEAN_S3_SECRET", $this->s3Secret);
    $this->s3Bucket = $this->configEnvironmentHelper("LEAN_S3_BUCKET", $this->s3Bucket);
    $this->s3UsePathStyleEndpoint = $this->configEnvironmentHelper("LEAN_S3_USE_PATH_STYLE_ENDPOINT", $this->s3UsePathStyleEndpoint, "boolean");
    $this->s3Region = $this->configEnvironmentHelper("LEAN_S3_REGION", $this->s3Region);
    $this->s3FolderName = $this->configEnvironmentHelper("LEAN_S3_FOLDER_NAME", $this->s3FolderName);
              
    /* Sessions */
    $this->sessionpassword = $this->configEnvironmentHelper("LEAN_SESSION_PASSWORD", $this->sessionpassword);
    $this->sessionExpiration = $this->configEnvironmentHelper("LEAN_SESSION_EXPIRATION", $this->sessionExpiration, "number");

    /* Email */
    $this->email = $this->configEnvironmentHelper("LEAN_EMAIL_RETURN", $this->email);
    $this->useSMTP = $this->configEnvironmentHelper("LEAN_EMAIL_USE_SMTP", $this->useSMTP, "boolean");
    $this->smtpHosts = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_HOSTS", $this->smtpHosts);
    $this->smtpUsername = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_USERNAME", $this->smtpUsername);
    $this->smtpPassword = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_PASSWORD", $this->smtpPassword);
    $this->smtpAutoTLS = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_AUTO_TLS", $this->smtpAutoTLS, "boolean");
    $this->smtpSecure = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_SECURE", $this->smtpSecure);
    $this->smtpPort = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_PORT", $this->smtpPort);

  }

  private function configEnvironmentHelper($envVar, $default, $dataType = "string") {
    $found = getenv($envVar);
    if(!$found || $found == ""){
      return $default;
    }

    // we need to check to see if we need to conver the found data
    if($dataType == "string"){
      return $found;
    } else if($dataType == "boolean"){
      // if the string is true, then it is true, simple enough
      return $found == "true" ? true : false;
    } else if($dataType == "number"){
      return intval($found);
    }
    return $found;

  }
}