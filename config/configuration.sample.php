<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

namespace leantime\core;

class config
{
    /* General */

    public $sitename = 'Leantime';                        //Name of your site, can be changed later
    public $language = 'en-US';                           //Default language
    public $logoPath = '/images/logo.svg';                //Default logo path, can be changed later
    public $appUrl = '';                                  //Base URL, trailing slash not needed
    public $primarycolor = '#1b75bb';                     //Primary Theme color
    public $secondarycolor = '#81B1A8';                   //Secondary Theme Color
    public $defaultTimezone = 'America/Los_Angeles';      //Set default timezone
    public $debug = 0;                                    //Debug flag


    /* Database */
    public $dbHost = 'localhost';                         //Database host
    public $dbUser = '';                                  //Database username
    public $dbPassword = '';                              //Database password
    public $dbDatabase = '';                              //Database name
    public $dbPort = '3306';                              //Database port

    /* Fileupload */
    public $userFilePath = 'userfiles/';                  //Local relative path to store uploaded files (if not using S3)
    public $dbBackupPath = 'backupdb/';                   //Local relative path to store backup files, need permission to write

    /* S3 configuration */
    public $useS3 = false;                                //Set to true if you want to use S3 instead of local files
    public $s3Key = '';                                   //S3 Key
    public $s3Secret = '';                                //S3 Secret
    public $s3Bucket = '';                                //Your S3 bucket
    public $s3UsePathStyleEndpoint = false;               // false => https://[bucket].[endpoint] ; true => https://[endpoint]/[bucket]
    public $s3Region = '';                                //S3 region
    public $s3FolderName = '';                            //Foldername within S3 (can be emtpy)
    public $s3EndPoint = '';                              //S3 EndPoint S3 Compatible (https://sfo2.digitaloceanspaces.com)

    /* Sessions */
    public $sessionpassword = '3evBlq9zdUEuzKvVJHWWx3QzsQhturBApxwcws2m';  //Salting sessions. Replace with a strong password
    public $sessionExpiration = 28800;                    //How many seconds after inactivity should we logout?  28800seconds = 8hours

    /* Email */
    public $email = '';                                   //Return email address
    public $useSMTP = false;                              //Use SMTP? If set to false, the default php mail() function will be used
    public $smtpHosts = '';                               //SMTP host
    public $smtpUsername = '';                            //SMTP username
    public $smtpPassword = '';                            //SMTP password
    public $smtpAutoTLS = true;                           //SMTP Enable TLS encryption automatically if a server supports it
    public $smtpSecure = '';                              //SMTP Security protocol (usually one of: TLS, SSL, STARTTLS)
    public $smtpPort = '';                                //Port (usually one of 25, 465, 587, 2526)

    /*ldap default settings (can be changed in company settings) */
    public $useLdap = false;                              //Set to true if you want to use LDAP
    public $ldapType = 'OL';                              //Select the correct directory type. Currently Supported: OL - OpenLdap, AD - Active Directory
    public $ldapHost = '';                                //FQDN
    public $ldapPort = 389;                               //Default Port
    public $baseDn = '';                                  //Base DN, example: DC=example,DC=com
    public $ldapDn = '';                                  //Location of users, example: CN=users,DC=example,DC=com
    public $ldapUserDomain = '';                          //Domain after ldap, example @example.com
    public $bindUser = '';                                //ldap user that can search directory. (Should be read only)
    public $bindPassword = '';                            //Default ldap keys in your directory.
    public $ldapKeys = '{ 
        "username":"uid",
        "groups":"memberof",
        "email":"mail",
        "firstname":"displayname",
        "lastname":"",
        "phonenumber":""
        }';
//Default role assignments upon first login. (Optional) Can be updated in user settings for each user
    public $ldapLtGroupAssignments = '{
          "5": {
            "ltRole":"readonly",
            "ldapRole":""
          },
          "10": {
            "ltRole":"commenter",
            "ldapRole":""
          },
          "20": {
            "ltRole":"editor",
            "ldapRole":""
          },
          "30": {
            "ltRole":"manager",
            "ldapRole":""
          },
          "40": {
            "ltRole":"admin",
            "ldapRole":""
          },
          "50": {
            "ltRole":"owner",
            "ldapRole":"administrators"
          }
        }';
    public $ldapDefaultRoleKey = 20;           //Default Leantime Role on creation. (set to editor)

    /* cache invalidation */
    private $configurationLastModified = '';   //Last modified date of the configuration file

    public function __construct()
    {
        //Get the last modified configuration timestamp.
        $this->configurationLastModified = filemtime('../config/configuration.php');

        //if config file is newer then when the session cache was created, remove the session cache.
        if( isset($_SESSION['mainconfig']['configurationLastModified']) && $_SESSION['mainconfig']['configurationLastModified'] != $this->configurationLastModified) {
            unset($_SESSION['mainconfig']);
        }

        //set configurationLastModified (if missing)
        if( ! isset($_SESSION['mainconfig']['configurationLastModified']) ){
          $_SESSION['mainconfig']['configurationLastModified'] = $this->configurationLastModified;
        }

        /* General */
        $this->sitename = $this->configEnvironmentHelper("LEAN_SITENAME", $this->sitename);
        $this->language = $this->configEnvironmentHelper("LEAN_LANGUAGE", $this->language);
        $this->logoPath = $this->configEnvironmentHelper("LEAN_LOGO_PATH", $this->logoPath);
        $this->appUrl = $this->configEnvironmentHelper("LEAN_APP_URL", $this->appUrl);
        $this->primarycolor = $this->configEnvironmentHelper("LEAN_PRIMARY_COLOR", $this->primarycolor);
        $this->secondarycolor = $this->configEnvironmentHelper("LEAN_SECONDARY_COLOR", $this->secondarycolor);
        $this->debug = $this->configEnvironmentHelper("LEAN_DEBUG", $this->debug);
        $this->defaultTimezone = $this->configEnvironmentHelper("LEAN_DEFAULT_TIMEZONE", $this->defaultTimezone);

    /* Database */
        $this->dbHost = $this->configEnvironmentHelper("LEAN_DB_HOST", $this->dbHost);
        $this->dbUser = $this->configEnvironmentHelper("LEAN_DB_USER", $this->dbUser);
        $this->dbPassword = $this->configEnvironmentHelper("LEAN_DB_PASSWORD", $this->dbPassword);
        $this->dbDatabase = $this->configEnvironmentHelper("LEAN_DB_DATABASE", $this->dbDatabase);
        $this->dbPort = $this->configEnvironmentHelper("LEAN_DB_PORT", $this->dbPort);

    /* Fileupload */
        $this->userFilePath = $this->configEnvironmentHelper("LEAN_USER_FILE_PATH", $this->userFilePath);
        $this->useS3 = $this->configEnvironmentHelper("LEAN_USE_S3", $this->useS3, "boolean");
        if ($this->useS3) {
            $this->s3EndPoint = $this->configEnvironmentHelper("LEAN_S3_END_POINT", $this->s3EndPoint);
            $this->s3Key = $this->configEnvironmentHelper("LEAN_S3_KEY", $this->s3Key);
            $this->s3Secret = $this->configEnvironmentHelper("LEAN_S3_SECRET", $this->s3Secret);
            $this->s3Bucket = $this->configEnvironmentHelper("LEAN_S3_BUCKET", $this->s3Bucket);
            $this->s3UsePathStyleEndpoint = $this->configEnvironmentHelper("LEAN_S3_USE_PATH_STYLE_ENDPOINT", $this->s3UsePathStyleEndpoint, "boolean");
            $this->s3Region = $this->configEnvironmentHelper("LEAN_S3_REGION", $this->s3Region);
            $this->s3FolderName = $this->configEnvironmentHelper("LEAN_S3_FOLDER_NAME", $this->s3FolderName);
        }

    /* Sessions */
        $this->sessionpassword = $this->configEnvironmentHelper("LEAN_SESSION_PASSWORD", $this->sessionpassword);
        $this->sessionExpiration = $this->configEnvironmentHelper("LEAN_SESSION_EXPIRATION", $this->sessionExpiration, "number");

    /* Email */
        $this->email = $this->configEnvironmentHelper("LEAN_EMAIL_RETURN", $this->email);
        $this->useSMTP = $this->configEnvironmentHelper("LEAN_EMAIL_USE_SMTP", $this->useSMTP, "boolean");
        if ($this->useSMTP) {
            $this->smtpHosts = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_HOSTS", $this->smtpHosts);
            $this->smtpUsername = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_USERNAME", $this->smtpUsername);
            $this->smtpPassword = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_PASSWORD", $this->smtpPassword);
            $this->smtpAutoTLS = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_AUTO_TLS", $this->smtpAutoTLS, "boolean");
            $this->smtpSecure = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_SECURE", $this->smtpSecure);
            $this->smtpPort = $this->configEnvironmentHelper("LEAN_EMAIL_SMTP_PORT", $this->smtpPort);
        }

    /*ldap*/
        $this->useLdap = $this->configEnvironmentHelper("LEAN_LDAP_USE_LDAP", $this->useLdap, "boolean");
        if ($this->useLdap) {
            $this->ldapType = $this->configEnvironmentHelper("LEAN_LDAP_LDAP_TYPE", $this->ldapType);
            $this->ldapHost = $this->configEnvironmentHelper("LEAN_LDAP_HOST", $this->ldapHost);
            $this->ldapPort = $this->configEnvironmentHelper("LEAN_LDAP_PORT", $this->ldapPort);
            $this->baseDn = $this->configEnvironmentHelper("LEAN_LDAP_BASE_DN", $this->baseDn);
            $this->ldapDn = $this->configEnvironmentHelper("LEAN_LDAP_DN", $this->ldapDn);
            $this->ldapUserDomain = $this->configEnvironmentHelper("LEAN_LDAP_USER_DOMAIN", $this->ldapUserDomain);
            $this->ldapKeys = $this->configEnvironmentHelper("LEAN_LDAP_KEYS", $this->ldapKeys);
            $this->ldapLtGroupAssignments = $this->configEnvironmentHelper("LEAN_LDAP_GROUP_ASSIGNMENT", $this->ldapLtGroupAssignments);
            $this->ldapDefaultRoleKey = $this->configEnvironmentHelper("LEAN_LDAP_DEFAULT_ROLE_KEY", $this->ldapDefaultRoleKey);
        }
    }

    private function configEnvironmentHelper($envVar, $default, $dataType = "string")
    {

        if (isset($_SESSION['mainconfig'][$envVar])) {
            return $_SESSION['mainconfig'][$envVar];
        } else {
            $found = getenv($envVar);
            if (!$found || $found == "") {
                $_SESSION['mainconfig'][$envVar] = $default;
                return $default;
            }

            // we need to check to see if we need to conver the found data
            if ($dataType == "string") {
                $_SESSION['mainconfig'][$envVar] = $found;
            } elseif ($dataType == "boolean") {
            // if the string is true, then it is true, simple enough
                $_SESSION['mainconfig'][$envVar] = $found == "true" ? true : false;
            } elseif ($dataType == "number") {
                $_SESSION['mainconfig'][$envVar] = intval($found);
            }

            return $_SESSION['mainconfig'][$envVar];
        }
    }
}
