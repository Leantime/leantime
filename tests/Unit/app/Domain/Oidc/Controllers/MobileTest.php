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
use Leantime\Domain\Auth\Services\SecondFactor;
use Leantime\Domain\Oidc\Controllers\Mobile;
use Leantime\Domain\Oidc\Services\OidcMobileCode;
use Leantime\Domain\Plugins\Services\Plugins;
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

    private SecondFactor $secondFactor;

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
        // Stubbed so no real TOTP codes are needed: the contract under test is
        // what the exchange does with "right" and "wrong", not the arithmetic.
        $this->secondFactor = $this->createMock(SecondFactor::class);
        $this->app->instance(SecondFactor::class, $this->secondFactor);

        // Mobile SSO is gated on AdvancedAuth (see Mobile::exchange). Mock the
        // plugin as installed so these exchange-contract tests run past the gate;
        // the gate itself is verified live e2e (AdvancedAuth off -> 404).
        $plugins = $this->createMock(Plugins::class);
        $plugins->method('isEnabled')->willReturn(true);
        $this->app->instance(Plugins::class, $plugins);

        // Back the RateLimiter facade with a fresh in-memory store so throttle
        // state is deterministic and isolated per test.
        \Illuminate\Support\Facades\Facade::setFacadeApplication($this->app);
        $this->app->instance(
            \Illuminate\Cache\RateLimiter::class,
            new \Illuminate\Cache\RateLimiter(
                new \Illuminate\Cache\Repository(new \Illuminate\Cache\ArrayStore)
            )
        );
    }

    private function makeController(string $method = 'POST', array $body = []): Mobile
    {
        // IncomingRequest inherits getMethod() from Symfony's Request, where it
        // reads from the request's internal server bag. Building a real instance
        // is simpler and more accurate than mocking through the inheritance chain.
        // $body populates the POST (request) bag — what the controller reads via
        // ->post(); a query string on the URL is deliberately NOT read.
        $request = IncomingRequest::create('/oidc/mobile/exchange', $method, $body);
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

        $controller = $this->makeController('GET', ['code' => 'x', 'code_verifier' => 'y']);
        $response = $controller->exchange([]);

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

        $controller = $this->makeController('POST', ['code' => 'bad', 'code_verifier' => 'v']);
        $response = $controller->exchange([]);

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

        $controller = $this->makeController('POST', ['code' => 'good', 'code_verifier' => 'wrong-verifier']);
        $response = $controller->exchange([]);

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

        $controller = $this->makeController('POST', ['code' => 'good', 'code_verifier' => $verifier]);
        $response = $controller->exchange([]);

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
            'password' => 'SHOULD_NOT_APPEAR', 'twoFAEnabled' => 0,
        ]);
        // Code consumed exactly once, AFTER all validation; returns true (this
        // caller won the single-use race), so minting proceeds.
        $this->codes->expects($this->once())->method('consumeCode')->with('good')->willReturn(true);
        // Minted with full scope AND an explicit expiry (not non-expiring).
        $this->tokens->expects($this->once())->method('createToken')
            ->with(7, 'mobile-sso', ['*'], $this->isInstanceOf(\DateTimeInterface::class))
            ->willReturn(['token' => 'the-token']);

        $controller = $this->makeController('POST', ['code' => 'good', 'code_verifier' => $verifier]);
        $response = $controller->exchange([]);

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->bodyOf($response);
        $this->assertSame('the-token', $body['token']);
        // Only safe identity fields — never password / 2FA state.
        $this->assertSame(['id', 'firstname', 'lastname', 'username'], array_keys($body['user']));
    }

    public function test_secrets_in_query_string_are_ignored(): void
    {
        // The code + verifier must come from the POST body, never the URL query
        // (URLs land in access logs). A ?code=... is not read, so this is a
        // missing_code — and the code store is never touched.
        $this->codes->expects($this->never())->method('peekCode');

        $request = IncomingRequest::create('/oidc/mobile/exchange?code=fromquery&code_verifier=v', 'POST');
        $this->app->instance(IncomingRequest::class, $request);
        $controller = new Mobile($request, $this->createMock(Template::class), $this->createMock(Language::class));
        $response = $controller->exchange([]);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('missing_code', $this->bodyOf($response)['error']);
    }

    public function test_exchange_is_rate_limited_per_ip(): void
    {
        // Once the per-IP cap is hit, further attempts are refused with 429
        // BEFORE the code store is consulted — throttling code/verifier probing.
        $this->codes->expects($this->never())->method('peekCode');

        for ($i = 0; $i < 10; $i++) {
            \Illuminate\Support\Facades\RateLimiter::hit('oidc.mobile.exchange:127.0.0.1', 60);
        }

        $controller = $this->makeController('POST', ['code' => 'x', 'code_verifier' => 'y']);
        $response = $controller->exchange([]);

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame('too_many_requests', $this->bodyOf($response)['error']);
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    public function test_lost_consume_race_does_not_mint(): void
    {
        // Two concurrent exchanges both peek the same valid code; the one whose
        // atomic consumeCode() returns false (the other burned it first) must
        // NOT mint a second token from a single-use code.
        $verifier = 'testverifier';
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        $this->codes->method('peekCode')->willReturn(['userId' => 7, 'challenge' => $challenge]);
        $this->userRepo->method('getUser')->with(7)->willReturn([
            'id' => 7, 'firstname' => 'A', 'lastname' => 'B', 'username' => 'a@b',
        ]);
        $this->codes->method('consumeCode')->with('good')->willReturn(false);
        $this->tokens->expects($this->never())->method('createToken');

        $controller = $this->makeController('POST', ['code' => 'good', 'code_verifier' => $verifier]);
        $response = $controller->exchange([]);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('invalid_code', $this->bodyOf($response)['error']);
    }

    // ---------------------------------------------------------------------
    // Second factor: a 2FA account must present its TOTP code at the exchange,
    // the one place the bearer token is minted. Same keys/field as the
    // AdvancedAuth password endpoint (two_factor_required / two_factor_invalid,
    // POST twofaCode).
    // ---------------------------------------------------------------------

    private const VERIFIER = 'testverifier';

    /** Wires a PKCE-valid code 'good' for user 7 with 2FA on (secret 'SEED'). */
    private function twoFactorUser(int $failuresSoFar = 0): void
    {
        $challenge = rtrim(strtr(base64_encode(hash('sha256', self::VERIFIER, true)), '+/', '-_'), '=');
        $this->codes->method('peekCode')->with('good')->willReturn([
            'userId' => 7, 'challenge' => $challenge, 'secondFactorFailures' => $failuresSoFar,
        ]);
        $this->userRepo->method('getUser')->with(7)->willReturn([
            'id' => 7, 'firstname' => 'A', 'lastname' => 'B', 'username' => 'a@b',
            'twoFAEnabled' => 1, 'twoFASecret' => 'SEED',
        ]);
    }

    public function test_two_factor_account_without_a_code_is_asked_for_one_and_keeps_its_code(): void
    {
        $this->twoFactorUser();
        $this->codes->expects($this->once())->method('extendForSecondFactor')->with('good')->willReturn(true);
        $this->codes->expects($this->never())->method('consumeCode');
        $this->secondFactor->expects($this->never())->method('verify');
        $this->tokens->expects($this->never())->method('createToken');

        $response = $this->makeController('POST', ['code' => 'good', 'code_verifier' => self::VERIFIER])->exchange([]);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('two_factor_required', $this->bodyOf($response)['error']);
    }

    public function test_two_factor_required_is_only_answered_after_pkce(): void
    {
        // A scheme-hijacker holding the code but not the verifier learns nothing
        // about 2FA and cannot extend the code's life.
        $this->twoFactorUser();
        $this->codes->expects($this->never())->method('extendForSecondFactor');

        $response = $this->makeController('POST', ['code' => 'good', 'code_verifier' => 'wrong'])->exchange([]);

        $this->assertSame('invalid_verifier', $this->bodyOf($response)['error']);
    }

    public function test_wrong_second_factor_code_is_counted_and_the_code_survives(): void
    {
        $this->twoFactorUser();
        $this->secondFactor->expects($this->once())->method('verify')->with('SEED', '000000')->willReturn(false);
        $this->codes->expects($this->once())->method('recordSecondFactorFailure')->with('good')->willReturn(1);
        $this->codes->expects($this->never())->method('consumeCode');
        $this->tokens->expects($this->never())->method('createToken');

        $response = $this->makeController('POST', ['code' => 'good', 'code_verifier' => self::VERIFIER, 'twofaCode' => '000000'])->exchange([]);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('two_factor_invalid', $this->bodyOf($response)['error']);
    }

    public function test_fifth_wrong_second_factor_code_burns_the_code(): void
    {
        $this->twoFactorUser(4);
        $this->secondFactor->method('verify')->willReturn(false);
        $this->codes->method('recordSecondFactorFailure')->with('good')->willReturn(5);
        $this->codes->expects($this->once())->method('consumeCode')->with('good')->willReturn(true);
        $this->tokens->expects($this->never())->method('createToken');

        $response = $this->makeController('POST', ['code' => 'good', 'code_verifier' => self::VERIFIER, 'twofaCode' => '000000'])->exchange([]);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('invalid_code', $this->bodyOf($response)['error'], 'the app has to start the sign-in over');
    }

    public function test_right_second_factor_code_mints_the_token(): void
    {
        $this->twoFactorUser();
        $this->secondFactor->expects($this->once())->method('verify')->with('SEED', '123456')->willReturn(true);
        $this->codes->expects($this->never())->method('recordSecondFactorFailure');
        $this->codes->expects($this->once())->method('consumeCode')->with('good')->willReturn(true);
        $this->tokens->expects($this->once())->method('createToken')->willReturn(['token' => 'the-token']);

        $response = $this->makeController('POST', ['code' => 'good', 'code_verifier' => self::VERIFIER, 'twofaCode' => '123456'])->exchange([]);

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->bodyOf($response);
        $this->assertSame('the-token', $body['token']);
        $this->assertSame(['id', 'firstname', 'lastname', 'username'], array_keys($body['user']), 'never the 2FA seed');
    }

    public function test_second_factor_code_in_the_query_string_is_ignored(): void
    {
        $this->twoFactorUser();
        $this->secondFactor->expects($this->never())->method('verify');
        $this->codes->method('extendForSecondFactor')->willReturn(true);

        $request = IncomingRequest::create('/oidc/mobile/exchange?twofaCode=123456', 'POST', ['code' => 'good', 'code_verifier' => self::VERIFIER]);
        $this->app->instance(IncomingRequest::class, $request);
        $response = (new Mobile($request, $this->createMock(Template::class), $this->createMock(Language::class)))->exchange([]);

        $this->assertSame('two_factor_required', $this->bodyOf($response)['error']);
    }

    public function test_account_without_two_factor_ignores_a_stray_code(): void
    {
        $challenge = rtrim(strtr(base64_encode(hash('sha256', self::VERIFIER, true)), '+/', '-_'), '=');
        $this->codes->method('peekCode')->with('good')->willReturn(['userId' => 7, 'challenge' => $challenge]);
        $this->userRepo->method('getUser')->with(7)->willReturn(['id' => 7, 'firstname' => 'A', 'lastname' => 'B', 'username' => 'a@b', 'twoFAEnabled' => 0]);
        $this->secondFactor->expects($this->never())->method('verify');
        $this->codes->expects($this->never())->method('extendForSecondFactor');
        $this->codes->method('consumeCode')->willReturn(true);
        $this->tokens->method('createToken')->willReturn(['token' => 't']);

        $response = $this->makeController('POST', ['code' => 'good', 'code_verifier' => self::VERIFIER, 'twofaCode' => '999999'])->exchange([]);

        $this->assertSame(200, $response->getStatusCode());
    }
}
