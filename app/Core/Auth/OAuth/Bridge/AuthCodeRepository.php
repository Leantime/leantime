<?php

namespace Leantime\Core\Auth\OAuth\Bridge;

use Laravel\Passport\Bridge\AuthCodeRepository as PassportAuthCodeRepository;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use Leantime\Core\Auth\OAuth\OAuthTokenStore;

/**
 * Passport's authorization code repository, plus: the client is marked as approved (so cleanup keeps
 * it), and a code records the user's password fingerprint when the user approves, and counts as revoked once the password changed (or the user was
 * deactivated) before the app redeemed it. Otherwise a code approved before a password reset
 * would mint tokens stamped with the new password.
 */
class AuthCodeRepository extends PassportAuthCodeRepository
{
    public function __construct(private OAuthTokenStore $tokenStore) {}

    /**
     * Persist the code, then stamp it with the user's current password fingerprint.
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        parent::persistNewAuthCode($authCodeEntity);

        $userId = $authCodeEntity->getUserIdentifier();

        if ($userId !== null && ctype_digit($userId)) {
            $this->tokenStore->stampAuthCodePasswordFingerprint($authCodeEntity->getIdentifier(), (int) $userId);
        }

        $this->tokenStore->markClientAuthorized($authCodeEntity->getClient()->getIdentifier());
    }

    /**
     * Revoked when Passport says so, or when the code no longer stands for its user.
     */
    public function isAuthCodeRevoked(string $codeId): bool
    {
        return parent::isAuthCodeRevoked($codeId)
            || ! $this->tokenStore->authCodeIsBoundToUser($codeId);
    }
}
