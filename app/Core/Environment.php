<?php

namespace Leantime\Core;

use ArrayAccess;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Support\Arr;

/**
 * environment - class To handle environment variables
 *
 * @package    leantime
 * @subpackage core
 */
class Environment implements ArrayAccess, ConfigContract
{
    # Config =====================================================================================
    /**
     * @var \Dotenv\Dotenv
     */
    public \Dotenv\Dotenv $dotenv;

    /**
     * @var ?object
     */
    public ?object $yaml;

    /**
     * @var \Leantime\Config\Config
     */
    public ?\Leantime\Config\Config $phpConfig;

    # General =====================================================================================
    /**
     * @var string Name of your site
     */
    public string $sitename;

    /**
     * @var string Language
     */
    public string $language;

    /**
     * @var string Logo path
     */
    public string $logoPath;

    /**
     * @var string Logo URL
     */
    public string $printLogoURL;

    /**
     * @var string Base URL, trailing slash not needed
     */
    public string $appUrl;

    /**
     * @var string Default theme
     */
    public string $defaultTheme;

    /**
     * @var string Primary Theme color
     */
    public string $primarycolor;

    /**
     * @var string Secondary Theme Color
     */
    public string $secondarycolor;

    /**
     * @var int Debug level
     */
    public bool|int $debug;

    /**
     * @var string Default timezone
     */
    public string $defaultTimezone;

    /**
     * @var boolean Enable to specifiy menu on a project by project basis
     */
    public bool $enableMenuType;

    /**
     * @var boolean Keep theme on login
     */
    public bool $keepTheme;

    /**
     * @var string Log path
     */
    public string $logPath;

    /**
     * @var string App URL Root
     * @todo this variables needs to be removed and generated programmatically.
     */
    public string $appDir;


    # Database =====================================================================================
    /**
     * @var string Database host
     */
    public string $dbHost;

    /**
     * @var string Database user
     */
    public string $dbUser;

    /**
     * @var string Database password
     */
    public string $dbPassword;

    /**
     * @var string Database name
     */
    public string $dbDatabase;

    /**
     * @var string Database port
     */
    public int $dbPort;


    # Fileupload =====================================================================================
    /**
     * @var string User file path
     */
    public string $userFilePath;

    /**
     * @var boolean Use S3
     */
    public bool $useS3;

    /**
     * @var string S3 End Point
     */
    public ?string $s3EndPoint;

    /**
     * @var string S3 Key
     */
    public string $s3Key;

    /**
     * @var string S3 Secret
     */
    public string $s3Secret;

    /**
     * @var string S3 Bucket
     */
    public string $s3Bucket;

    /**
     * @var ?bool S3 Use Path Style Endpoint
     */
    public ?bool $s3UsePathStyleEndpoint;

    /**
     * @var string S3 Region
     */
    public string $s3Region;

    /**
     * @var string S3 Folder Name
     */
    public string $s3FolderName;


    # Sessions =====================================================================================
    /**
     * @var string Session password
     */
    public string $sessionpassword;

    /**
     * @var integer Session expiration
     */
    public int $sessionExpiration;


    # Email =====================================================================================
    /**
     * @var string Email
     */
    public string $email;

    /**
     * @var boolean Use SMTP
     */
    public bool $useSMTP;

    /**
     * @var string SMTP Hosts
     */
    public string $smtpHosts;

    /**
     * @var boolean SMTP Auth
     */
    public bool $smtpAuth;

    /**
     * @var string SMTP Username
     */
    public string $smtpUsername;

    /**
     * @var string SMTP Password
     */
    public string $smtpPassword;

    /**
     * @var boolean SMTP Auto TLS
     */
    public bool $smtpAutoTLS;

    /**
     * @var string SMTP Secure
     */
    public string $smtpSecure;

    /**
     * @var integer SMTP Port
     */
    public int $smtpPort;

    /**
     * @var boolean SMTP SSL No Verify
     */
    public bool $smtpSSLNoverify;


    # LDAP =====================================================================================
    /**
     * @var boolean Use LDAP
     */
    public bool $useLdap;

    /**
     * @var string LDAP Type
     */
    public string $ldapType;

    /**
     * @var string LDAP Host
     */
    public string $ldapHost;

    /**
     * @var integer LDAP Port
     */
    public int $ldapPort;

    /**
     * @var string LDAP DN
     */
    public string $ldapDn;

    /**
     * @var string LDAP Keys
     */
    public string $ldapKeys;

    /**
     * @var string LDAP LT Group Assignments
     */
    public string $ldapLtGroupAssignments;

    /**
     * @var string LDAP Default Role Key
     */
    public string $ldapDefaultRoleKey;

    /**
     * @var string LDAP Domain
     */
    public string $ldapDomain;

    /**
     * @var string LDAP URI
     */
    public string $ldapUri;


    # OIDC =====================================================================================
    /**
     * @var boolean OIDC Enable
     */
    public bool $oidcEnable;

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
     * @var string OIDC Auth URL Override
     */
    public string $oidcAuthUrl = '';

    /**
     * @var string OIDC Token URL Override
     */
    public string $oidcTokenUrl = '';

    /**
     * @var string OIDC JWKS URL Override
     */
    public string $oidcJwksUrl = '';

    /**
     * @var string OIDC Userinfo URL Override
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
    public string $oidcScopes = '';

    /**
     * @var string OIDC Field Email
     */
    public string $oidcFieldEmail = '';

    /**
     * @var string OIDC Field First Name
     */
    public string $oidcFieldFirstName = '';

    /**
     * @var string OIDC Field Last Name
     */
    public string $oidcFieldLastName = '';


    # Redis =====================================================================================
    /**
     * @var boolean Use Redis
     */
    public bool $useRedis;

    /**
     * @var string Redis URL
     */
    public string $redisURL;

    /**
     * @var string list of default plugins
     */
    public ?string $plugins;

    /**
     * environment constructor.
     * @param \Leantime\Core\DefaultConfig $defaultConfiguration
     * @return self
     */
    public function __construct(DefaultConfig $defaultConfiguration)
    {

        /* PHP */
        $this->phpConfig = null;
        if (file_exists($phpConfigFile = APP_ROOT . "/config/configuration.php")) {
            require_once $phpConfigFile;
            $this->phpConfig = new \Leantime\Config\Config();
        }

        /* Dotenv */
        $this->dotenv = \Dotenv\Dotenv::createImmutable(APP_ROOT . "/config");
        $this->dotenv->safeLoad();

        /* YAML */
        $this->yaml = null;
        if (file_exists(APP_ROOT . "/config/config.yaml")) {
            $this->yaml = \Symfony\Component\Yaml\Yaml::parseFile(APP_ROOT . "/config/config.yaml");
        }

        /* General */
        $this->sitename = $this->environmentHelper("LEAN_SITENAME", $defaultConfiguration->sitename ?? 'Leantime');
        $this->language = $this->environmentHelper("LEAN_LANGUAGE", $defaultConfiguration->language ?? 'en-US');
        $this->logoPath = $this->environmentHelper("LEAN_LOGO_PATH", $defaultConfiguration->logoPath ?? '/dist/images/logo.svg');
        $this->printLogoURL = $this->environmentHelper("LEAN_PRINT_LOGO_URL", $defaultConfiguration->printLogoURL ?? '');
        $this->appUrl = $this->environmentHelper("LEAN_APP_URL", $defaultConfiguration->appUrl ?? '');
        $this->defaultTheme = $this->environmentHelper("LEAN_DEFAULT_THEME", $defaultConfiguration->defaultTheme ?? 'default');
        $this->primarycolor = $this->environmentHelper("LEAN_PRIMARY_COLOR", $defaultConfiguration->primarycolor ?? '#1b75bb');
        $this->secondarycolor = $this->environmentHelper("LEAN_SECONDARY_COLOR", $defaultConfiguration->secondarycolor ?? '#81B1A8');
        $this->debug = $this->environmentHelper("LEAN_DEBUG", $defaultConfiguration->debug ?? 0);
        $this->defaultTimezone = $this->environmentHelper("LEAN_DEFAULT_TIMEZONE", $defaultConfiguration->defaultTimezone ?? 'America/Los_Angeles');
        $this->enableMenuType = $this->environmentHelper("LEAN_ENABLE_MENU_TYPE", $defaultConfiguration->enableMenuType ?? false);
        $this->keepTheme = $this->environmentHelper("LEAN_KEEP_THEME", $defaultConfiguration->keepTheme ?? true);
        $this->logPath = $this->environmentHelper("LEAN_LOG_PATH", APP_ROOT . '/logs/error.log');

        /** @todo this variables needs to be removed and generated programmatically. */
        $this->appDir = $this->environmentHelper("LEAN_APP_DIR", $defaultConfiguration->appDir ?? '');

        /* Database */
        $this->dbHost = $this->environmentHelper("LEAN_DB_HOST", $defaultConfiguration->dbHost);
        $this->dbUser = $this->environmentHelper("LEAN_DB_USER", $defaultConfiguration->dbUser);
        $this->dbPassword = $this->environmentHelper("LEAN_DB_PASSWORD", $defaultConfiguration->dbPassword);
        $this->dbDatabase = $this->environmentHelper("LEAN_DB_DATABASE", $defaultConfiguration->dbDatabase);
        $this->dbPort = $this->environmentHelper("LEAN_DB_PORT", $defaultConfiguration->dbPort ?? '3306');

        /* Fileupload */
        $this->userFilePath = $this->environmentHelper("LEAN_USER_FILE_PATH", $defaultConfiguration->userFilePath ?? 'userfiles/');
        $this->useS3 = $this->environmentHelper("LEAN_USE_S3", $defaultConfiguration->useS3 ?? false, "boolean");
        if ($this->useS3) {
            $this->s3EndPoint = $this->environmentHelper("LEAN_S3_END_POINT", $defaultConfiguration->s3EndPoint ?? null);
            $this->s3Key = $this->environmentHelper("LEAN_S3_KEY", $defaultConfiguration->s3Key ?? '');
            $this->s3Secret = $this->environmentHelper("LEAN_S3_SECRET", $defaultConfiguration->s3Secret ?? '');
            $this->s3Bucket = $this->environmentHelper("LEAN_S3_BUCKET", $defaultConfiguration->s3Bucket ?? '');
            $this->s3UsePathStyleEndpoint = $this->environmentHelper("LEAN_S3_USE_PATH_STYLE_ENDPOINT", $defaultConfiguration->s3UsePathStyleEndpoint ?? false, "boolean");
            $this->s3Region = $this->environmentHelper("LEAN_S3_REGION", $defaultConfiguration->s3Region ?? '');
            $this->s3FolderName = $this->environmentHelper("LEAN_S3_FOLDER_NAME", $defaultConfiguration->s3FolderName ?? '');
        }

        /* Sessions */
        $this->sessionpassword = $this->environmentHelper("LEAN_SESSION_PASSWORD", $defaultConfiguration->sessionpassword);
        $this->sessionExpiration = $this->environmentHelper("LEAN_SESSION_EXPIRATION", $defaultConfiguration->sessionExpiration, "number");

        /* Email */
        $this->email = $this->environmentHelper("LEAN_EMAIL_RETURN", $defaultConfiguration->email ?? '');
        $this->useSMTP = $this->environmentHelper("LEAN_EMAIL_USE_SMTP", $defaultConfiguration->useSMTP ?? false, "boolean");
        if ($this->useSMTP) {
            $this->smtpHosts = $this->environmentHelper("LEAN_EMAIL_SMTP_HOSTS", $defaultConfiguration->smtpHosts ?? '');
            $this->smtpAuth = $this->environmentHelper("LEAN_EMAIL_SMTP_AUTH", $defaultConfiguration->smtpAuth ?? false, "boolean");
            $this->smtpUsername = $this->environmentHelper("LEAN_EMAIL_SMTP_USERNAME", $defaultConfiguration->smtpUsername ?? '');
            $this->smtpPassword = $this->environmentHelper("LEAN_EMAIL_SMTP_PASSWORD", $defaultConfiguration->smtpPassword ?? '');
            $this->smtpAutoTLS = $this->environmentHelper("LEAN_EMAIL_SMTP_AUTO_TLS", $defaultConfiguration->smtpAutoTLS ?? false, "boolean");
            $this->smtpSecure = $this->environmentHelper("LEAN_EMAIL_SMTP_SECURE", $defaultConfiguration->smtpSecure ?? '');
            $this->smtpPort = $this->environmentHelper("LEAN_EMAIL_SMTP_PORT", $defaultConfiguration->smtpPort ?? 465, "number");
            $this->smtpSSLNoverify = $this->environmentHelper("LEAN_EMAIL_SMTP_SSLNOVERIFY", $defaultConfiguration->smtpSSLNoverify ?? false, "boolean");
        }

        /* ldap */
        $this->useLdap = $this->environmentHelper("LEAN_LDAP_USE_LDAP", $defaultConfiguration->useLdap ?? false, "boolean");
        if ($this->useLdap) {
            $this->ldapType = $this->environmentHelper("LEAN_LDAP_LDAP_TYPE", $defaultConfiguration->ldapType ?? '');
            $this->ldapHost = $this->environmentHelper("LEAN_LDAP_HOST", $defaultConfiguration->ldapHost ?? '');
            $this->ldapPort = $this->environmentHelper("LEAN_LDAP_PORT", $defaultConfiguration->ldapPort ?? '');
            $this->ldapDn = $this->environmentHelper("LEAN_LDAP_DN", $defaultConfiguration->ldapDn ?? '') ;
            $this->ldapKeys = $this->environmentHelper("LEAN_LDAP_KEYS", $defaultConfiguration->ldapKeys ?? '');
            $this->ldapLtGroupAssignments = $this->environmentHelper("LEAN_LDAP_GROUP_ASSIGNMENT", $defaultConfiguration->ldapLtGroupAssignments ?? '') ;
            $this->ldapDefaultRoleKey = $this->environmentHelper("LEAN_LDAP_DEFAULT_ROLE_KEY", $defaultConfiguration->ldapDefaultRoleKey ?? '');
            $this->ldapDomain = $this->environmentHelper("LEAN_LDAP_LDAP_DOMAIN", $defaultConfiguration->ldapDomain ?? '');
            $this->ldapUri = $this->environmentHelper("LEAN_LDAP_URI", $defaultConfiguration->ldapUri ?? '');
        }

        /* OIDC */
        $this->oidcEnable = $this->getBool('LEAN_OIDC_ENABLE', false);
        if ($this->oidcEnable) {
            $this->oidcProviderUrl = $this->getString('LEAN_OIDC_PROVIDER_URL', '');
            $this->oidcClientId = $this->getString('LEAN_OIDC_CLIEND_ID', '');
            $this->oidcClientSecret = $this->getString('LEAN_OIDC_CLIEND_SECRET', '');

            //These are optional and will override the well-known configuration
            $this->oidcAuthUrl = $this->getString('LEAN_OIDC_AUTH_URL_OVERRIDE', '');
            $this->oidcTokenUrl = $this->getString('LEAN_OIDC_TOKEN_URL_OVERRIDE', '');
            $this->oidcJwksUrl = $this->getString('LEAN_OIDC_JWKS_URL_OVERRIDE', '');
            $this->oidcUserInfoUrl = $this->getString('LEAN_OIDC_USERINFO_URL_OVERRIDE', '');
            $this->oidcCertificateString = $this->getString('LEAN_OIDC_CERTIFICATE_STRING', '');
            $this->oidcCertificateFile = $this->getString('LEAN_OIDC_CERTIFICATE_FILE', '');
            $this->oidcScopes = $this->getString('LEAN_OIDC_SCOPES', 'openid profile email');
            $this->oidcFieldEmail = $this->getString('LEAN_OIDC_FIELD_EMAIL', 'email');
            $this->oidcFieldFirstName = $this->getString('LEAN_OIDC_FIELD_FIRSTNAME', 'given_name');
            $this->oidcFieldLastName = $this->getString('LEAN_OIDC_FIELD_LASTNAME', 'family_name');
        }

        /* Redis */
        $this->useRedis = $this->getBool('LEAN_USE_REDIS', false);
        if ($this->useRedis) {
            $this->redisURL = $this->getString('LEAN_REDIS_URL', '');
        }

        /* Plugins */
        $this->plugins = $this->environmentHelper("LEAN_PLUGINS", $defaultConfiguration->plugins ?? '', "string");
    }

    /**
     * getBool - get a boolean value from the environment
     *
     * @param string  $envVar
     * @param boolean $default
     * @return boolean
     */
    private function getBool(string $envVar, bool $default): bool
    {
        return $this->environmentHelper($envVar, $default, 'boolean');
    }

    /**
     * getString - get a string value from the environment
     *
     * @param string $envVar
     * @param string $default
     * @return string
     */
    private function getString(string $envVar, string $default = ''): string
    {
        return $this->environmentHelper($envVar, $default, 'string');
    }

    /**
     * environmentHelper - helper function to get a value from the environment
     *
     * @param string $envVar
     * @param mixed  $default
     * @param string $dataType
     * @return mixed
     */
    private function environmentHelper(string $envVar, $default, $dataType = "string")
    {
        if (isset($_SESSION['mainconfig'][$envVar])) {
            return $_SESSION['mainconfig'][$envVar];
        }

        /**
         * Basically, here, we are doing the fetch order of
         * environment -> .env file -> yaml file -> user default -> leantime default
         * This allows us to use any one or a combination of those methods to configure leantime.
         */
        $found = null;
        $found = $this->tryGetFromPhp($envVar, $found);
        $found = $this->tryGetFromYaml($envVar, $found);
        $found = $this->tryGetFromEnvironment($envVar, $found);

        if (!$found || $found == "") {
            $_SESSION['mainconfig'][$envVar] = $default;
            return $default;
        }

        // we need to check to see if we need to convert the found data
        $_SESSION['mainconfig'][$envVar] = match ($dataType) {
            "string" => $found,
            "boolean" => $found == "true" ? true : false,
            "number" => intval($found),
            default => $found,
        };

        return $_SESSION['mainconfig'][$envVar];
    }

    private function tryGetFromPhp(string $envVar, mixed $currentValue): mixed
    {
        if ($currentValue != null && $currentValue != "") {
            return $currentValue;
        }

        if ($this->phpConfig) {
            $key = strtolower(preg_replace('/^LEAN_/', '', $envVar));
            return isset($this->phpConfig->$key) ? $this->phpConfig->$key : null;
        }

        return null;
    }

    /**
     * tryGetFromEnvironment - try to get a value from the environment
     *
     * @param string $envVar
     * @param mixed  $currentValue
     * @return mixed
     */
    private function tryGetFromEnvironment(string $envVar, mixed $currentValue): mixed
    {
        if ($currentValue != null && $currentValue != "") {
            return $currentValue;
        }

        return $_ENV[$envVar] ?? null;
    }

    /**
     * tryGetFromYaml - try to get a value from the yaml file
     *
     * @param string $envVar
     * @param mixed  $currentValue
     * @return mixed
     */
    private function tryGetFromYaml(string $envVar, mixed $currentValue): mixed
    {
        if ($currentValue != null && $currentValue != "") {
            return $currentValue;
        }

        if ($this->yaml) {
            $key = strtolower(preg_replace('/^LEAN_/', '', $envVar));
            return isset($this->yaml[$key]) ? $this->yaml[$key] : null;
        }

        return null;
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($_SESSION['mainconfig'], $key);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        return Arr::get($_SESSION['mainconfig'], $key, $default);
    }

    /**
     * Get many configuration values.
     *
     * @param  array  $keys
     * @return array
     */
    public function getMany($keys)
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = Arr::get($_SESSION['mainconfig'], $key, $default);
        }

        return $config;
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($_SESSION['mainconfig'], $key, $value);

            if (property_exists($this, $key)) {
                $this->$key = $_SESSION['mainconfig'][$key];
            }
        }
    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key, []);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->set($key, $array);
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->set($key, null);
    }
}
