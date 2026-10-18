<?php

namespace Unit\app\Core\Middleware;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Middleware\StartSession;
use ReflectionMethod;
use Unit\TestCase;

/**
 * JSON-RPC requests used to be stateless: the app's own JS (leantime.rpc) read the user's session
 * but its writes were dropped, so saved UI states (accordions, sidebar widths) showed up one page
 * load late. Browser RPC (AJAX + session cookie) now persists like a web request; external API
 * clients and cron stay stateless.
 */
class BrowserRpcSessionTest extends TestCase
{
    private function shouldPersist(IncomingRequest $request): bool
    {
        $method = new ReflectionMethod(StartSession::class, 'shouldPersistSession');
        $method->setAccessible(true);

        return $method->invoke(new StartSession(app('session')), $request);
    }

    private function request(string $path, bool $ajax, bool $withSessionCookie): IncomingRequest
    {
        $cookieName = app('session')->getSessionConfig()['cookie'];
        $cookies = $withSessionCookie ? [$cookieName => 'abc'] : [];
        $server = $ajax ? ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'] : [];

        return IncomingRequest::create($path, 'POST', [], $cookies, [], $server);
    }

    public function test_the_apps_own_rpc_calls_persist_the_session(): void
    {
        $this->assertTrue($this->shouldPersist($this->request('/api/jsonrpc', true, true)));
    }

    public function test_an_api_client_without_a_session_cookie_stays_stateless(): void
    {
        $this->assertFalse($this->shouldPersist($this->request('/api/jsonrpc', true, false)));
        $this->assertFalse($this->shouldPersist($this->request('/api/jsonrpc', false, false)));
    }

    public function test_a_non_ajax_rpc_request_with_a_cookie_stays_stateless(): void
    {
        $this->assertFalse($this->shouldPersist($this->request('/api/jsonrpc', false, true)));
    }

    public function test_cron_stays_stateless(): void
    {
        $this->assertFalse($this->shouldPersist($this->request('/cron/run', true, true)));
    }

    public function test_web_requests_still_persist(): void
    {
        $this->assertTrue($this->shouldPersist($this->request('/dashboard/home', false, true)));
    }
}
