<?php

namespace Leantime\Core\Auth\OAuth;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;
use Laravel\Passport\Bridge\AuthCodeRepository as PassportAuthCodeRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository as PassportRefreshTokenRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;
use Leantime\Core\Configuration\Environment;

/**
 * OAuth 2.1 authorization server for MCP clients (Claude, ChatGPT, …), built on Laravel Passport.
 *
 * Passport issues and refreshes tokens; Leantime keeps the parts that are specific to it:
 *  - its own routes and controllers (Domain/Oauth): login + 2FA through AuthCheck, our consent
 *    screen, RFC 8707 `resource` and RFC 9207 `iss` handling,
 *  - signing keys that need no setup ({@see OAuthKeys}),
 *  - token validation on protected endpoints ({@see OAuthBearerAuthenticator}),
 *  - tokens tied to the user's password and status ({@see OAuthTokenStore}).
 *
 * Passport's provider is package-discovered, so this always runs to keep Passport's own routes
 * off, even when LEAN_OAUTH_ENABLED is false.
 */
class OAuthServiceProvider extends ServiceProvider
{
    /**
     * Access tokens are short-lived; clients refresh them silently.
     */
    private const ACCESS_TOKEN_TTL_HOURS = 1;

    /**
     * Refresh tokens rotate on every use; an app unused for this long has to reconnect.
     */
    private const REFRESH_TOKEN_TTL_DAYS = 30;

    /**
     * Configure Passport. Runs before any provider boots, which is when Passport registers routes.
     */
    public function register(): void
    {
        Passport::ignoreRoutes();
        Passport::$deviceCodeGrantEnabled = false;
        Passport::$passwordGrantEnabled = false;
        Passport::$implicitGrantEnabled = false;

        Passport::useClientModel(Models\Client::class);
        Passport::useTokenModel(Models\Token::class);
        Passport::useAuthCodeModel(Models\AuthCode::class);
        Passport::useRefreshTokenModel(Models\RefreshToken::class);

        Passport::tokensExpireIn(CarbonInterval::hours(self::ACCESS_TOKEN_TTL_HOURS));
        Passport::refreshTokensExpireIn(CarbonInterval::days(self::REFRESH_TOKEN_TTL_DAYS));
        Passport::tokensCan(ProtectedResources::scopes());
        // A client that asks for no scope gets the MCP scope, so its tokens work on /mcp.
        Passport::setDefaultScope(array_keys(ProtectedResources::scopes()));

        $this->app->singleton(OAuthKeys::class);
        $this->app->singleton(OAuthTokenStore::class);

        $this->app->bind(PassportAccessTokenRepository::class, Bridge\AccessTokenRepository::class);
        $this->app->bind(PassportAuthCodeRepository::class, Bridge\AuthCodeRepository::class);
        $this->app->bind(PassportRefreshTokenRepository::class, Bridge\RefreshTokenRepository::class);

        // Hand Passport the signing keys only when its servers are actually built, so requests
        // that never touch OAuth never read (or generate) a key.
        $loadSigningKeys = function ($abstract, $parameters, $app): void {
            $keys = $app->make(OAuthKeys::class)->keys();

            $app['config']->set('passport.private_key', $keys['private']);
            $app['config']->set('passport.public_key', $keys['public']);
        };

        // Encrypt code and refresh token payloads with a per-installation secret, not with the app
        // key (LEAN_SESSION_PASSWORD has a public default). See OAuthKeys::tokenEncryptionKey().
        Passport::encryptTokensUsing(fn (): string => $this->app->make(OAuthKeys::class)->tokenEncryptionKey());

        $this->app->beforeResolving(AuthorizationServer::class, $loadSigningKeys);
        $this->app->beforeResolving(ResourceServer::class, $loadSigningKeys);
    }

    /**
     * Whether OAuth sign-in is switched on for this installation (LEAN_OAUTH_ENABLED).
     */
    public static function enabled(): bool
    {
        return (bool) app(Environment::class)->get('oauthEnabled', true);
    }
}
