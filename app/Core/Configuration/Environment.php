<?php

namespace Leantime\Core\Configuration;

use ArrayAccess;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Support\Str;
use Leantime\Config\Config;
use Symfony\Component\Yaml\Yaml;

/**
 * environment - class To handle environment variables
 */
class Environment extends Repository implements ArrayAccess, ConfigContract
{
    // Config Files ===============================================================================

    private ?object $yaml;

    private ?Config $phpConfig;

    /**
     * @var array list of legacy mappings
     *
     * @todo warn about key changes after deprecating config/configuration.php
     * @todo remove this after removing support for config/configuration.php
     */
    private const LEGACY_MAPPINGS = [
        'printLogoUrl' => 'LEAN_PRINT_LOGO_URL',
        'primarycolor' => 'LEAN_PRIMARY_COLOR',
        'secondarycolor' => 'LEAN_SECONDARY_COLOR',
        'email' => 'LEAN_EMAIL_RETURN',
        'useSMTP' => 'LEAN_EMAIL_USE_SMTP',
        'smtpHosts' => 'LEAN_EMAIL_SMTP_HOSTS',
        'smtpAuth' => 'LEAN_EMAIL_SMTP_AUTH',
        'smtpUsername' => 'LEAN_EMAIL_SMTP_USERNAME',
        'smtpPassword' => 'LEAN_EMAIL_SMTP_PASSWORD',
        'smtpAutoTLS' => 'LEAN_EMAIL_SMTP_AUTO_TLS',
        'smtpSecure' => 'LEAN_EMAIL_SMTP_SECURE',
        'smtpPort' => 'LEAN_EMAIL_SMTP_PORT',
        'smtpSSLNoverify' => 'LEAN_EMAIL_SMTP_SSLNOVERIFY',
        'useLdap' => 'LEAN_LDAP_USE_LDAP',
        'ldapType' => 'LEAN_LDAP_LDAP_TYPE',
        'ldapLtGroupAssignments' => 'LEAN_LDAP_GROUP_ASSIGNMENT',
        'ldapDomain' => 'LEAN_LDAP_LDAP_DOMAIN',
        'oidcClientId' => 'LEAN_OIDC_CLIENT_ID',
        'oidcClientSecret' => 'LEAN_OIDC_CLIENT_SECRET',
        'oidcAutoDiscoverUrl' => 'LEAN_OIDC_AUTO_DISCOVER',
        'oidcAuthUrl' => 'LEAN_OIDC_AUTH_URL_OVERRIDE',
        'oidcTokenUrl' => 'LEAN_OIDC_TOKEN_URL_OVERRIDE',
        'oidcJwksUrl' => 'LEAN_OIDC_JWKS_URL_OVERRIDE',
        'oidcUserInfoUrl' => 'LEAN_OIDC_USERINFO_URL_OVERRIDE',
        'oidcFieldFirstName' => 'LEAN_OIDC_FIELD_FIRSTNAME',
        'oidcFieldLastName' => 'LEAN_OIDC_FIELD_LASTNAME',
        'redisURL' => 'LEAN_REDIS_URL',
    ];

    /**
     * environment constructor.
     *
     * @throws Exception
     */
    public function __construct(array $items = [])
    {
        if (! empty($items) && is_array($items)) {
            $this->items = $items;
        }

        $defaultConfiguration = new DefaultConfig;

        /* PHP */
        $this->phpConfig = null;
        if (file_exists($phpConfigFile = APP_ROOT.'/config/configuration.php')) {

            require_once $phpConfigFile;

            if (! class_exists(Config::class)) {
                throw new Exception('We found a php configuration file but the class cannot be instantiated. Please check the configuration file for namespace and class name. You can use the configuration.sample.php as a template. See https://github.com/leantime/leantime/releases/tag/v2.4-beta-2 for more details.');
            }

            $this->phpConfig = new Config;

            $configVars = get_class_vars(Config::class);
            foreach (array_keys($configVars) as $propertyName) {
                $envVarName = self::LEGACY_MAPPINGS[$propertyName] ?? 'LEAN_'.Str::of($propertyName)->snake()->upper()->toString();
                putenv($envVarName.'='.$configVars[$propertyName]);
            }

        }

        $defaultConfigurationProperties = get_class_vars($defaultConfiguration::class);

        foreach (array_keys($defaultConfigurationProperties) as $propertyName) {

            $type = gettype($defaultConfigurationProperties[$propertyName]);
            $type = $type == 'NULL' ? 'string' : $type;

            $this->set($propertyName, $this->environmentHelper(
                envVar: self::LEGACY_MAPPINGS[$propertyName] ?? 'LEAN_'.Str::of($propertyName)->snake()->upper()->toString(),
                default: $defaultConfigurationProperties[$propertyName],
                dataType: $type,
            ));
        }

    }

    /**
     * getBool - get a boolean value from the environment
     */
    private function getBool(string $envVar, bool $default): bool
    {
        return $this->environmentHelper($envVar, $default, 'boolean');
    }

    /**
     * getString - get a string value from the environment
     */
    private function getString(string $envVar, string $default = ''): string
    {
        return $this->environmentHelper($envVar, $default, 'string');
    }

    /**
     * environmentHelper - helper function to get a value from the environment
     */
    private function environmentHelper(string $envVar, mixed $default, string $dataType = 'string'): mixed
    {
        /**
         * Basically, here, we are doing the fetch order of
         * environment -> .env file -> yaml file -> user default -> leantime default
         * This allows us to use any one or a combination of those methods to configure leantime.
         */
        $found = $default;
        $found = $this->tryGetFromPhp($envVar, $found) ?? $found;
        $found = $this->tryGetFromEnvironment($envVar, $found) ?? $found;

        // we need to check to see if we need to convert the found data
        return match ($dataType) {
            'string' => $found,
            'boolean' => filter_var($found, FILTER_VALIDATE_BOOLEAN),
            'number' => (int) ($found),
            default => $found,
        };
    }

    private function tryGetFromPhp(string $envVar, mixed $currentValue): mixed
    {

        if ($this->phpConfig) {
            $key = array_search($envVar, self::LEGACY_MAPPINGS) ?: Str::of($envVar)->replace('LEAN_', '')->lower()->camel()->toString();

            return $this->phpConfig->$key ?? $currentValue;
        }

        return null;
    }

    /**
     * tryGetFromEnvironment - try to get a value from the environment
     */
    private function tryGetFromEnvironment(string $envVar, mixed $currentValue): mixed
    {
        return $_ENV[$envVar] ?? env($envVar) ?? $currentValue;
    }

    /**
     * Dynamically access the configuration using object syntax.
     */
    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the configuration using object syntax.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Dynamically check if a configuration option is set using object syntax.
     */
    public function __isset(string $key): bool
    {
        return $this->has($key) && $this->get($key) !== null;
    }

    /**
     * Dynamically unset a configuration option using object syntax.
     */
    public function __unset(string $key): void
    {
        $this->set($key, null);
    }
}
