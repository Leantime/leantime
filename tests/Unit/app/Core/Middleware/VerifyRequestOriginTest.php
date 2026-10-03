<?php

namespace Unit\app\Core\Middleware;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Middleware\VerifyRequestOrigin;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * State-changing requests a browser sends on behalf of another site must be rejected, using the
 * Sec-Fetch-Site / Origin / Referer headers browsers attach (no form tokens needed). Token-
 * authenticated API clients, non-browser clients and identity-provider callbacks pass.
 */
class VerifyRequestOriginTest extends TestCase
{
    private const APP_HOST = 'leantime.example.com';

    private function middleware(bool $enabled = true, string $appUrl = ''): VerifyRequestOrigin
    {
        $config = app(Environment::class);
        $config->set('csrfOriginCheck', $enabled);
        $config->set('appUrl', $appUrl);

        return new VerifyRequestOrigin($config);
    }

    /**
     * @param  array<string, string>  $headers  Header name => value
     */
    private function request(string $method, array $headers = [], string $path = '/tickets/delTicket/5', string $host = self::APP_HOST): IncomingRequest
    {
        $server = ['HTTP_HOST' => $host, 'HTTPS' => 'on'];
        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return IncomingRequest::create('https://'.$host.$path, $method, [], [], [], $server);
    }

    private function passes(IncomingRequest $request, ?VerifyRequestOrigin $middleware = null): bool
    {
        $middleware ??= $this->middleware();

        $response = $middleware->handle($request, fn () => new Response('next'));

        return $response->getStatusCode() === 200 && $response->getContent() === 'next';
    }

    public function test_cross_site_post_is_rejected(): void
    {
        $request = $this->request('POST', ['Sec-Fetch-Site' => 'cross-site', 'Origin' => 'https://evil.example.net']);

        $response = $this->middleware()->handle($request, fn () => new Response('next'));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_cross_site_login_post_is_rejected(): void
    {
        // Login is public and cookie-less, but forging it still plants the attacker's session.
        $request = $this->request('POST', ['Sec-Fetch-Site' => 'cross-site'], '/auth/login');

        $this->assertFalse($this->passes($request));
    }

    public function test_same_origin_post_passes(): void
    {
        $this->assertTrue($this->passes($this->request('POST', ['Sec-Fetch-Site' => 'same-origin'])));
    }

    public function test_user_initiated_post_passes(): void
    {
        $this->assertTrue($this->passes($this->request('POST', ['Sec-Fetch-Site' => 'none'])));
    }

    public function test_safe_methods_are_never_checked(): void
    {
        $this->assertTrue($this->passes($this->request('GET', ['Sec-Fetch-Site' => 'cross-site'])));
        $this->assertTrue($this->passes($this->request('HEAD', ['Sec-Fetch-Site' => 'cross-site'])));
    }

    public function test_put_patch_delete_are_checked(): void
    {
        foreach (['PUT', 'PATCH', 'DELETE'] as $method) {
            $this->assertFalse(
                $this->passes($this->request($method, ['Sec-Fetch-Site' => 'cross-site'])),
                $method.' from another site must be rejected'
            );
        }
    }

    public function test_method_override_to_get_does_not_hide_a_cross_site_post(): void
    {
        IncomingRequest::enableHttpMethodParameterOverride();
        $request = $this->request('POST', ['Sec-Fetch-Site' => 'cross-site']);
        $request->request->set('_method', 'GET');

        $this->assertFalse($this->passes($request));
    }

    public function test_same_site_sibling_subdomain_is_rejected(): void
    {
        $request = $this->request('POST', ['Sec-Fetch-Site' => 'same-site', 'Origin' => 'https://other.example.com']);

        $this->assertFalse($this->passes($request));
    }

    public function test_same_site_with_matching_origin_passes(): void
    {
        $request = $this->request('POST', ['Sec-Fetch-Site' => 'same-site', 'Origin' => 'https://'.self::APP_HOST]);

        $this->assertTrue($this->passes($request));
    }

    public function test_same_site_without_origin_is_rejected(): void
    {
        $this->assertFalse($this->passes($this->request('POST', ['Sec-Fetch-Site' => 'same-site'])));
    }

    public function test_legacy_browser_origin_is_compared_with_request_host(): void
    {
        $this->assertTrue($this->passes($this->request('POST', ['Origin' => 'https://'.self::APP_HOST])));
        $this->assertFalse($this->passes($this->request('POST', ['Origin' => 'https://evil.example.net'])));
        $this->assertFalse($this->passes($this->request('POST', ['Origin' => 'https://'.self::APP_HOST.':8443'])));
    }

    public function test_opaque_null_origin_is_rejected(): void
    {
        $this->assertFalse($this->passes($this->request('POST', ['Origin' => 'null'])));
    }

    public function test_referer_is_used_when_origin_is_missing(): void
    {
        $this->assertTrue($this->passes($this->request('POST', ['Referer' => 'https://'.self::APP_HOST.'/dashboard/show'])));
        $this->assertFalse($this->passes($this->request('POST', ['Referer' => 'https://evil.example.net/page'])));
    }

    public function test_request_without_any_provenance_header_passes(): void
    {
        // curl, mobile app, server-to-server webhook: no browser, no ambient cookie to abuse.
        $this->assertTrue($this->passes($this->request('POST')));
    }

    public function test_configured_app_url_host_is_accepted_behind_a_proxy(): void
    {
        // The proxy forwards an internal Host header; the browser reports the public origin.
        $middleware = $this->middleware(appUrl: 'https://pm.example.org');
        $request = $this->request('POST', ['Origin' => 'https://pm.example.org'], host: 'leantime-app:8080');

        $this->assertTrue($this->passes($request, $middleware));
    }

    public function test_scheme_mismatch_from_tls_terminating_proxy_still_matches_host(): void
    {
        $request = IncomingRequest::create('http://'.self::APP_HOST.'/tickets/delTicket/5', 'POST', [], [], [], [
            'HTTP_HOST' => self::APP_HOST,
            'HTTP_ORIGIN' => 'https://'.self::APP_HOST,
        ]);

        $this->assertTrue($this->passes($request));
    }

    public function test_insecure_origin_cannot_post_to_https_app(): void
    {
        // Content on http://same-host must not submit to the https app with its cookies.
        $this->assertFalse($this->passes($this->request('POST', ['Origin' => 'http://'.self::APP_HOST])));
        $this->assertFalse($this->passes($this->request('POST', ['Referer' => 'http://'.self::APP_HOST.'/page'])));
    }

    public function test_api_key_and_bearer_requests_are_exempt(): void
    {
        $this->assertTrue($this->passes($this->request('POST', ['Sec-Fetch-Site' => 'cross-site', 'x-api-key' => 'lt_user_key'], '/api/jsonrpc')));
        $this->assertTrue($this->passes($this->request('POST', ['Sec-Fetch-Site' => 'cross-site', 'Authorization' => 'Bearer abc123'], '/mcp')));
    }

    public function test_api_path_without_token_is_not_exempt(): void
    {
        // Cookie-authenticated JSON-RPC from another site must not ride on the /api prefix.
        $this->assertFalse($this->passes($this->request('POST', ['Sec-Fetch-Site' => 'cross-site'], '/api/jsonrpc')));
    }

    public function test_identity_provider_callbacks_are_exempt(): void
    {
        $this->assertTrue($this->passes($this->request('POST', ['Sec-Fetch-Site' => 'cross-site'], '/oidc/callback')));
        $this->assertTrue($this->passes($this->request('POST', ['Sec-Fetch-Site' => 'cross-site'], '/advancedAuth/callback/saml2')));
    }

    public function test_plugins_can_register_exempt_paths(): void
    {
        EventDispatcher::add_filter_listener(
            'leantime.core.middleware.verifyrequestorigin.isExemptPath.csrfOriginExemptPaths',
            function (array $paths) {
                $paths[] = 'myplugin/acs';

                return $paths;
            }
        );

        $this->assertTrue($this->passes($this->request('POST', ['Sec-Fetch-Site' => 'cross-site'], '/myplugin/acs')));
    }

    public function test_kill_switch_disables_the_check(): void
    {
        $request = $this->request('POST', ['Sec-Fetch-Site' => 'cross-site']);

        $this->assertTrue($this->passes($request, $this->middleware(enabled: false)));
    }
}
