<?php

namespace leantime\core;

class environment
{
    private static $instance = null;

    public static function getInstance(): static
    {

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public \Dotenv\Dotenv $dotenv;

    public ?object $yaml;

    public string $sitename;
    public string $language;
    public string $logoPath;
    public string $printLogoURL;
    public string $appUrl;
    public string $defaultTheme;
    public string $primarycolor;
    public string $secondarycolor;
    public int $debug;
    public string $defaultTimezone;
    public bool $enableMenuType;
    public bool $keepTheme;
    public string $logPath;

    public string $appUrlRoot;


    public string $dbHost;
    public string $dbUser;
    public string $dbPassword;
    public string $dbDatabase;
    public int $dbPort;

    public string $userFilePath;
    public bool $useS3;
    public string $s3EndPoint;
    public string $s3Key;
    public string $s3Secret;
    public string $s3Bucket;
    public string $s3UsePathStyleEndpoint;
    public string $s3Region;
    public string $s3FolderName;

    public string $sessionpassword;
    public int $sessionExpiration;

    public string $email;
    public bool $useSMTP;
    public string $smtpHosts;
    public string $smtpAuth;
    public string $smtpUsername;
    public string $smtpPassword;
    public bool $smtpAutoTLS;
    public string $smtpSecure;
    public int $smtpPort;
    public bool $smtpSSLNoverify;

    public bool $useLdap;
    public string $ldapType;
    public string $ldapHost;
    public int $ldapPort;
    public string $ldapDn;
    public string $ldapKeys;
    public string $ldapLtGroupAssignments;
    public string $ldapDefaultRoleKey;

    public bool $oidcEnable;
    public string $oidcProviderUrl;
    public string $oidcClientId;
    public string $oidcClientSecret;
    public string $oidcAuthUrl;
    public string $oidcTokenUrl;
    public string $oidcJwksUrl;
    public string $oidcUserInfoUrl;

    private function __construct()
    {

        $defaultConfiguration = new \leantime\core\config();

        $this->dotenv = \Dotenv\Dotenv::createImmutable(ROOT . "/../config");
        $this->dotenv->safeLoad();

        $this->yaml = null;
        if (file_exists(ROOT . "/../config/config.yaml")) {
            $this->yaml = \Symfony\Component\Yaml\Yaml::parseFile(ROOT . "/../config/config.yaml");
        }

        /* General */
        $this->sitename = $this->environmentHelper("LEAN_SITENAME", $defaultConfiguration->sitename ?? 'Leantime');
        $this->language = $this->environmentHelper("LEAN_LANGUAGE", $defaultConfiguration->language ?? 'en-US');
        $this->logoPath = $this->environmentHelper("LEAN_LOGO_PATH", $defaultConfiguration->logoPath ?? '/images/logo.svg');
        $this->printLogoURL = $this->environmentHelper("LEAN_PRINT_LOGO_URL", $defaultConfiguration->printLogoURL ?? '');
        $this->appUrl = $this->environmentHelper("LEAN_APP_URL", $defaultConfiguration->appUrl ?? '');
        $this->defaultTheme = $this->environmentHelper("LEAN_DEFAULT_THEME", $defaultConfiguration->defaultTheme ?? 'default');
        $this->primarycolor = $this->environmentHelper("LEAN_PRIMARY_COLOR", $defaultConfiguration->primarycolor ?? '#1b75bb');
        $this->secondarycolor = $this->environmentHelper("LEAN_SECONDARY_COLOR", $defaultConfiguration->secondarycolor ?? '#81B1A8');
        $this->debug = $this->environmentHelper("LEAN_DEBUG", $defaultConfiguration->debug ?? 0);
        $this->defaultTimezone = $this->environmentHelper("LEAN_DEFAULT_TIMEZONE", $defaultConfiguration->defaultTimezone ?? 'America/Los_Angeles');
        $this->enableMenuType = $this->environmentHelper("LEAN_ENABLE_MENU_TYPE", $defaultConfiguration->enableMenuType ?? false);
        $this->keepTheme = $this->environmentHelper("LEAN_KEEP_THEME", $defaultConfiguration->keepTheme ?? true);
        $this->logPath = $this->environmentHelper("LEAN_LOG_PATH", APP_ROOT.'/logs/error.log');


        //TODO this variables needs to be removed and generated programmatically.
        $this->appUrlRoot = $this->environmentHelper("LEAN_APP_URL_ROOT", $defaultConfiguration->appUrlRoot ?? '');

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
            $this->smtpAuth = $this->environmentHelper("LEAN_EMAIL_SMTP_AUTH", $defaultConfiguration->smtpAuth ?? '', "boolean");
            $this->smtpUsername = $this->environmentHelper("LEAN_EMAIL_SMTP_USERNAME", $defaultConfiguration->smtpUsername ?? '');
            $this->smtpPassword = $this->environmentHelper("LEAN_EMAIL_SMTP_PASSWORD", $defaultConfiguration->smtpPassword ?? '');
            $this->smtpAutoTLS = $this->environmentHelper("LEAN_EMAIL_SMTP_AUTO_TLS", $defaultConfiguration->smtpAutoTLS ?? false, "boolean");
            $this->smtpSecure = $this->environmentHelper("LEAN_EMAIL_SMTP_SECURE", $defaultConfiguration->smtpSecure ?? '');
            $this->smtpPort = $this->environmentHelper("LEAN_EMAIL_SMTP_PORT", $defaultConfiguration->smtpPort ?? '');
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
        }

        /* OIDC */
        $this->oidcEnable = $this->getBool('LEAN_OIDC_ENABLE', false);
        if($this->oidcEnable) {
            $this->oidcProviderUrl = $this->getString('LEAN_OIDC_PROVIDER_URL', '');
            $this->oidcClientId = $this->getString('LEAN_OIDC_CLIEND_ID', '');
            $this->oidcClientSecret = $this->getString('LEAN_OIDC_CLIEND_SECRET', '');

            //These are optional and will override the well-known configuration
            $this->oidcAuthUrl = $this->getString('LEAN_OIDC_AUTH_URL_OVERRIDE', '');
            $this->oidcTokenUrl = $this->getString('LEAN_OIDC_TOKEN_URL_OVERRIDE', '');
            $this->oidcJwksUrl = $this->getString('LEAN_OIDC_JWKS_URL_OVERRIDE', '');
            $this->oidcUserInfoUrl = $this->getString('LEAN_OIDC_USERINFO_URL_OVERRIDE', '');
        }
    }

    private function getBool(string $envVar, bool $default): bool
    {
        return $this->environmentHelper($envVar, $default, 'boolean');
    }

    private function getString(string $envVar, string $default): string
    {
        return $this->environmentHelper($envVar, $default, 'string');
    }

    private function environmentHelper(string $envVar, $default, $dataType = "string")
    {

        if (isset($_SESSION['mainconfig'][$envVar])) {
            return $_SESSION['mainconfig'][$envVar];
        } else {
            /*
             * Basically, here, we are doing the fetch order of
             * environment -> .env file -> yaml file -> user default -> leantime default
             * This allows us to use any one or a combination of those methods to configure leantime.
             */
            $found = null;
            $found = $this->tryGetFromYaml($envVar, $found);
            $found = $this->tryGetFromEnvironment($envVar, $found);

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

    private function tryGetFromEnvironment($envVar, $currentValue)
    {
        if ($currentValue != null && $currentValue != "") {
            return $currentValue;
        }
        return $_ENV[$envVar] ?? null;
    }

    private function tryGetFromYaml($envVar, $currentValue)
    {
        if ($currentValue != null && $currentValue != "") {
            return $currentValue;
        }
        if ($this->yaml) {
            $key = strtolower(preg_replace('/^LEAN_/', '', $envVar));
            return isset($this->yaml[$key]) ? $this->yaml[$key] : null;
        } else {
            return null;
        }
    }
}
