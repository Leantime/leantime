<?php

namespace leantime\core;

/**
 * Default Configuration Class
 *
 * @package    leantime
 * @subpackage core
 */
class config
{
    # General =====================================================================================
    /**
     * @var string Name of your site, can be changed later
     */
    public $sitename = 'Leantime';

    /**
     * @var string Default language
     */
    public $language = 'en-US';

    /**
     * @var string Default logo path, can be changed later
     */
    public $logoPath = '/dist/images/logo.svg';

    /**
     * @var string Default logo URL use for printing (must be jpg or png format)
     */
    public $printLogoURL = '/dist/images/logo.jpg';

    /**
     * @var string Base URL, trailing slash not needed
     */
    public $appUrl = '';

    /**
     * @var string Base of application withotu trailing slash (used for cookies), e.g, /leantime
     */
    public $appUrlRoot = '';

    /**
     * @var string Default theme
     */
    public $defaultTheme = 'default';

    /**
     * @var string Primary Theme color
     */
    public $primarycolor = '#1b75bb';

    /**
     * @var string Secondary Theme Color
     */
    public $secondarycolor = '#81B1A8';

    /**
     * @var string Default timezone
     */
    public $defaultTimezone = 'America/Los_Angeles';

    /**
     * @var bool Enable to specifiy menu on a project by project basis
     */
    public $enableMenuType = false;

    /**
     * @var bool Keep theme and language from previous user for login screen
     */
    public $keepTheme = true;

    /**
     * @var bool|int Debug flag
     */
    public $debug = 0;


    # Database ====================================================================================
    /**
     * @var string Database host
     */
    public $dbHost = 'localhost';

    /**
     * @var string Database username
     */
    public $dbUser = '';

    /**
     * @var string Database password
     */
    public $dbPassword = '';

    /**
     * @var string Database name
     */
    public $dbDatabase = '';

    /**
     * @var string Database port
     */
    public $dbPort = '3306';


    # Fileupload ==================================================================================
    /**
     * @var string Local relative path to store uploaded files (if not using S3)
     */
    public $userFilePath = 'userfiles/';

    /**
     * @var string Local relative path to store backup files, need permission to write
     */
    public $dbBackupPath = 'backupdb/';


    # S3 configuration ==============================================================================
    /**
     * @var bool Set to true if you want to use S3 instead of local files
     */
    public $useS3 = false;

    /**
     * @var string S3 Key
     */
    public $s3Key = '';

    /**
     * @var string S3 Secret
     */
    public $s3Secret = '';

    /**
     * @var string S3 Bucket
     */
    public $s3Bucket = '';

    /**
     * @var bool false => https://[bucket].[endpoint] ; true => https://[endpoint]/[bucket]
     */
    public $s3UsePathStyleEndpoint = false;

    /**
     * @var string S3 Region
     */
    public $s3Region = '';

    /**
     * @var string S3 Foldername within S3 (can be empty)
     */
    public $s3FolderName = '';

    /**
     * @var string S3 EndPoint S3 Compatible
     * @see https://sfo2.digitaloceanspaces.com
     */
    public $s3EndPoint = null;


    # Sessions ====================================================================================
    /**
     * @var string Salting sessions. Replace with a strong password
     */
    public $sessionpassword = '3evBlq9zdUEuzKvVJHWWx3QzsQhturBApxwcws2m';

    /**
     * @var int How many seconds after inactivity should we logout?  28800seconds = 8hours
     */
    public $sessionExpiration = 28800;


    # Email =======================================================================================
    /**
     * @var string Return email address
     */
    public $email = '';

    /**
     * @var bool Use SMTP? If set to false, the default php mail() function will be used
     */
    public $useSMTP = false;

    /**
     * @var string SMTP host
     */
    public $smtpHosts = '';

    /**
     * @var bool SMTP use user/password authentication
     */
    public $smtpAuth = true;

    /**
     * @var string SMTP username
     */
    public $smtpUsername = '';

    /**
     * @var string SMTP password
     */
    public $smtpPassword = '';

    /**
     * @var bool SMTP Enable TLS encryption automatically if a server supports it
     */
    public $smtpAutoTLS = true;

    /**
     * @var string SMTP Security protocol (usually one of: TLS, SSL, STARTTLS)
     */
    public $smtpSecure = '';

    /**
     * @var bool SMTP Allow insecure SSL: Don't verify certificate, accept self-signed, etc.
     */
    public $smtpSSLNoverify = false;

    /**
     * @var int SMTP Port (usually one of 25, 465, 587, 2526)
     */
    public $smtpPort = '';


    # ldap default settings (can be changed in company settings) ==================================
    /**
     * @var bool Set to true if you want to use LDAP
     */
    public $useLdap = false;

    /**
     * @var string Select the correct directory type. Currently Supported: OL - OpenLdap, AD - Active Directory
     */
    public $ldapType = 'OL';

    /**
     * @var string LDAP host (FQDN)
     */
    public $ldapHost = '';

    /**
     * @var string LDAP port
     */
    public $ldapPort = 389;

    /**
     * @var string LDAP domain
     */
    public $ldapDomain = '';

    /**
     * @var string LDAP base DN
     */
    public $ldapUri = '';

    /**
     * @var string Location of users, example: CN=users,DC=example,DC=com
     */
    public $ldapDn = '';

    /**
     * @var string Default LDAP keys in your directory. Works for OL
     */
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

    /**
     * @var string Default role assignments upon first login. (Optional) Can be updated in user settings for each user
     */
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
               //Default Leantime Role on creation. (set to editor)
    /**
     * @var ?string Comma separated list of plugins that will always be loaded
     */
    public ?string $plugins = '';

    /**
     * @var int Default Leantime Role on creation. (set to editor)
     */
    public $ldapDefaultRoleKey = 20;
}
