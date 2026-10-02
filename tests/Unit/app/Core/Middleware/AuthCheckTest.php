<?php

namespace Unit\app\Core\Middleware;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Middleware\AuthCheck;
use Leantime\Domain\Api\Services\Api;
use Leantime\Domain\Auth\Guards\WebGuard;
use Leantime\Domain\Users\Services\Users;

/**
 * Guards the Bearer-auth regression (3.9.0): the permission engine reads the user's id + role from
 * session('userdata'), which the x-api-key guard establishes as a side effect of getAPIKeyUser()
 * but the Sanctum (Bearer) guard never did — so every gated @api method denied Bearer requests.
 * establishApiUserSession() makes the API auth path uniform: any guard that resolves a user has the
 * same userdata built from the canonical user row, through the same setApiUserSession() builder.
 *
 * This tests the middleware's responsibility — resolve the user id, fetch the canonical row, and
 * hand it to the session builder, idempotently. The builder itself is covered by ApiServiceTest.
 */
class AuthCheckTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * A request whose user() resolver returns an object with the given id — i.e. a guard (Sanctum
     * or x-api-key) has authenticated, but userdata has not been established yet.
     */
    private function apiRequestForUser(int $userId): IncomingRequest
    {
        $request = IncomingRequest::create('/api/jsonrpc', 'POST');
        $request->setUserResolver(fn () => (object) ['id' => $userId]);

        return $request;
    }

    /** Invoke the protected establishApiUserSession() on a constructor-less AuthCheck. */
    private function establish(IncomingRequest $request): void
    {
        $authCheck = $this->make(AuthCheck::class);
        (fn () => $this->establishApiUserSession($request))->call($authCheck);
    }

    public function test_establishes_userdata_from_the_canonical_row_when_missing(): void
    {
        session()->forget('userdata');

        $row = ['id' => 42, 'firstname' => 'Gloria', 'role' => 20];

        app()->instance(Users::class, $this->make(Users::class, [
            'getUser' => fn ($id = null) => (int) $id === 42 ? $row : false,
        ]));

        $captured = null;
        app()->instance(Api::class, $this->make(Api::class, [
            'setApiUserSession' => function (array $user, bool $isExternalAuth = false) use (&$captured) {
                $captured = ['user' => $user, 'external' => $isExternalAuth];
            },
        ]));

        $this->establish($this->apiRequestForUser(42));

        $this->assertSame($row, $captured['user'] ?? null, 'the canonical row must be handed to the session builder');
        $this->assertTrue($captured['external'] ?? false, 'API sessions are external auth');
    }

    public function test_is_idempotent_when_userdata_already_exists(): void
    {
        // x-api-key (and stateful web) already populated userdata before this runs — leave it,
        // and never re-resolve the user.
        session(['userdata' => ['id' => 7, 'role' => 'admin']]);

        app()->instance(Users::class, $this->make(Users::class, [
            'getUser' => function ($id = null) {
                $this->fail('must not re-resolve the user when userdata already exists');
            },
        ]));

        $called = false;
        app()->instance(Api::class, $this->make(Api::class, [
            'setApiUserSession' => function (array $user, bool $isExternalAuth = false) use (&$called) {
                $called = true;
            },
        ]));

        $this->establish($this->apiRequestForUser(42));

        $this->assertFalse($called, 'must not rebuild an already-established session');
        $this->assertSame(7, session('userdata.id'), 'existing userdata must be left untouched');
    }

    /**
     * The mobile SSO exchange (/oidc/mobile/exchange) arrives with no session
     * cookie — the validated one-time code + PKCE verifier are the authorization —
     * so it must be allow-listed as public. Guards that allow-list from regressing.
     */
    public function test_oidc_mobile_exchange_is_a_public_route(): void
    {
        $authCheck = $this->make(AuthCheck::class);

        $this->assertTrue(
            $authCheck->isPublicController('oidc.mobile.exchange'),
            'the mobile exchange endpoint must be public (no session at exchange time)'
        );

        // Negative control: an oidc sub-route that is NOT allow-listed stays private.
        $this->assertFalse($authCheck->isPublicController('oidc.settings.save'));
    }

    public function test_status_discovery_is_a_public_route(): void
    {
        $authCheck = $this->make(AuthCheck::class);

        // The mobile app hits /status unauthenticated at connect time to discover
        // login methods, so the route must be public.
        $this->assertTrue($authCheck->isPublicController('status.index'));
        $this->assertTrue($authCheck->isPublicController('status'));
    }

    // ---------------------------------------------------------------------
    // API auth path: the cookie session guard is only honoured for same-origin XHR calls whose
    // session completed 2FA. Everything else needs an API key or Bearer token.
    // ---------------------------------------------------------------------

    /**
     * An AuthCheck whose auth factory serves the given guards, counting failed-auth limiter hits.
     *
     * @param  array<string, Guard>  $guards
     */
    private function authCheckWithGuards(array $guards, int &$limiterHits): AuthCheck
    {
        $authFactory = $this->makeEmpty(AuthFactory::class, [
            'guard' => fn ($name = null) => $guards[$name],
        ]);

        return $this->make(AuthCheck::class, [
            'auth' => $authFactory,
            'hitFailedAuthLimiter' => function () use (&$limiterHits): void {
                $limiterHits++;
            },
        ]);
    }

    /** @return true|\Symfony\Component\HttpFoundation\Response */
    private function authenticateApi(AuthCheck $authCheck, IncomingRequest $request, array $guards): mixed
    {
        return (fn () => $this->authenticateApi($request, $guards))->call($authCheck);
    }

    private function loggedInSessionGuard(): WebGuard
    {
        return $this->make(WebGuard::class, ['check' => fn () => true]);
    }

    public function test_session_cookie_does_not_authenticate_a_non_xhr_api_request(): void
    {
        session(['userdata' => ['id' => 3, 'role' => 'editor']]);
        $limiterHits = 0;
        $authCheck = $this->authCheckWithGuards(['leantime' => $this->loggedInSessionGuard()], $limiterHits);

        // A top-level navigation / cross-site form: no X-Requested-With header.
        $request = ApiRequest::create('/api/jsonrpc?method=leantime.rpc.users.getAll', 'GET');

        $result = $this->authenticateApi($authCheck, $request, ['leantime']);

        $this->assertNotTrue($result);
        $this->assertSame(401, $result->getStatusCode());
    }

    public function test_session_awaiting_two_factor_does_not_authenticate_api(): void
    {
        session(['userdata' => ['id' => 3, 'role' => 'editor', 'twoFAEnabled' => true, 'twoFAVerified' => false]]);
        $limiterHits = 0;
        $authCheck = $this->authCheckWithGuards(['leantime' => $this->loggedInSessionGuard()], $limiterHits);

        $request = ApiRequest::create('/api/jsonrpc', 'POST', server: ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $result = $this->authenticateApi($authCheck, $request, ['leantime']);

        $this->assertNotTrue($result);
        $this->assertSame(401, $result->getStatusCode());
        $this->assertSame(0, $limiterHits, 'a pending-2FA browser session must not burn the per-IP failed-auth budget');
    }

    public function test_verified_session_authenticates_same_origin_xhr(): void
    {
        session(['userdata' => ['id' => 3, 'role' => 'editor', 'twoFAEnabled' => true, 'twoFAVerified' => true]]);
        $limiterHits = 0;
        $authCheck = $this->authCheckWithGuards(['leantime' => $this->loggedInSessionGuard()], $limiterHits);

        $request = ApiRequest::create('/api/jsonrpc', 'POST', server: ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->assertTrue($this->authenticateApi($authCheck, $request, ['leantime']));
    }

    public function test_api_key_guard_still_authenticates_without_xhr(): void
    {
        session(['userdata' => ['id' => 9, 'role' => 'editor']]);
        $limiterHits = 0;
        $apiKeyGuard = $this->makeEmpty(Guard::class, ['check' => fn () => true]);
        $authCheck = $this->authCheckWithGuards([
            'leantime' => $this->loggedInSessionGuard(),
            'jsonRpc' => $apiKeyGuard,
        ], $limiterHits);

        $request = ApiRequest::create('/api/jsonrpc', 'POST', server: ['HTTP_X_API_KEY' => 'lt_key_secret']);

        $this->assertTrue($this->authenticateApi($authCheck, $request, ['leantime', 'jsonRpc']));
    }

    public function test_non_canonical_api_path_is_rejected_before_authentication(): void
    {
        $limiterHits = 0;
        $authCheck = $this->authCheckWithGuards([], $limiterHits);

        $request = new IncomingRequest([], [], [], [], [], [
            'REQUEST_URI' => '/%61pi/jsonrpc?method=x',
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
            'HTTP_HOST' => 'localhost',
        ]);

        $response = $authCheck->handle($request, function () {
            $this->fail('a non-canonical API path must never reach the next middleware');
        });

        $this->assertSame(400, $response->getStatusCode());
    }
}
