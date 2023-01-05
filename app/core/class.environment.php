<?php

namespace leantime\core;

class environment {

    private static $instance = null;

    public static function getInstance() {

        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
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
            $this->smtpPassword = $this->environmentHelper("LEAN_EMAIL_SMTP_PASSWORD", $defaultConfiguration->smtpPassword ?? '' );
            $this->smtpAutoTLS = $this->environmentHelper("LEAN_EMAIL_SMTP_AUTO_TLS", $defaultConfiguration->smtpAutoTLS ?? false, "boolean");
            $this->smtpSecure = $this->environmentHelper("LEAN_EMAIL_SMTP_SECURE", $defaultConfiguration->smtpSecure ?? '');
            $this->smtpPort = $this->environmentHelper("LEAN_EMAIL_SMTP_PORT", $defaultConfiguration->smtpPort ?? '');
            $this->smtpSSLNoverify = $this->environmentHelper("LEAN_EMAIL_SMTP_SSLNOVERIFY", $defaultConfiguration->smtpSSLNoverify ?? false, "boolean");
        }

        /* ldap */
        $this->useLdap = $this->environmentHelper("LEAN_LDAP_USE_LDAP", $defaultConfiguration->useLdap, "boolean");
        if ($this->useLdap) {
            $this->ldapType = $this->environmentHelper("LEAN_LDAP_LDAP_TYPE", $defaultConfiguration->ldapType);
            $this->ldapHost = $this->environmentHelper("LEAN_LDAP_HOST", $defaultConfiguration->ldapHost);
            $this->ldapPort = $this->environmentHelper("LEAN_LDAP_PORT", $defaultConfiguration->ldapPort);
            $this->baseDn = $this->environmentHelper("LEAN_LDAP_BASE_DN", $defaultConfiguration->baseDn);
            $this->ldapDn = $this->environmentHelper("LEAN_LDAP_DN", $defaultConfiguration->ldapDn);
            $this->ldapUserDomain = $this->environmentHelper("LEAN_LDAP_USER_DOMAIN", $defaultConfiguration->ldapUserDomain);
            $this->ldapKeys = $this->environmentHelper("LEAN_LDAP_KEYS", $defaultConfiguration->ldapKeys);
            $this->ldapLtGroupAssignments = $this->environmentHelper("LEAN_LDAP_GROUP_ASSIGNMENT", $defaultConfiguration->ldapLtGroupAssignments);
            $this->ldapDefaultRoleKey = $this->environmentHelper("LEAN_LDAP_DEFAULT_ROLE_KEY", $defaultConfiguration->ldapDefaultRoleKey);
        }
    }

    private function environmentHelper($envVar, $default, $dataType = "string") {

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

    private function tryGetFromEnvironment($envVar, $currentValue) {
        if ($currentValue != null && $currentValue != "") {
            return $currentValue;
        }
        return isset($_ENV[$envVar]) ? $_ENV[$envVar] : null;
    }

    private function tryGetFromYaml($envVar, $currentValue) {
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
