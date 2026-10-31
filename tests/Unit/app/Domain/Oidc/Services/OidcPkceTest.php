<?php

namespace Tests\Unit\app\Domain\Oidc\Services;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Oidc\Services\Oidc;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * PKCE (RFC 7636, S256) on the web login (#3971). Providers that require it, like Authelia with
 * `require_pkce: true`, refuse the authorization request without a code_challenge.
 */
class OidcPkceTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function oidc(): Oidc
    {
        $settings = [
            'oidcProviderUrl' => 'https://idp.example.com',
            'oidcClientId' => 'leantime-client',
            'oidcAuthUrl' => 'https://idp.example.com/authorize',
            'oidcTokenUrl' => 'https://idp.example.com/token',
            'oidcJwksUrl' => 'https://idp.example.com/jwks',
        ];

        $config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $settings[$key] ?? $default,
        ]);

        return new Oidc(
            $config,
            $this->make(Language::class, ['__' => fn (string $index) => $index]),
            $this->make(AuthService::class),
            $this->make(UserRepository::class),
        );
    }

    public function test_login_url_carries_an_s256_challenge_for_the_stored_verifier(): void
    {
        $url = $this->oidc()->buildLoginUrl();
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $verifier = session('oidc.codeVerifier');
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\-_]{43,128}$/', $verifier);

        $expectedChallenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $this->assertSame('S256', $query['code_challenge_method']);
        $this->assertSame($expectedChallenge, $query['code_challenge']);
    }

    public function test_token_request_sends_the_verifier_once(): void
    {
        Http::fake([
            'https://idp.example.com/token' => Http::response(['token_type' => 'Bearer'], 200),
        ]);

        $oidc = $this->oidc();
        $oidc->buildLoginUrl();
        $verifier = session('oidc.codeVerifier');
        $state = session('oidc.state');

        try {
            $oidc->callback('the-code', $state);
            $this->fail('A token response without tokens must be rejected');
        } catch (\RuntimeException $e) {
            $this->assertSame('oidc.error.unsupportedToken', $e->getMessage());
        }

        Http::assertSent(fn (Request $request) => $request->url() === 'https://idp.example.com/token'
            && $request['code'] === 'the-code'
            && $request['code_verifier'] === $verifier);

        // One-time use, like the state and nonce.
        $this->assertNull(session('oidc.codeVerifier'));
    }
}
