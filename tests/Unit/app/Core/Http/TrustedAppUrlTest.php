<?php

namespace Unit\app\Core\Http;

use Illuminate\Http\Request;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Unit\TestCase;

/**
 * Links in emails must only use a trusted app URL: LEAN_APP_URL, else a URL recorded at install
 * or at the first owner/admin login. The request Host header is never trusted on its own.
 */
class TrustedAppUrlTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<string, mixed> */
    private array $settings = [];

    protected function tearDown(): void
    {
        Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);

        parent::tearDown();
    }

    private function makeTrustedAppUrl(string $configuredAppUrl = ''): TrustedAppUrl
    {
        $config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $key === 'appUrl' ? $configuredAppUrl : $default,
        ]);

        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn (string $key, mixed $default = false) => $this->settings[$key] ?? $default,
            'saveSetting' => function (string $key, mixed $value) {
                $this->settings[$key] = $value;

                return true;
            },
        ]);

        return new TrustedAppUrl($config, $settingsRepo);
    }

    private function requestFor(string $url, array $server = []): Request
    {
        return Request::create($url, 'POST', [], [], [], $server);
    }

    public function test_configured_app_url_wins_over_stored_url_and_request_host(): void
    {
        $this->settings[TrustedAppUrl::SETTING_KEY] = 'https://stored.example.com';

        $trustedAppUrl = $this->makeTrustedAppUrl('https://pm.example.com/');

        $this->assertSame('https://pm.example.com', $trustedAppUrl->get());
        $this->assertTrue($trustedAppUrl->isConfigured());
    }

    public function test_stored_url_is_used_when_app_url_is_empty(): void
    {
        $this->settings[TrustedAppUrl::SETTING_KEY] = 'https://stored.example.com';

        $this->assertSame('https://stored.example.com', $this->makeTrustedAppUrl()->get());
    }

    public function test_unknown_when_neither_configured_nor_stored(): void
    {
        $this->assertNull($this->makeTrustedAppUrl()->get());
    }

    public function test_invalid_stored_values_are_ignored(): void
    {
        foreach (['javascript:alert(1)', 'pm.example.com', 'https://user:pw@pm.example.com', 'https://bad_host!'] as $invalid) {
            $this->settings[TrustedAppUrl::SETTING_KEY] = $invalid;
            $this->assertNull($this->makeTrustedAppUrl()->get(), $invalid);
        }
    }

    public function test_admin_login_records_the_url_when_none_is_stored(): void
    {
        $trustedAppUrl = $this->makeTrustedAppUrl();

        $trustedAppUrl->learnFromAdminLogin('admin', $this->requestFor('https://pm.example.com/auth/login'));

        $this->assertSame('https://pm.example.com', $this->settings[TrustedAppUrl::SETTING_KEY]);
        $this->assertSame('https://pm.example.com', $trustedAppUrl->get());
    }

    public function test_owner_login_by_numeric_role_records_the_url(): void
    {
        $this->makeTrustedAppUrl()->learnFromAdminLogin(50, $this->requestFor('https://pm.example.com/auth/login'));

        $this->assertSame('https://pm.example.com', $this->settings[TrustedAppUrl::SETTING_KEY]);
    }

    public function test_login_below_admin_never_records_the_url(): void
    {
        $trustedAppUrl = $this->makeTrustedAppUrl();

        foreach (['manager', 'editor', 30, 'readonly', null] as $role) {
            $trustedAppUrl->learnFromAdminLogin($role, $this->requestFor('https://evil.example.net/auth/login'));
        }

        $this->assertArrayNotHasKey(TrustedAppUrl::SETTING_KEY, $this->settings);
    }

    public function test_admin_login_never_overwrites_a_stored_url(): void
    {
        $this->settings[TrustedAppUrl::SETTING_KEY] = 'https://pm.example.com';

        $this->makeTrustedAppUrl()->learnFromAdminLogin('owner', $this->requestFor('https://other.example.com/auth/login'));

        $this->assertSame('https://pm.example.com', $this->settings[TrustedAppUrl::SETTING_KEY]);
    }

    public function test_nothing_is_recorded_when_app_url_is_configured(): void
    {
        $this->makeTrustedAppUrl('https://pm.example.com')->learnFromAdminLogin('owner', $this->requestFor('https://other.example.com/auth/login'));

        $this->assertArrayNotHasKey(TrustedAppUrl::SETTING_KEY, $this->settings);
    }

    public function test_forwarded_host_from_an_untrusted_client_is_not_learned(): void
    {
        Request::setTrustedProxies(['10.0.0.0/8'], Request::HEADER_X_FORWARDED_HOST);

        $request = $this->requestFor('https://pm.example.com/auth/login', [
            'REMOTE_ADDR' => '203.0.113.9',
            'HTTP_X_FORWARDED_HOST' => 'evil.example.net',
        ]);

        $this->assertNull(TrustedAppUrl::urlFromRequest($request));

        $this->makeTrustedAppUrl()->learnFromAdminLogin('owner', $request);

        $this->assertArrayNotHasKey(TrustedAppUrl::SETTING_KEY, $this->settings);
    }

    public function test_install_records_the_url_once(): void
    {
        $trustedAppUrl = $this->makeTrustedAppUrl();

        $trustedAppUrl->learnFromInstall($this->requestFor('http://localhost:8090/install'));
        $trustedAppUrl->learnFromInstall($this->requestFor('http://other.example.com/install'));

        $this->assertSame('http://localhost:8090', $this->settings[TrustedAppUrl::SETTING_KEY]);
    }

    public function test_rebase_moves_base_url_links_to_the_trusted_url(): void
    {
        $this->settings[TrustedAppUrl::SETTING_KEY] = 'https://pm.example.com';
        $trustedAppUrl = $this->makeTrustedAppUrl();
        $baseUrl = rtrim(BASE_URL, '/');

        $this->assertSame('https://pm.example.com/tickets/showKanban', $trustedAppUrl->rebase($baseUrl.'/tickets/showKanban'));
        $this->assertSame('https://pm.example.com#/tickets/showTicket/5', $trustedAppUrl->rebase($baseUrl.'#/tickets/showTicket/5'));
        $this->assertSame('https://external.example.org/x', $trustedAppUrl->rebase('https://external.example.org/x'));
        $this->assertSame($baseUrl.'.evil.net/x', $trustedAppUrl->rebase($baseUrl.'.evil.net/x'));
    }

    public function test_rebase_and_for_links_keep_base_url_when_nothing_is_trusted(): void
    {
        $trustedAppUrl = $this->makeTrustedAppUrl();
        $baseUrl = rtrim(BASE_URL, '/');

        $this->assertSame($baseUrl.'/tickets/showKanban', $trustedAppUrl->rebase($baseUrl.'/tickets/showKanban'));
        $this->assertSame($baseUrl, $trustedAppUrl->forLinks());
    }

    public function test_admin_warning(): void
    {
        $request = $this->requestFor('https://pm.example.com/dashboard/home');

        $this->assertSame(TrustedAppUrl::WARNING_MISSING, $this->makeTrustedAppUrl()->adminWarning('admin', $request));
        $this->assertNull($this->makeTrustedAppUrl()->adminWarning('editor', $request));
        $this->assertNull($this->makeTrustedAppUrl('https://pm.example.com')->adminWarning('owner', $request));

        $this->settings[TrustedAppUrl::SETTING_KEY] = 'https://pm.example.com';
        $this->assertNull($this->makeTrustedAppUrl()->adminWarning('owner', $request));

        $this->settings[TrustedAppUrl::SETTING_KEY] = 'https://old.example.com';
        $this->assertSame(TrustedAppUrl::WARNING_MISMATCH, $this->makeTrustedAppUrl()->adminWarning('owner', $request));
    }
}
