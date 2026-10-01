<?php

namespace Unit\app\Core\Middleware;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Middleware\AuthCheck;
use Leantime\Domain\Api\Services\Api;
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
}
