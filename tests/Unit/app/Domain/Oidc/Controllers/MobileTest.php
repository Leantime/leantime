<?php

namespace Tests\Unit\app\Domain\Oidc\Controllers;

use Leantime\Core\Application;
use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Bootstrap\LoadConfig;
use Leantime\Core\Bootstrap\SetRequestForConsole;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Oidc\Controllers\Mobile;
use Leantime\Domain\Oidc\Services\OidcMobileCode;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * Unit tests for the mobile SSO exchange endpoint.
 *
 * These pin the security-critical contract: POST-only, PKCE-before-consume
 * (a bad verifier must NOT burn the code), and orphan-token prevention
 * (mint only after user existence is confirmed).
 */
class MobileTest extends \Unit\TestCase
{
    private OidcMobileCode $codes;

    private AccessTokenRepository $tokens;

    private UserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(APP_ROOT);
        $this->app->bootstrapWith([LoadConfig::class, SetRequestForConsole::class]);
        $this->app->boot();
        $this->app['view'] = $this->createMock(\Illuminate\View\Factory::class);
        $this->app['session'] = $this->createMock(\Illuminate\Session\SessionManager::class);
        $this->app->instance(PermissionEnforcer::class, $this->createMock(PermissionEnforcer::class));

        $this->codes = $this->createMock(OidcMobileCode::class);
        $this->tokens = $this->createMock(AccessTokenRepository::class);
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->app->instance(OidcMobileCode::class, $this->codes);
        $this->app->instance(AccessTokenRepository::class, $this->tokens);
        $this->app->instance(UserRepository::class, $this->userRepo);
    }

    private function makeController(string $method = 'POST'): Mobile
    {
        // IncomingRequest inherits getMethod() from Symfony's Request, where it
        // reads from the request's internal server bag. Building a real instance
        // is simpler and more accurate than mocking through the inheritance chain.
        $request = IncomingRequest::create('/oidc/mobile/exchange', $method);
        $this->app->instance(IncomingRequest::class, $request);

        return new Mobile($request, $this->createMock(Template::class), $this->createMock(Language::class));
    }

    private function bodyOf($response): array
    {
        return json_decode($response->getContent(), true);
    }

    public function test_get_is_rejected_with_405(): void
    {
        // Peek must never be called — the request is rejected before the code store is touched.
        $this->codes->expects($this->never())->method('peekCode');

        $controller = $this->makeController('GET');
        $response = $controller->exchange(['code' => 'x', 'code_verifier' => 'y']);

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('POST', $response->headers->get('Allow'));
        $this->assertSame('method_not_allowed', $this->bodyOf($response)['error']);
    }

    public function test_missing_code_returns_400(): void
    {
        $this->codes->expects($this->never())->method('peekCode');

        $controller = $this->makeController();
        $response = $controller->exchange([]);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('missing_code', $this->bodyOf($response)['error']);
    }

    public function test_unknown_code_returns_401_and_does_not_consume(): void
    {
        $this->codes->method('peekCode')->with('bad')->willReturn(null);
        // Nothing to consume for an unknown code — but assert it explicitly.
        $this->codes->expects($this->never())->method('consumeCode');

        $controller = $this->makeController();
        $response = $controller->exchange(['code' => 'bad', 'code_verifier' => 'v']);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('invalid_code', $this->bodyOf($response)['error']);
    }

    public function test_invalid_verifier_does_not_burn_the_code(): void
    {
        // The core DoS-protection contract: a wrong verifier from a scheme-
        // hijacker must not consume the code, so the legitimate app can still
        // exchange it.
        $this->codes->method('peekCode')->willReturn([
            'userId' => 42,
            'challenge' => 'somechallenge',
        ]);
        $this->codes->expects($this->never())->method('consumeCode');
        $this->tokens->expects($this->never())->method('createToken');

        $controller = $this->makeController();
        $response = $controller->exchange(['code' => 'good', 'code_verifier' => 'wrong-verifier']);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('invalid_verifier', $this->bodyOf($response)['error']);
    }

    public function test_missing_user_returns_401_without_minting(): void
    {
        // PKCE(S256) of the verifier 'testverifier' — used below to pass PKCE.
        $verifier = 'testverifier';
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        $this->codes->method('peekCode')->willReturn(['userId' => 99, 'challenge' => $challenge]);
        $this->userRepo->method('getUser')->with(99)->willReturn([]);
        $this->codes->expects($this->never())->method('consumeCode');
        $this->tokens->expects($this->never())->method('createToken');

        $controller = $this->makeController();
        $response = $controller->exchange(['code' => 'good', 'code_verifier' => $verifier]);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('invalid_user', $this->bodyOf($response)['error']);
    }

    public function test_valid_exchange_consumes_code_and_mints_token(): void
    {
        $verifier = 'testverifier';
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        $this->codes->method('peekCode')->with('good')->willReturn(['userId' => 7, 'challenge' => $challenge]);
        $this->userRepo->method('getUser')->with(7)->willReturn([
            'id' => 7, 'firstname' => 'A', 'lastname' => 'B', 'username' => 'a@b',
            'password' => 'SHOULD_NOT_APPEAR', 'twoFAEnabled' => 1,
        ]);
        // Code consumed exactly once, AFTER all validation.
        $this->codes->expects($this->once())->method('consumeCode')->with('good');
        $this->tokens->method('createToken')->willReturn(['token' => 'the-token']);

        $controller = $this->makeController();
        $response = $controller->exchange(['code' => 'good', 'code_verifier' => $verifier]);

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->bodyOf($response);
        $this->assertSame('the-token', $body['token']);
        // Only safe identity fields — never password / 2FA state.
        $this->assertSame(['id', 'firstname', 'lastname', 'username'], array_keys($body['user']));
    }
}
