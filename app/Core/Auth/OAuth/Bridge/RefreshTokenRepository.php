<?php

namespace Leantime\Core\Auth\OAuth\Bridge;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Passport\Bridge\RefreshTokenRepository as PassportRefreshTokenRepository;
use Leantime\Core\Auth\OAuth\OAuthTokenStore;

/**
 * Passport's refresh token repository, plus: a refresh token counts as revoked once the user it
 * was issued to is deactivated or has changed their password, so the app can't mint new access
 * tokens after that (see {@see OAuthTokenStore}).
 */
class RefreshTokenRepository extends PassportRefreshTokenRepository
{
    public function __construct(
        Dispatcher $events,
        private OAuthTokenStore $tokenStore,
    ) {
        parent::__construct($events);
    }

    /**
     * Revoked when Passport says so, or when the token no longer stands for its user.
     */
    public function isRefreshTokenRevoked(string $tokenId): bool
    {
        return parent::isRefreshTokenRevoked($tokenId)
            || ! $this->tokenStore->refreshTokenIsBoundToUser($tokenId);
    }
}
