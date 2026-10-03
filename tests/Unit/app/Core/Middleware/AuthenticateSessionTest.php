<?php

namespace Unit\app\Core\Middleware;

use Leantime\Core\Auth\PasswordFingerprint;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Middleware\AuthenticateSession;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Auth\Repositories\Auth as AuthRepository;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session lifecycle hardening:
 *  - a password change (reset, admin edit, own change elsewhere) logs out every other session,
 *    while the session that changed its own password stays signed in;
 *  - login regenerates the session id (no fixation) and logout destroys the session.
 */
class AuthenticateSessionTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private int $logoutCalls = 0;

    protected function setUp(): void
    {
        parent::setUp();

        session()->flush();
        $this->logoutCalls = 0;

        app()->instance(AuthService::class, $this->make(AuthService::class, [
            'logout' => function () {
                $this->logoutCalls++;
            },
        ]));
    }

    private function middlewareWithPasswordHash(string|false $passwordHash, int &$lookups = 0): AuthenticateSession
    {
        $userRepo = $this->make(UserRepository::class, [
            'getUser' => function ($id) use ($passwordHash, &$lookups) {
                $lookups++;

                return $passwordHash === false ? false : ['id' => (int) $id, 'password' => $passwordHash];
            },
        ]);

        return new AuthenticateSession($userRepo);
    }

    private function attachSession(IncomingRequest $request): IncomingRequest
    {
        $request->setLaravelSession(app('session')->driver());

        return $request;
    }

    private function runMiddleware(AuthenticateSession $middleware, IncomingRequest $request, bool &$reachedNext): Response
    {
        return $middleware->handle($request, function () use (&$reachedNext) {
            $reachedNext = true;

            return new Response('ok');
        });
    }

    public function test_session_with_current_password_passes(): void
    {
        session(['userdata' => ['id' => 7, 'pwfp' => PasswordFingerprint::of('$2y$hash-current')]]);

        $reached = false;
        $response = $this->runMiddleware($this->middlewareWithPasswordHash('$2y$hash-current'), $this->attachSession(IncomingRequest::create('/dashboard/home')), $reached);

        $this->assertTrue($reached);
        $this->assertSame('ok', $response->getContent());
        $this->assertSame(0, $this->logoutCalls);
    }

    public function test_session_is_logged_out_after_password_changed_elsewhere(): void
    {
        session(['userdata' => ['id' => 7, 'pwfp' => PasswordFingerprint::of('$2y$hash-old')]]);

        $reached = false;
        $response = $this->runMiddleware($this->middlewareWithPasswordHash('$2y$hash-new'), $this->attachSession(IncomingRequest::create('/dashboard/home')), $reached);

        $this->assertFalse($reached, 'the request must not reach the app');
        $this->assertSame(1, $this->logoutCalls);
        $this->assertTrue($response->isRedirect(BASE_URL.'/auth/login'));
    }

    public function test_htmx_request_gets_client_side_redirect(): void
    {
        session(['userdata' => ['id' => 7, 'pwfp' => PasswordFingerprint::of('$2y$hash-old')]]);

        $reached = false;
        $response = $this->runMiddleware($this->middlewareWithPasswordHash('$2y$hash-new'), $this->attachSession(HtmxRequest::create('/hx/tickets/foo')), $reached);

        $this->assertFalse($reached);
        $this->assertSame(BASE_URL.'/auth/login', $response->headers->get('HX-Redirect'));
    }

    public function test_deleted_user_is_logged_out(): void
    {
        session(['userdata' => ['id' => 7, 'pwfp' => PasswordFingerprint::of('$2y$x')]]);

        $reached = false;
        $this->runMiddleware($this->middlewareWithPasswordHash(false), $this->attachSession(IncomingRequest::create('/dashboard/home')), $reached);

        $this->assertFalse($reached);
        $this->assertSame(1, $this->logoutCalls);
    }

    public function test_legacy_session_without_fingerprint_must_sign_in_again(): void
    {
        session(['userdata' => ['id' => 7]]);

        $reached = false;
        $response = $this->runMiddleware($this->middlewareWithPasswordHash('$2y$hash-current'), $this->attachSession(IncomingRequest::create('/dashboard/home')), $reached);

        $this->assertFalse($reached, 'a session whose password state cannot be verified must not be trusted');
        $this->assertSame(1, $this->logoutCalls);
        $this->assertTrue($response->isRedirect(BASE_URL.'/auth/login'));
    }

    public function test_token_authenticated_api_request_is_not_checked(): void
    {
        session(['userdata' => ['id' => 7]]);

        $lookups = 0;
        $reached = false;
        $this->runMiddleware($this->middlewareWithPasswordHash('$2y$x', $lookups), $this->attachSession(ApiRequest::create('/api/jsonrpc', 'POST')), $reached);

        $this->assertTrue($reached);
        $this->assertSame(0, $lookups, 'API key / Bearer sessions carry no fingerprint and need no lookup');
    }

    public function test_guest_request_is_not_checked(): void
    {
        $lookups = 0;
        $reached = false;
        $this->runMiddleware($this->middlewareWithPasswordHash('$2y$x', $lookups), $this->attachSession(IncomingRequest::create('/auth/login')), $reached);

        $this->assertTrue($reached);
        $this->assertSame(0, $lookups);
    }

    public function test_own_password_change_refreshes_only_the_session_users_fingerprint(): void
    {
        session(['userdata' => ['id' => 7, 'pwfp' => PasswordFingerprint::of('$2y$old')]]);

        // Another user's password changed: this session is untouched.
        PasswordFingerprint::refreshForSessionUser(8, '$2y$someone-else');
        $this->assertSame(PasswordFingerprint::of('$2y$old'), session(PasswordFingerprint::SESSION_KEY));

        // The session user changed their own password: this session follows the new hash.
        PasswordFingerprint::refreshForSessionUser(7, '$2y$new');
        $this->assertSame(PasswordFingerprint::of('$2y$new'), session(PasswordFingerprint::SESSION_KEY));

        $reached = false;
        $this->runMiddleware($this->middlewareWithPasswordHash('$2y$new'), $this->attachSession(IncomingRequest::create('/dashboard/home')), $reached);
        $this->assertTrue($reached, 'the session that changed the password stays signed in');
    }

    public function test_fingerprint_does_not_expose_the_hash(): void
    {
        $fingerprint = PasswordFingerprint::of('$2y$10$abcdefghijklmnopqrstuv');

        $this->assertStringNotContainsString('abcdefghijklmnop', $fingerprint);
        $this->assertSame(64, strlen($fingerprint));
    }

    private function makeAuthService(): AuthService
    {
        return new AuthService(
            $this->make(EnvironmentCore::class),
            app('session'),
            $this->make(LanguageCore::class),
            $this->make(SettingRepository::class),
            $this->make(AuthRepository::class, [
                'updateUserSession' => fn () => true,
                'invalidateSession' => fn () => true,
            ]),
            $this->make(UserRepository::class),
            $this->make(AccessTokenRepository::class),
        );
    }

    public function test_login_regenerates_session_id_and_pins_password(): void
    {
        session()->start();
        session(['preLogin' => 'kept']);
        $idBeforeLogin = session()->getId();

        $this->makeAuthService()->setUserSession([
            'id' => 5,
            'firstname' => 'Ada',
            'username' => 'ada@example.com',
            'role' => 20,
            'password' => '$2y$hash-ada',
        ]);

        $this->assertNotSame($idBeforeLogin, session()->getId(), 'a planted pre-login session id must not survive login');
        $this->assertSame(5, session('userdata.id'));
        $this->assertSame(PasswordFingerprint::of('$2y$hash-ada'), session(PasswordFingerprint::SESSION_KEY));
    }

    public function test_second_factor_regenerates_session_id(): void
    {
        session()->start();
        session(['userdata' => ['id' => 5, 'twoFAVerified' => false]]);
        $idBefore = session()->getId();

        $this->makeAuthService()->set2FAVerified();

        $this->assertNotSame($idBefore, session()->getId());
        $this->assertTrue(session('userdata.twoFAVerified'));
    }

    public function test_logout_destroys_the_session(): void
    {
        session()->start();
        session(['userdata' => ['id' => 5], 'somePluginState' => 'secret']);
        $idBefore = session()->getId();
        $tokenBefore = session()->token();

        $this->makeAuthService()->logout();

        $this->assertNotSame($idBefore, session()->getId());
        $this->assertNotSame($tokenBefore, session()->token());
        $this->assertFalse(session()->has('userdata'));
        $this->assertFalse(session()->has('somePluginState'));
    }
}
