<?php

namespace Leantime\Domain\Oauth\Services;

use Leantime\Core\Auth\OAuth\OAuthTokenStore;

/**
 * The apps (Claude, ChatGPT, …) a user connected to their Leantime account through OAuth.
 * Each user sees and disconnects only their own.
 */
class ConnectedApps
{
    public function __construct(private OAuthTokenStore $tokenStore) {}

    /**
     * The signed-in user's connected apps: one entry per app that can still act for them.
     *
     * @return array<int, array{clientId: string, name: string, redirectHost: string, lastActiveAt: string}>
     *
     * @api
     */
    public function getConnectedApps(): array
    {
        $userId = (int) session('userdata.id');

        if ($userId === 0) {
            return [];
        }

        return $this->tokenStore->connectedApps($userId);
    }

    /**
     * Disconnect one of the signed-in user's apps: every token it holds for the user is revoked
     * at once, so it has to sign in again to reconnect.
     *
     * @param  string  $clientId  The app's client id (from getConnectedApps).
     * @return bool True when the app held tokens for the user and they were revoked.
     *
     * @api
     */
    public function disconnectApp(string $clientId): bool
    {
        $userId = (int) session('userdata.id');

        if ($userId === 0 || trim($clientId) === '') {
            return false;
        }

        $revokedTokens = $this->tokenStore->withClientLock(
            $clientId,
            fn (): int => $this->tokenStore->revokeForUserAndClient($userId, $clientId)
        );

        return $revokedTokens > 0;
    }
}
