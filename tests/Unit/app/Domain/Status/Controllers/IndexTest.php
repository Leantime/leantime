<?php

namespace Tests\Unit\app\Domain\Status\Controllers;

use Leantime\Core\Application;
use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Bootstrap\LoadConfig;
use Leantime\Core\Bootstrap\SetRequestForConsole;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Status\Controllers\Index;

/**
 * Unit tests for the public /status discovery endpoint.
 *
 * Pins the contract the mobile app relies on (authMethods + oidcLoginUrl drive
 * whether the SSO button appears) AND the security tier: the unauthenticated
 * response must NEVER leak a plugin/version inventory.
 */
class IndexTest extends \Unit\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(APP_ROOT);
        $this->app->bootstrapWith([LoadConfig::class, SetRequestForConsole::class]);
        $this->app->boot();
        $this->app['view'] = $this->createMock(\Illuminate\View\Factory::class);
        $this->app['session'] = $this->createMock(\Illuminate\Session\SessionManager::class);
        $this->app->instance(PermissionEnforcer::class, $this->createMock(PermissionEnforcer::class));
    }

    private function makeController(array $overrides): Index
    {
        // Environment's constructor overwrites known config keys with
        // env-resolved defaults, so set the values AFTER construction.
        $env = new Environment;
        $env->set('oidcEnable', $overrides['oidcEnable'] ?? false);
        $env->set('useLdap', $overrides['useLdap'] ?? false);
        $env->set('sitename', $overrides['sitename'] ?? 'Leantime');

        $request = IncomingRequest::create('https://demo.leantime.io/status', 'GET');
        $this->app->instance(IncomingRequest::class, $request);
        $this->app->instance(Environment::class, $env);
        $this->app->instance(AppSettings::class, new AppSettings);

        return new Index($request, $this->createMock(Template::class), $this->createMock(Language::class));
    }

    private function bodyOf($response): array
    {
        return json_decode($response->getContent(), true);
    }

    public function test_password_only_when_no_sso_configured(): void
    {
        $response = $this->makeController(['oidcEnable' => false, 'useLdap' => false, 'sitename' => 'Acme'])->get([]);
        $body = $this->bodyOf($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['password'], $body['authMethods']);
        $this->assertArrayNotHasKey('oidcLoginUrl', $body);
        $this->assertSame('Acme', $body['instanceName']);
        $this->assertTrue($body['mobileAuthEnabled']);
    }

    public function test_oidc_enabled_advertises_oidc_and_login_url(): void
    {
        $response = $this->makeController(['oidcEnable' => true, 'useLdap' => false, 'sitename' => 'Acme'])->get([]);
        $body = $this->bodyOf($response);

        $this->assertContains('oidc', $body['authMethods']);
        $this->assertSame('https://demo.leantime.io/oidc/login', $body['oidcLoginUrl']);
    }

    public function test_response_never_leaks_a_plugin_or_version_inventory(): void
    {
        // The unauthenticated tier must not become a recon gift.
        $response = $this->makeController(['oidcEnable' => true, 'useLdap' => false])->get([]);
        $body = $this->bodyOf($response);

        $this->assertArrayNotHasKey('plugins', $body);
        $this->assertArrayNotHasKey('dbVersion', $body);
        $this->assertArrayHasKey('version', $body);
    }
}
