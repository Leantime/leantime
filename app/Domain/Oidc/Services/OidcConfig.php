<?php

namespace Leantime\Domain\Oidc\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Domain\Oidc\Events\OidcConfigFilter;

/**
 * The OIDC settings Leantime signs in with.
 *
 * Starts from the environment (LEAN_OIDC_*) and runs the result through OidcConfigFilter, so a
 * plugin can supply settings an admin entered in the UI. Everything that reads OIDC settings
 * (the OIDC service, the login page, the public status endpoint) goes through here, so they
 * always agree on whether OIDC is on.
 */
class OidcConfig
{
    /**
     * Every OIDC setting, with the value used when the environment has none.
     *
     * @var array<string, mixed>
     */
    public const DEFAULTS = [
        'oidcEnable' => false,
        'oidcProviderUrl' => '',
        'oidcClientId' => '',
        'oidcClientSecret' => '',
        'oidcAutoDiscoverUrl' => '',
        'oidcAuthUrl' => '',
        'oidcTokenUrl' => '',
        'oidcJwksUrl' => '',
        'oidcUserInfoUrl' => '',
        'oidcCertificateString' => '',
        'oidcCertificateFile' => '',
        'oidcScopes' => 'openid profile email',
        'oidcCreateUser' => false,
        'oidcDefaultRole' => 20,
        'oidcFieldEmail' => 'email',
        'oidcFieldFirstName' => 'given_name',
        'oidcFieldLastName' => 'family_name',
        'oidcFieldPhone' => '',
        'oidcFieldJobtitle' => '',
        'oidcFieldJoblevel' => '',
        'oidcFieldDepartment' => '',
        'oidcRequireVerifiedEmail' => true,
        'oidcSkipTlsVerify' => false,
    ];

    /**
     * Settings only the server operator may change: turning off TLS verification, or reading a
     * certificate from a path on the server. Filter listeners can't override these.
     *
     * @var array<int, string>
     */
    public const ENV_ONLY_KEYS = ['oidcSkipTlsVerify', 'oidcCertificateFile'];

    /** @var array<string, mixed>|null Resolved settings, built once per instance. */
    private ?array $resolved = null;

    public function __construct(
        private Environment $config,
    ) {}

    /**
     * All OIDC settings: environment first, then the filter, with env-only keys restored.
     *
     * @return array<string, mixed> Setting name => value; always contains every key of DEFAULTS.
     */
    public function all(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $fromEnvironment = [];
        foreach (self::DEFAULTS as $key => $default) {
            $fromEnvironment[$key] = $this->config->get($key, $default);
        }

        $filtered = OidcConfigFilter::dispatch(config: $fromEnvironment);
        if (! is_array($filtered)) {
            $filtered = $fromEnvironment;
        }

        // Only known settings, and the env-only ones exactly as the environment has them.
        $resolved = array_merge($fromEnvironment, array_intersect_key($filtered, self::DEFAULTS));
        foreach (self::ENV_ONLY_KEYS as $key) {
            $resolved[$key] = $fromEnvironment[$key];
        }

        return $this->resolved = $resolved;
    }

    /**
     * One OIDC setting.
     *
     * @param  string  $key  Setting name, e.g. "oidcClientId".
     * @param  mixed  $default  Returned when the setting is null.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Whether OIDC sign-in is switched on.
     */
    public function isEnabled(): bool
    {
        return filter_var($this->get('oidcEnable', false), FILTER_VALIDATE_BOOLEAN);
    }
}
