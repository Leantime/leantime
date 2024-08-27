<?php

namespace Leantime\Core\Configuration;

use ArrayAccess;
use Dotenv\Dotenv;
use Exception;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Leantime\Config\Config;
use Symfony\Component\Yaml\Yaml;

/**
 * environment - class To handle environment variables
 *
 * @package    leantime
 * @subpackage core
 */
class Environment implements ArrayAccess, ConfigContract
{
    # Config Files ===============================================================================
    /**
     * @var Dotenv
     */
    public Dotenv $dotenv;

    /**
     * @var ?object
     */
    public ?object $yaml;

    /**
     * @var Config|null
     */
    public ?Config $phpConfig;

    # Config ======================================================================================
    /**
     * @var array The Config
     */
    public array $config = [];

    /**
     * @var array list of legacy mappings
     * @todo remove this after deprecating configuration.php
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
     * @param DefaultConfig $defaultConfiguration
     * @throws Exception
     */
    public function __construct(DefaultConfig $defaultConfiguration)
    {


        /* PHP */
        $this->phpConfig = null;
        if (file_exists($phpConfigFile = APP_ROOT . "/config/configuration.php")) {
            require_once $phpConfigFile;

            if (! class_exists(Config::class)) {
                throw new Exception("We found a php configuration file but the class cannot be instantiated. Please check the configuration file for namespace and class name. You can use the configuration.sample.php as a template. See https://github.com/leantime/leantime/releases/tag/v2.4-beta-2 for more details.");
            }

            $this->phpConfig = new Config();
        }

        /* Dotenv */
        $this->dotenv = Dotenv::createImmutable(APP_ROOT . "/config");
        $this->dotenv->safeLoad();

        /* YAML */
        $this->yaml = null;
        if (file_exists(APP_ROOT . "/config/config.yaml")) {
            $this->yaml = Yaml::parseFile(APP_ROOT . "/config/config.yaml");
        }

        $defaultConfigurationProperties = get_class_vars($defaultConfiguration::class);

        foreach (array_keys($defaultConfigurationProperties) as $propertyName) {
            $type = gettype($defaultConfigurationProperties[$propertyName]);
            $type = $type == 'NULL' ? 'string' : $type;

            $this->config[$propertyName] = $this->environmentHelper(
                envVar: self::LEGACY_MAPPINGS[$propertyName] ?? 'LEAN_' . Str::of($propertyName)->snake()->upper()->toString(),
                default: $defaultConfigurationProperties[$propertyName],
                dataType: $type,
            );
        }

        $end = microtime(true);

    }

    public function updateCache() {
        file_put_contents(APP_ROOT . "/cache/configCache", serialize($this->config));
    }
    /**
     * getBool - get a boolean value from the environment
     *
     * @param string $envVar
     * @param bool   $default
     * @return bool
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
    private function environmentHelper(string $envVar, mixed $default, string $dataType = "string"): mixed
    {
        /**
         * Basically, here, we are doing the fetch order of
         * environment -> .env file -> yaml file -> user default -> leantime default
         * This allows us to use any one or a combination of those methods to configure leantime.
         */
        $found = $default;
        $found = $this->tryGetFromPhp($envVar, $found) ?? $found;
        $found = $this->tryGetFromYaml($envVar, $found) ?? $found;
        $found = $this->tryGetFromEnvironment($envVar, $found) ?? $found;

        // we need to check to see if we need to convert the found data
        return match ($dataType) {
            "string" => $found,
            "boolean" => $found == "true",
            "number" => intval($found),
            default => $found,
        };
    }

    /**
     * @param string $envVar
     * @param mixed  $currentValue
     * @return mixed
     */
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
     *
     * @param string $envVar
     * @param mixed  $currentValue
     * @return mixed
     */
    private function tryGetFromEnvironment(string $envVar, mixed $currentValue): mixed
    {

        return $_ENV[$envVar] ?? $currentValue;
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

        if ($this->yaml) {
            $key = strtolower(preg_replace('/^LEAN_/', '', $envVar));
            return $this->yaml[$key] ?? $currentValue;
        }

        return null;
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string $key
     * @return bool
     */
    public function has($key): bool
    {
        return Arr::has($this->config, $key);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  array|string $key
     * @param  mixed        $default
     * @return mixed
     */
    public function get($key, $default = null): mixed
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        return Arr::get(
            $this->config,
            $key,
            $default
        );
    }

    /**
     * Get many configuration values.
     *
     * @param array $keys
     * @return array
     */
    public function getMany(array $keys): array
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = $this->get($key, $default);
        }

        return $config;
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string $key
     * @param  mixed        $value
     * @return void
     */
    public function set($key, $value = null): void
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->config, $key, $value);
        }

    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function prepend($key, $value): void
    {
        $array = $this->get($key, []);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function push($key, $value): void
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
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->set($key, null);
    }

    /**
     * Dynamically access the configuration using object syntax.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the configuration using object syntax.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Dynamically check if a configuration option is set using object syntax.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->has($key) && $this->get($key) !== null;
    }

    /**
     * Dynamically unset a configuration option using object syntax.
     *
     * @param string $key
     *
     * @return void
     */
    public function __unset(string $key): void
    {
        $this->set($key, null);
    }
}
