<?php

namespace Leantime\Core\Auth\OAuth\Bridge;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use Leantime\Core\Auth\OAuth\OAuthTokenStore;

/**
 * Passport's access token repository, plus: every new token records the user's password
 * fingerprint, so a password change ends the connection (see {@see OAuthTokenStore}).
 */
class AccessTokenRepository extends PassportAccessTokenRepository
{
    public function __construct(
        Dispatcher $events,
        private OAuthTokenStore $tokenStore,
    ) {
        parent::__construct($events);
    }

    /**
     * Persist the token, then stamp it with the user's current password fingerprint.
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        parent::persistNewAccessToken($accessTokenEntity);

        $userId = $accessTokenEntity->getUserIdentifier();

        if ($userId !== null && ctype_digit($userId)) {
            $this->tokenStore->stampPasswordFingerprint($accessTokenEntity->getIdentifier(), (int) $userId);
        }
    }
}
