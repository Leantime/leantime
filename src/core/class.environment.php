<?php

namespace leantime\core;

class environment {

    public function __construct()
    {
        $defaultConfiguration = new config();
        /* General */
        $this->sitename = $this->environmentHelper("LEAN_SITENAME", $defaultConfiguration->sitename);
        $this->language = $this->environmentHelper("LEAN_LANGUAGE", $defaultConfiguration->language);
        $this->logoPath = $this->environmentHelper("LEAN_LOGO_PATH", $defaultConfiguration->logoPath);
        $this->printLogoURL = $this->environmentHelper("LEAN_PRINT_LOGO_URL", $defaultConfiguration->printLogoURL);
        $this->appUrl = $this->environmentHelper("LEAN_APP_URL", $defaultConfiguration->appUrl);
        $this->defaultTheme = $this->environmentHelper("LEAN_DEFAULT_THEME", $defaultConfiguration->defaultTheme);
        $this->primarycolor = $this->environmentHelper("LEAN_PRIMARY_COLOR", $defaultConfiguration->primarycolor);
        $this->secondarycolor = $this->environmentHelper("LEAN_SECONDARY_COLOR", $defaultConfiguration->secondarycolor);
        $this->debug = $this->environmentHelper("LEAN_DEBUG", $defaultConfiguration->debug);
        $this->defaultTimezone = $this->environmentHelper("LEAN_DEFAULT_TIMEZONE", $defaultConfiguration->defaultTimezone);
        $this->enableMenuType = $this->environmentHelper("LEAN_ENABLE_MENU_TYPE", $defaultConfiguration->enableMenuType);
        $this->keepTheme = $this->environmentHelper("LEAN_KEEP_THEME", $defaultConfiguration->keepTheme);

        /* Database */
        $this->dbHost = $this->environmentHelper("LEAN_DB_HOST", $defaultConfiguration->dbHost);
        $this->dbUser = $this->environmentHelper("LEAN_DB_USER", $defaultConfiguration->dbUser);
        $this->dbPassword = $this->environmentHelper("LEAN_DB_PASSWORD", $defaultConfiguration->dbPassword);
        $this->dbDatabase = $this->environmentHelper("LEAN_DB_DATABASE", $defaultConfiguration->dbDatabase);
        $this->dbPort = $this->environmentHelper("LEAN_DB_PORT", $defaultConfiguration->dbPort);

        /* Fileupload */
        $this->userFilePath = $this->environmentHelper("LEAN_USER_FILE_PATH", $defaultConfiguration->userFilePath);
        $this->useS3 = $this->environmentHelper("LEAN_USE_S3", $defaultConfiguration->useS3, "boolean");
        if ($this->useS3) {
            $this->s3EndPoint = $this->environmentHelper("LEAN_S3_END_POINT", $defaultConfiguration->s3EndPoint);
            $this->s3Key = $this->environmentHelper("LEAN_S3_KEY", $defaultConfiguration->s3Key);
            $this->s3Secret = $this->environmentHelper("LEAN_S3_SECRET", $defaultConfiguration->s3Secret);
            $this->s3Bucket = $this->environmentHelper("LEAN_S3_BUCKET", $defaultConfiguration->s3Bucket);
            $this->s3UsePathStyleEndpoint = $this->environmentHelper("LEAN_S3_USE_PATH_STYLE_ENDPOINT", $defaultConfiguration->s3UsePathStyleEndpoint, "boolean");
            $this->s3Region = $this->environmentHelper("LEAN_S3_REGION", $defaultConfiguration->s3Region);
            $this->s3FolderName = $this->environmentHelper("LEAN_S3_FOLDER_NAME", $defaultConfiguration->s3FolderName);
        }

        /* Sessions */
        $this->sessionpassword = $this->environmentHelper("LEAN_SESSION_PASSWORD", $defaultConfiguration->sessionpassword);
        $this->sessionExpiration = $this->environmentHelper("LEAN_SESSION_EXPIRATION", $defaultConfiguration->sessionExpiration, "number");

        /* Email */
        $this->email = $this->environmentHelper("LEAN_EMAIL_RETURN", $defaultConfiguration->email);
        $this->useSMTP = $this->environmentHelper("LEAN_EMAIL_USE_SMTP", $defaultConfiguration->useSMTP, "boolean");
        if ($this->useSMTP) {
            $this->smtpHosts = $this->environmentHelper("LEAN_EMAIL_SMTP_HOSTS", $defaultConfiguration->smtpHosts);
            $this->smtpAuth = $this->environmentHelper("LEAN_EMAIL_SMTP_AUTH", $defaultConfiguration->smtpAuth, "boolean");
            $this->smtpUsername = $this->environmentHelper("LEAN_EMAIL_SMTP_USERNAME", $defaultConfiguration->smtpUsername);
            $this->smtpPassword = $this->environmentHelper("LEAN_EMAIL_SMTP_PASSWORD", $defaultConfiguration->smtpPassword);
            $this->smtpAutoTLS = $this->environmentHelper("LEAN_EMAIL_SMTP_AUTO_TLS", $defaultConfiguration->smtpAutoTLS, "boolean");
            $this->smtpSecure = $this->environmentHelper("LEAN_EMAIL_SMTP_SECURE", $defaultConfiguration->smtpSecure);
            $this->smtpPort = $this->environmentHelper("LEAN_EMAIL_SMTP_PORT", $defaultConfiguration->smtpPort);
            $this->smtpSSLNoverify = $this->environmentHelper("LEAN_EMAIL_SMTP_SSLNOVERIFY", $defaultConfiguration->smtpSSLNoverify, "boolean");
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

    private function environmentHelper($envVar, $default, $dataType = "string")
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
