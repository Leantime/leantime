<?php

namespace Leantime\Core\Configuration;

/**
 * Default Configuration Class
 *
 * @package    leantime
 * @subpackage core
 */
class DefaultConfig
{
    # General =====================================================================================
    /**
     * @var string Name of your site, can be changed later
     */
    public string $sitename = 'Leantime';

    /**
     * @var string Default language
     */
    public string $language = 'en-US';

    /**
     * @var string Default logo path, can be changed later
     */
    public string $logoPath = '/dist/images/logo.svg';

    /**
     * @var string Default logo URL use for printing (must be jpg or png format)
     */
    public string $printLogoURL = '/dist/images/logo.jpg';

    /**
     * @var string Base URL, trailing slash not needed
     */
    public string $appUrl = '';

    /**
     * @var string Base of application withotu trailing slash (used for cookies), e.g, /leantime
     */
    public string $appDir = '';

    /**
     * @var string Default theme
     */
    public string $defaultTheme = 'default';

    /**
     * @var string Primary Theme color
     */
    public string $primarycolor = '#1b75bb';

    /**
     * @var string Secondary Theme Color
     */
    public string $secondarycolor = '#81B1A8';

    /**
     * @var string Default timezone
     */
    public string $defaultTimezone = 'America/Los_Angeles';

    /**
     * @var bool Enable to specifiy menu on a project by project basis
     */
    public bool $enableMenuType = false;

    /**
     * @var bool|int Debug flag
     */
    public int|bool $debug = 0;

    /**
     * @var string editor used for code editing
     */
    public string $editor = 'phpstorm';

    /**
     * @var environment
     */
    public string $env = 'production';

    /**
     * @var string Log Path
     */
    public string $logPath = APP_ROOT . '/logs/error.log';

    /**
     * @var bool Whether or not to enable the Poor Man's Cron fallback
     */
    public bool $poorMansCron = true;

    /**
     * @var bool Don't show user/pass form on login?
     */
    public bool $disableLoginForm = false;


    # Database ====================================================================================
    /**
     * @var string Database host
     */
    public string $dbHost = 'localhost';

    /**
     * @var string Database username
     */
    public string $dbUser = '';

    /**
     * @var string Database password
     */
    public string $dbPassword = '';

    /**
     * @var string Database name
     */
    public string $dbDatabase = '';

    /**
     * @var string Database port
     */
    public string $dbPort = '3306';


    # Fileupload ==================================================================================
    /**
     * @var string Local relative path to store uploaded files (if not using S3)
     */
    public string $userFilePath = 'userfiles/';

    /**
     * @var string Local relative path to store backup files, need permission to write
     */
    public string $dbBackupPath = 'userfiles/';


    # S3 configuration ============================================================================
    /**
     * @var bool Set to true if you want to use S3 instead of local files
     */
    public bool $useS3 = false;

    /**
     * @var string S3 Key
     */
    public string $s3Key = '';

    /**
     * @var string S3 Secret
     */
    public string $s3Secret = '';

    /**
     * @var string S3 Bucket
     */
    public string $s3Bucket = '';

    /**
     * @var bool false => https://[bucket].[endpoint] ; true => https://[endpoint]/[bucket]
     */
    public bool $s3UsePathStyleEndpoint = false;

    /**
     * @var string S3 Region
     */
    public string $s3Region = '';

    /**
     * @var string S3 Foldername within S3 (can be empty)
     */
    public string $s3FolderName = '';

    /**
     * @var string|null S3 EndPoint S3 Compatible
     * @see https://sfo2.digitaloceanspaces.com
     */
    public ?string $s3EndPoint = null;


    # Sessions ====================================================================================
    /**
     * @var string Salting sessions. Replace with a strong password
     */
    public string $sessionPassword = '3evBlq9zdUEuzKvVJHWWx3QzsQhturBApxwcws2m';

    /**
     * @var int How many seconds after inactivity should we logout?  28800seconds = 8hours
     */
    public int $sessionExpiration = 28800;


    # Email =======================================================================================
    /**
     * @var string Return email address
     */
    public string $email = '';

    /**
     * @var bool Use SMTP? If set to false, the default php mail() function will be used
     */
    public bool $useSMTP = false;

    /**
     * @var string SMTP host
     */
    public string $smtpHosts = '';

    /**
     * @var bool SMTP use user/password authentication
     */
    public bool $smtpAuth = true;

    /**
     * @var string SMTP username
     */
    public string $smtpUsername = '';

    /**
     * @var string SMTP password
     */
    public string $smtpPassword = '';

    /**
     * @var bool SMTP Enable TLS encryption automatically if a server supports it
     */
    public bool $smtpAutoTLS = true;

    /**
     * @var string SMTP Security protocol (usually one of: TLS, SSL, STARTTLS)
     */
    public string $smtpSecure = '';

    /**
     * @var bool SMTP Allow insecure SSL: Don't verify certificate, accept self-signed, etc.
     */
    public bool $smtpSSLNoverify = false;

    /**
     * @var int SMTP Port (usually one of 25, 465, 587, 2526)
     */
    public int $smtpPort = 587;


    # ldap default settings (can be changed in company settings) ==================================
    /**
     * @var bool Set to true if you want to use LDAP
     */
    public bool $useLdap = false;

    /**
     * @var string Select the correct directory type. Currently Supported: OL - OpenLdap, AD - Active Directory
     */
    public string $ldapType = 'OL';

    /**
     * @var string LDAP host (FQDN)
     */
    public string $ldapHost = '';

    /**
     * @var int LDAP port
     */
    public int $ldapPort = 389;

    /**
     * @var string LDAP domain
     */
    public string $ldapDomain = '';

    /**
     * @var string LDAP base DN
     */
    public string $ldapUri = '';

    /**
     * @var string Location of users, example: CN=users,DC=example,DC=com
     */
    public string $ldapDn = '';

    /**
     * @var string Default LDAP keys in your directory. Works for OL
     */
    public string $ldapKeys = '{
        "username":"uid",
        "groups":"memberof",
        "email":"mail",
        "firstname":"displayname",
        "lastname":"",
        "phone":"",
        "jobTitle":"title",
        "jobLevel":"level",
        "department":"department"
        }';
    //For AD use
    /*
      public $ldapKeys = '{
      "username":"cn",
      "groups":"memberof",
      "email":"mail",
      "firstname":"givenname",
      "lastname":"sn",
      "phone":"telephoneNumber",
      "jobTitle":"title",
      "jobLevel":"level",
      "department":"department"
      }';
     */

    /**
     * @var bool Create users
     * Create user if not exists
     *
     */
    public bool $ldapCreateUser = false;

    /**
     * @var string Default role assignments upon first login. (Optional) Can be updated in user settings for each user
     */
    public string $ldapLtGroupAssignments = '{
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
     * @var int Default Leantime Role on creation. (set to editor)
     */
    public int $ldapDefaultRoleKey = 20;

    # Plugin Settings ==============================================================================
    /**
     * @var string Comma separated list of plugins that will always be loaded
     */
    public string $plugins = '';

    /**
     * @var string The Url of the Marketplace
     **/
    public string $marketplaceUrl = 'https://marketplace.leantime.io/';

    # OIDC Settings ================================================================================
    /**
     * @var bool Set to true if you want to use OIDC
     */
    public bool $oidcEnable = false;

    /**
     * @var string OIDC Provider URL
     */
    public string $oidcProviderUrl = '';

    /**
     * @var string OIDC Client ID
     */
    public string $oidcClientId = '';

    /**
     * @var string OIDC Client Secret
     */
    public string $oidcClientSecret = '';

    /**
     * @var string OIDC Auth URL
     */
    public string $oidcAuthUrl = '';

    /**
     * @var string OIDC Token URL
     */
    public string $oidcTokenUrl = '';

    /**
     * @var string OIDC JWKS URL
     */
    public string $oidcJwksUrl = '';

    /**
     * @var string OIDC User Info URL
     */
    public string $oidcUserInfoUrl = '';

    /**
     * @var string OIDC Certificate String
     */
    public string $oidcCertificateString = '';

    /**
     * @var string OIDC Certificate File
     */
    public string $oidcCertificateFile = '';

    /**
     * @var string OIDC Scopes
     */
    public string $oidcScopes = 'openid profile email';

    /**
     * @var bool create user
     *
     * Create user if not exists
     *
     */
    public bool $oidcCreateUser = false;

    /**
     * @var string OIDC
     *
     * Default Role for new users
     *
     */
    public int $oidcDefaultRole = 20;

    /**
     * @var string OIDC Field Email
     */
    public string $oidcFieldEmail = 'email';

    /**
     * @var string OIDC Field First Name
     */
    public string $oidcFieldFirstName = 'given_name';

    /**
     * @var string OIDC Field Last Name
     */
    public string $oidcFieldLastName = 'family_name';

    /**
     * @var string OIDC Field Phone
     */
    public string $oidcFieldPhone = '';

    /**
     * @var string OIDC Field Job Title
     */
    public string $oidcFieldJobtitle = '';

    /**
     * @var string OIDC Field Job Level
     */
    public string $oidcFieldJoblevel = '';

    /**
     * @var string OIDC Field Department
     */
    public string $oidcFieldDepartment = '';


    # Redis Settings ===============================================================================
    /**
     * @var bool Set to true if you want to use Redis
     */
    public bool $useRedis = false;

    /**
     * @var string Redis URL
     */
    public string $redisUrl = '';

    /**
     * @var string Redis Host
     */
    public string $redisHost = '127.0.0.1';

    /**
     * @var string Redis Port
     */
    public string $redisPort = '6379';

    /**
     * @var string Redis Password
     */
    public string $redisPassword = '';

    /**
     * @var string Redis Cluster
     */
    public string $redisCluster = '';

    /**
     * @var string Redis Prefix
     */
    public string $redisPrefix = 'ltRedis';

    # Security/Rate Limiting Settings ===============================================================================
    /**
     * @var string trusted Proxies
     */
    public string $trustedProxies = '127.0.0.1,REMOTE_ADDR';

    /**
     * @var int rate limit on all requests
     */
    public int $ratelimitGeneral = 2000;

    /**
     * @var int rate limit on api requests
     */
    public int $ratelimitApi = 10;

    /**
     * @var int rate limit on auth requests
     */
    public int $ratelimitAuth = 20;
}
