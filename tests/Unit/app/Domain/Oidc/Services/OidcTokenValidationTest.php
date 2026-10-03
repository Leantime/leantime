<?php

namespace Tests\Unit\app\Domain\Oidc\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Oidc\Services\Oidc;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * An id_token is only accepted when it is signed by the provider AND issued to this client for
 * this login attempt (aud / azp / exp / nbf / iat / nonce). Provider-flagged unverified emails
 * can't log in, and TLS verification towards the provider is on unless explicitly disabled.
 */
class OidcTokenValidationTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const ISSUER = 'https://idp.example.com';

    private const CLIENT_ID = 'leantime-client';

    private \OpenSSLAsymmetricKey $privateKey;

    private string $publicKeyPem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->privateKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $this->publicKeyPem = openssl_pkey_get_details($this->privateKey)['key'];
    }

    private function oidc(array $overrides = []): Oidc
    {
        $settings = $overrides + [
            'oidcProviderUrl' => self::ISSUER,
            'oidcClientId' => self::CLIENT_ID,
            'oidcCertificateString' => $this->publicKeyPem,
            'oidcFieldEmail' => 'email',
        ];

        $config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $settings[$key] ?? $default,
        ]);

        return new Oidc(
            $config,
            $this->make(Language::class, ['__' => fn (string $index) => $index.' %s']),
            $this->make(AuthService::class),
            $this->make(UserRepository::class),
        );
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function token(array $claimOverrides = [], ?\OpenSSLAsymmetricKey $signWith = null): string
    {
        $claims = array_merge([
            'iss' => self::ISSUER,
            'aud' => self::CLIENT_ID,
            'sub' => 'user-1',
            'email' => 'jane@example.com',
            'iat' => time(),
            'exp' => time() + 300,
            'nonce' => 'expected-nonce',
        ], $claimOverrides);

        $claims = array_filter($claims, fn ($value) => $value !== null);

        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'kid' => 'k1']));
        $payload = $this->base64Url(json_encode($claims));

        openssl_sign($header.'.'.$payload, $signature, $signWith ?? $this->privateKey, OPENSSL_ALGO_SHA256);

        return $header.'.'.$payload.'.'.$this->base64Url($signature);
    }

    private function decode(Oidc $oidc, string $jwt, string $expectedNonce = 'expected-nonce'): ?array
    {
        return (fn () => $this->decodeJWT($jwt, $expectedNonce))->call($oidc);
    }

    public function test_valid_token_is_accepted(): void
    {
        $claims = $this->decode($this->oidc(), $this->token());

        $this->assertSame('jane@example.com', $claims['email']);
    }

    public function test_audience_list_containing_client_is_accepted(): void
    {
        $claims = $this->decode($this->oidc(), $this->token(['aud' => ['other-api', self::CLIENT_ID], 'azp' => self::CLIENT_ID]));

        $this->assertSame('jane@example.com', $claims['email']);
    }

    public function test_token_with_bad_signature_is_rejected(): void
    {
        $otherKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);

        $this->assertNull($this->decode($this->oidc(), $this->token([], $otherKey)));
    }

    public static function invalidClaimsProvider(): array
    {
        return [
            'token for another client' => [['aud' => 'some-other-client'], 'expected-nonce'],
            'azp of another client' => [['aud' => [self::CLIENT_ID, 'x'], 'azp' => 'x'], 'expected-nonce'],
            'several audiences without azp' => [['aud' => [self::CLIENT_ID, 'x']], 'expected-nonce'],
            'single audience with foreign azp' => [['azp' => 'x'], 'expected-nonce'],
            'expired token' => [['exp' => time() - 3600], 'expected-nonce'],
            'missing expiry' => [['exp' => null], 'expected-nonce'],
            'not yet valid' => [['nbf' => time() + 3600], 'expected-nonce'],
            'issued in the future' => [['iat' => time() + 3600], 'expected-nonce'],
            'missing issue time' => [['iat' => null], 'expected-nonce'],
            'non-numeric issue time' => [['iat' => 'yesterday'], 'expected-nonce'],
            'nonce from another login' => [['nonce' => 'replayed'], 'expected-nonce'],
            'missing nonce' => [['nonce' => null], 'expected-nonce'],
            'no nonce stored in session' => [[], ''],
        ];
    }

    /**
     * @dataProvider invalidClaimsProvider
     */
    public function test_invalid_claims_are_rejected(array $claims, string $expectedNonce): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('oidc.error.invalidClaims');

        $this->decode($this->oidc(), $this->token($claims), $expectedNonce);
    }

    public function test_small_clock_skew_is_tolerated(): void
    {
        $claims = $this->decode($this->oidc(), $this->token(['exp' => time() - 30, 'iat' => time() + 30]));

        $this->assertIsArray($claims);
    }

    private function emailAccepted(Oidc $oidc, array $userInfo): bool
    {
        return (fn () => $this->emailVerificationAccepted($userInfo))->call($oidc);
    }

    public function test_unverified_email_is_rejected_by_default(): void
    {
        $oidc = $this->oidc();

        $this->assertFalse($this->emailAccepted($oidc, ['email' => 'a@b.c', 'email_verified' => false]));
        $this->assertFalse($this->emailAccepted($oidc, ['email' => 'a@b.c', 'email_verified' => 'false']));
        $this->assertTrue($this->emailAccepted($oidc, ['email' => 'a@b.c', 'email_verified' => true]));
        $this->assertTrue($this->emailAccepted($oidc, ['email' => 'a@b.c', 'email_verified' => 'true']));
    }

    public function test_providers_without_the_claim_are_accepted(): void
    {
        $this->assertTrue($this->emailAccepted($this->oidc(), ['email' => 'a@b.c']));
    }

    public function test_email_verification_check_can_be_disabled(): void
    {
        $oidc = $this->oidc(['oidcRequireVerifiedEmail' => false]);

        $this->assertTrue($this->emailAccepted($oidc, ['email' => 'a@b.c', 'email_verified' => false]));
    }

    public function test_provider_requests_verify_tls_unless_opted_out(): void
    {
        $verifying = (fn () => $this->providerHttp())->call($this->oidc())->getOptions();
        $this->assertTrue($verifying['verify'] ?? true);

        $optedOut = (fn () => $this->providerHttp())->call($this->oidc(['oidcSkipTlsVerify' => true]))->getOptions();
        $this->assertFalse($optedOut['verify']);
    }

    public function test_login_url_carries_a_nonce_that_is_stored_in_the_session(): void
    {
        $oidc = $this->oidc([
            'oidcAuthUrl' => 'https://idp.example.com/authorize',
            'oidcTokenUrl' => 'https://idp.example.com/token',
            'oidcJwksUrl' => 'https://idp.example.com/jwks',
        ]);

        $url = $oidc->buildLoginUrl();
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $this->assertNotEmpty($query['nonce']);
        $this->assertSame(session('oidc.nonce'), $query['nonce']);
        $this->assertNotSame($query['state'], $query['nonce']);
    }
}
