<?php

namespace Unit\app\Core\Middleware;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Middleware\RequestRateLimiter;
use Leantime\Core\Middleware\TrustProxies;
use Symfony\Component\HttpFoundation\Response;

/**
 * Brute-force protection on the authentication endpoints: login (incl. path variants and a
 * per-username budget), password reset and second-factor entry; plus the client IP the limiter
 * keys on must not be spoofable from the internet via X-Forwarded-For.
 */
class RequestRateLimiterTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private array $trustedProxiesBackup;

    private int $trustedHeadersBackup;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);
        session()->flush();
        session(['isInstalled' => true]);
        app('cache')->store()->flush();

        $this->trustedProxiesBackup = Request::getTrustedProxies();
        $this->trustedHeadersBackup = Request::getTrustedHeaderSet();
    }

    protected function tearDown(): void
    {
        Request::setTrustedProxies($this->trustedProxiesBackup, $this->trustedHeadersBackup);

        parent::tearDown();
    }

    private function limiter(array $limits = []): RequestRateLimiter
    {
        $limits += [
            'ratelimitGeneral' => 2000,
            'ratelimitApi' => 120,
            'ratelimitAuth' => 3,
            'ratelimitMcp' => 300,
            'ratelimitSignup' => 5,
            'ratelimitPasswordReset' => 2,
            'ratelimitTwofa' => 2,
        ];

        $config = $this->make(Environment::class, [
            'has' => fn ($key) => array_key_exists($key, $limits),
            'get' => fn ($key, $default = null) => $limits[$key] ?? $default,
        ]);

        return new RequestRateLimiter($config, app(RateLimiter::class));
    }

    private function send(RequestRateLimiter $limiter, string $uri, string $method = 'GET', array $params = [], string $ip = '203.0.113.10'): int
    {
        $request = IncomingRequest::create($uri, $method, $params, [], [], ['REMOTE_ADDR' => $ip]);

        return $limiter->handle($request, fn () => new Response('ok'))->getStatusCode();
    }

    public function test_login_path_variants_share_the_login_budget(): void
    {
        $limiter = $this->limiter();

        $this->assertSame(200, $this->send($limiter, '/auth/login'));
        $this->assertSame(200, $this->send($limiter, '/auth/login/anything'));
        $this->assertSame(200, $this->send($limiter, '/Auth/Login/'));
        $this->assertSame(429, $this->send($limiter, '/auth//login'), 'all variants hit the same login limit');
    }

    public function test_login_budget_is_per_ip_regardless_of_session_user(): void
    {
        $limiter = $this->limiter();

        session(['userdata' => ['id' => 1]]);
        $this->assertSame(200, $this->send($limiter, '/auth/login'));
        session(['userdata' => ['id' => 2]]);
        $this->assertSame(200, $this->send($limiter, '/auth/login'));
        session()->forget('userdata');
        $this->assertSame(200, $this->send($limiter, '/auth/login'));
        session(['userdata' => ['id' => 3]]);
        $this->assertSame(429, $this->send($limiter, '/auth/login'), 'rotating sessions must not widen the login budget');

        $this->assertSame(200, $this->send($limiter, '/auth/login', 'GET', [], '198.51.100.99'), 'another IP has its own budget');
    }

    public function test_login_is_also_limited_per_username_across_ips(): void
    {
        $limiter = $this->limiter();

        $this->assertSame(200, $this->send($limiter, '/auth/login', 'POST', ['username' => 'victim@example.com'], '198.51.100.1'));
        $this->assertSame(200, $this->send($limiter, '/auth/login', 'POST', ['username' => 'Victim@example.com'], '198.51.100.2'));
        $this->assertSame(200, $this->send($limiter, '/auth/login', 'POST', ['username' => 'victim@example.com '], '198.51.100.3'));
        $this->assertSame(429, $this->send($limiter, '/auth/login', 'POST', ['username' => 'victim@example.com'], '198.51.100.4'));

        $this->assertSame(200, $this->send($limiter, '/auth/login', 'POST', ['username' => 'other@example.com'], '198.51.100.5'));
    }

    public function test_password_reset_posts_are_limited(): void
    {
        $limiter = $this->limiter();

        $this->assertSame(200, $this->send($limiter, '/auth/resetPw', 'POST', ['username' => 'a@example.com']));
        $this->assertSame(200, $this->send($limiter, '/auth/resetpw/sometoken', 'POST'));
        $this->assertSame(429, $this->send($limiter, '/auth/resetPw', 'POST', ['username' => 'b@example.com']));

        // Opening the reset form is not throttled.
        $this->assertSame(200, $this->send($limiter, '/auth/resetPw'));
    }

    public function test_two_factor_codes_are_limited_per_user_regardless_of_ip(): void
    {
        $limiter = $this->limiter();
        session(['userdata' => ['id' => 9]]);

        $this->assertSame(200, $this->send($limiter, '/twoFA/verify', 'POST', ['twoFA_code' => '000000'], '198.51.100.1'));
        $this->assertSame(200, $this->send($limiter, '/twofa/verify', 'POST', ['twoFA_code' => '000001'], '198.51.100.2'));
        $this->assertSame(429, $this->send($limiter, '/twoFA/verify', 'POST', ['twoFA_code' => '000002'], '198.51.100.3'));
    }

    public function test_default_trusted_proxies_ignore_forwarded_for_from_public_clients(): void
    {
        Request::setTrustedProxies(TrustProxies::resolveTrustedProxies(''), Request::HEADER_X_FORWARDED_FOR);

        $direct = IncomingRequest::create('/auth/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_X_FORWARDED_FOR' => '1.2.3.4',
        ]);
        $this->assertSame('203.0.113.10', $direct->getClientIp(), 'a public client cannot spoof its IP');

        $viaDockerProxy = IncomingRequest::create('/auth/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '172.18.0.2',
            'HTTP_X_FORWARDED_FOR' => '1.2.3.4',
        ]);
        $this->assertSame('1.2.3.4', $viaDockerProxy->getClientIp(), 'a proxy on a private network is trusted');
    }

    public function test_explicit_trusted_proxy_list_is_used(): void
    {
        $this->assertSame(['10.0.0.5', '192.0.2.0/24'], TrustProxies::resolveTrustedProxies(' 10.0.0.5, 192.0.2.0/24 ,'));
        $this->assertSame(['PRIVATE_SUBNETS'], TrustProxies::resolveTrustedProxies(null));
    }

    public function test_default_proxy_config_does_not_block_direct_clients(): void
    {
        Request::setTrustedProxies(TrustProxies::resolveTrustedProxies(''), Request::HEADER_X_FORWARDED_FOR);

        $middleware = new TrustProxies($this->make(Environment::class, ['get' => fn ($key, $default = null) => '']));
        $request = IncomingRequest::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.10']);

        $this->assertSame(200, $middleware->handle($request, fn () => new Response('ok'))->getStatusCode());
    }
}
