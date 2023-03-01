<?php

namespace leantime\core;

class config
{
    /* General */

    public $sitename = 'Leantime';                        //Name of your site, can be changed later
    public $language = 'en-US';                           //Default language
    public $logoPath = '/images/logo.svg';                //Default logo path, can be changed later
    public $printLogoURL = '/images/logo.jpg';            //Default logo URL use for printing (must be jpg or png format)
    public $appUrl = '';                                  //Base URL, trailing slash not needed
    public $appUrlRoot = '';                              //Base of application withotu trailing slash (used for cookies), e.g, /leantime
    public $defaultTheme = 'default';                     //Default theme
    public $primarycolor = '#1b75bb';                     //Primary Theme color
    public $secondarycolor = '#81B1A8';                   //Secondary Theme Color
    public $defaultTimezone = 'America/Los_Angeles';      //Set default timezone
    public $enableMenuType = false;                       //Enable to specifiy menu on aproject by project basis
    public $keepTheme = true;                             //Keep theme and language from previous user for login screen
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
    public $s3EndPoint = null;                              //S3 EndPoint S3 Compatible (https://sfo2.digitaloceanspaces.com)

    /* Sessions */
    public $sessionpassword = '3evBlq9zdUEuzKvVJHWWx3QzsQhturBApxwcws2m';  //Salting sessions. Replace with a strong password
    public $sessionExpiration = 28800;                    //How many seconds after inactivity should we logout?  28800seconds = 8hours

    /* Email */
    public $email = '';                                   //Return email address
    public $useSMTP = false;                              //Use SMTP? If set to false, the default php mail() function will be used
    public $smtpHosts = '';                               //SMTP host
    public $smtpAuth = true;                              //SMTP use user/password authentication
    public $smtpUsername = '';                            //SMTP username
    public $smtpPassword = '';                            //SMTP password
    public $smtpAutoTLS = true;                           //SMTP Enable TLS encryption automatically if a server supports it
    public $smtpSecure = '';                              //SMTP Security protocol (usually one of: TLS, SSL, STARTTLS)
    public $smtpSSLNoverify = false;                      //SMTP Allow insecure SSL: Don't verify certificate, accept self-signed, etc.
    public $smtpPort = '';                                //Port (usually one of 25, 465, 587, 2526)

    /* ldap default settings (can be changed in company settings) */
    public $useLdap = false;                              //Set to true if you want to use LDAP
    public $ldapType = 'OL';                              //Select the correct directory type. Currently Supported: OL - OpenLdap, AD - Active Directory
    public $ldapHost = '';                                //FQDN
    public $ldapPort = 389;                               //Default Port
    public $ldapDn = '';                                  //Location of users, example: CN=users,DC=example,DC=com
    //Default ldap keys in your directory.
    //Works for OL
    public $ldapKeys = '{
        "username":"uid",
        "groups":"memberof",
        "email":"mail",
        "firstname":"displayname",
        "lastname":"",
        "phonenumber":""
        }';
    //For AD use
    /*
      public $ldapKeys = '{
      "username":"cn",
      "groups":"memberof",
      "email":"mail",
      "firstname":"givenname",
      "lastname":"sn",
      "phonenumber":"telephoneNumber"
      }';
     */


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
}
