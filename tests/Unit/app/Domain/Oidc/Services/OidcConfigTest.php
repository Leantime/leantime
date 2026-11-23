<?php

namespace Tests\Unit\app\Domain\Oidc\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Oidc\Events\OidcConfigFilter;
use Leantime\Domain\Oidc\Services\OidcConfig;

/**
 * OIDC settings come from the environment and can be changed by OidcConfigFilter listeners
 * (AdvancedAuth applies the settings an admin entered in the UI). Turning off TLS verification
 * or reading a certificate file from disk stays with the server operator.
 */
class OidcConfigTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const DISPATCHER_PROPS = [
        'eventRegistry',
        'filterRegistry',
        'available_hooks',
        'patternMatchCache',
        'compiledPatternCache',
        'eventRegistryVersion',
        'filterRegistryVersion',
    ];

    private array $dispatcherSnapshot = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Listeners added here must not leak into other tests.
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (self::DISPATCHER_PROPS as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }

        parent::tearDown();
    }

    private function oidcConfig(array $environment): OidcConfig
    {
        $config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $environment[$key] ?? $default,
        ]);

        return new OidcConfig($config);
    }

    public function test_without_listeners_the_environment_is_used(): void
    {
        $oidcConfig = $this->oidcConfig(['oidcEnable' => true, 'oidcClientId' => 'env-client']);

        $this->assertTrue($oidcConfig->isEnabled());
        $this->assertSame('env-client', $oidcConfig->get('oidcClientId'));
        $this->assertSame('openid profile email', $oidcConfig->get('oidcScopes'));
    }

    public function test_a_listener_can_supply_the_settings(): void
    {
        EventDispatcher::add_filter_listener(OidcConfigFilter::class, function (array $config) {
            $config['oidcEnable'] = true;
            $config['oidcClientId'] = 'from-ui';

            return $config;
        });

        $oidcConfig = $this->oidcConfig(['oidcClientId' => 'env-client']);

        $this->assertTrue($oidcConfig->isEnabled());
        $this->assertSame('from-ui', $oidcConfig->get('oidcClientId'));
    }

    public function test_tls_skip_and_certificate_file_stay_with_the_environment(): void
    {
        EventDispatcher::add_filter_listener(OidcConfigFilter::class, function (array $config) {
            $config['oidcSkipTlsVerify'] = true;
            $config['oidcCertificateFile'] = '/etc/passwd';

            return $config;
        });

        $oidcConfig = $this->oidcConfig([]);

        $this->assertFalse($oidcConfig->get('oidcSkipTlsVerify'));
        $this->assertSame('', $oidcConfig->get('oidcCertificateFile'));
    }

    public function test_unknown_keys_from_a_listener_are_dropped(): void
    {
        EventDispatcher::add_filter_listener(OidcConfigFilter::class, fn (array $config) => $config + ['dbPassword' => 'x']);

        $this->assertArrayNotHasKey('dbPassword', $this->oidcConfig([])->all());
    }

    public function test_a_listener_that_returns_nothing_leaves_the_environment_in_place(): void
    {
        EventDispatcher::add_filter_listener(OidcConfigFilter::class, fn () => null);

        $this->assertSame('env-client', $this->oidcConfig(['oidcClientId' => 'env-client'])->get('oidcClientId'));
    }

    public function test_a_certificate_from_a_listener_must_be_inline_pem(): void
    {
        // openssl_pkey_get_public() reads "file://..." from disk.
        EventDispatcher::add_filter_listener(OidcConfigFilter::class, function (array $config) {
            $config['oidcCertificateString'] = 'file:///var/www/html/config/.env#-----BEGIN ';

            return $config;
        });

        $this->assertSame('', $this->oidcConfig([])->get('oidcCertificateString'));
    }

    public function test_an_inline_pem_certificate_from_a_listener_is_used(): void
    {
        $pem = "-----BEGIN PUBLIC KEY-----\nMIIB\n-----END PUBLIC KEY-----";
        EventDispatcher::add_filter_listener(OidcConfigFilter::class, function (array $config) use ($pem) {
            $config['oidcCertificateString'] = $pem;

            return $config;
        });

        $this->assertSame($pem, $this->oidcConfig([])->get('oidcCertificateString'));
    }
}
