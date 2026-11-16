<?php

namespace Leantime\Core\Auth\OAuth;

use GuzzleHttp\Psr7\ServerRequest;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;

/**
 * Validates an OAuth access token presented as a Bearer token on a protected endpoint.
 *
 * Passport's own TokenGuard is not used on purpose: it reads the Authorization header the way
 * Apache hides from PHP here (see ApiRequest::getBearerToken()), and it needs an Eloquent user
 * model. This does the same checks with league's ResourceServer directly and adds Leantime's:
 *  - signature, expiry and "not revoked" (ResourceServer + Passport's token table),
 *  - the token carries the scope the endpoint requires (MCP tokens can't be used elsewhere),
 *  - the client is not revoked, the user is active, and the password hasn't changed since issue.
 */
class OAuthBearerAuthenticator
{
    public function __construct(
        private ResourceServer $resourceServer,
        private OAuthTokenStore $tokenStore,
    ) {}

    /**
     * Whether a Bearer value looks like one of our JWT access tokens (three base64url parts, JSON
     * header). Personal access tokens are random strings and never match.
     */
    public static function looksLikeAccessToken(string $bearerToken): bool
    {
        return str_starts_with($bearerToken, 'eyJ') && substr_count($bearerToken, '.') === 2;
    }

    /**
     * The id of the user the token stands for, or null when the token isn't valid for this endpoint.
     *
     * @param  string  $requiredScope  Scope the endpoint requires ({@see ProtectedResources::scopeFor()}).
     */
    public function authenticate(string $bearerToken, string $requiredScope): ?int
    {
        $tokenRequest = new ServerRequest('GET', '/', ['Authorization' => 'Bearer '.$bearerToken]);

        try {
            $validatedRequest = $this->resourceServer->validateAuthenticatedRequest($tokenRequest);
        } catch (OAuthServerException $exception) {
            // Expired, revoked, forged or malformed: an ordinary client-side condition, so debug only.
            Log::debug('OAuth: rejected bearer token', ['reason' => $exception->getMessage()]);

            return null;
        }

        $accessTokenId = (string) $validatedRequest->getAttribute('oauth_access_token_id');
        $userId = (string) $validatedRequest->getAttribute('oauth_user_id');
        $scopes = (array) $validatedRequest->getAttribute('oauth_scopes', []);

        // Client-credentials tokens carry the client id as subject, never a user. Only numeric
        // ids can be users; anything else must not reach a user lookup (MySQL would cast it).
        if ($accessTokenId === '' || ! ctype_digit($userId)) {
            return null;
        }

        if (! in_array($requiredScope, $scopes, true)) {
            return null;
        }

        if (! $this->tokenStore->accessTokenIsBoundToUser($accessTokenId, (int) $userId)) {
            return null;
        }

        return (int) $userId;
    }
}
