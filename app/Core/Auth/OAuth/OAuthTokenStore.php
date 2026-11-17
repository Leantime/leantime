<?php

namespace Leantime\Core\Auth\OAuth;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Leantime\Core\Auth\PasswordFingerprint;

/**
 * All reads and writes on the zp_oauth_* tables that Leantime does itself (Passport handles the
 * rows it creates while issuing tokens).
 *
 * Every OAuth access token is tied to the password the user had when it was issued, the same way
 * a web session is (see {@see PasswordFingerprint}). Changing the password, by any route, ends every
 * OAuth connection of that user without needing a hook at each place a password is written.
 */
class OAuthTokenStore
{
    /**
     * Revoked or expired rows are kept this long before purge() deletes them.
     */
    private const PURGE_AFTER_DAYS = 7;

    /**
     * A registered client that never got a token within this many hours is deleted by purge().
     */
    private const UNUSED_CLIENT_HOURS = 24;

    public function __construct(private DatabaseManager $db) {}

    private function connection(): ConnectionInterface
    {
        return $this->db->connection();
    }

    /**
     * The password fingerprint of an active user, or null when the user doesn't exist or isn't active.
     */
    public function passwordFingerprintOfActiveUser(int $userId): ?string
    {
        $user = $this->connection()->table('zp_user')
            ->where('id', $userId)
            ->first(['password', 'status']);

        if ($user === null || strtolower((string) $user->status) !== 'a') {
            return null;
        }

        return PasswordFingerprint::of($user->password);
    }

    /**
     * Record the user's current password fingerprint on a freshly issued access token.
     */
    public function stampPasswordFingerprint(string $accessTokenId, int $userId): void
    {
        $fingerprint = $this->passwordFingerprintOfActiveUser($userId);

        $this->connection()->table('zp_oauth_access_tokens')
            ->where('id', $accessTokenId)
            ->update(['password_fingerprint' => $fingerprint]);
    }

    /**
     * Whether an access token still stands for its user: the token and its client are not revoked,
     * the user is active, and the password hasn't changed since the token was issued.
     */
    public function accessTokenIsBoundToUser(string $accessTokenId, int $userId): bool
    {
        $token = $this->connection()->table('zp_oauth_access_tokens as tokens')
            ->join('zp_oauth_clients as clients', 'clients.id', '=', 'tokens.client_id')
            ->where('tokens.id', $accessTokenId)
            ->where('tokens.revoked', false)
            ->where('clients.revoked', false)
            ->first(['tokens.user_id', 'tokens.password_fingerprint']);

        if ($token === null || (int) $token->user_id !== $userId || empty($token->password_fingerprint)) {
            return false;
        }

        $currentFingerprint = $this->passwordFingerprintOfActiveUser($userId);

        return $currentFingerprint !== null && hash_equals($token->password_fingerprint, $currentFingerprint);
    }

    /**
     * Record the user's password fingerprint on an authorization code when the user approves.
     */
    public function stampAuthCodePasswordFingerprint(string $authCodeId, int $userId): void
    {
        $fingerprint = $this->passwordFingerprintOfActiveUser($userId);

        $this->connection()->table('zp_oauth_auth_codes')
            ->where('id', $authCodeId)
            ->update(['password_fingerprint' => $fingerprint]);
    }

    /**
     * Mark a client as approved by a user at least once, so the unused-client cleanup never
     * deletes it (its tokens may all be purged later, but its client id is still in use).
     */
    public function markClientAuthorized(string $clientId): void
    {
        $this->connection()->table('zp_oauth_clients')
            ->where('id', $clientId)
            ->whereNull('first_authorized_at')
            ->update(['first_authorized_at' => CarbonImmutable::now('UTC')->format('Y-m-d H:i:s')]);
    }

    /**
     * Whether an authorization code may still be redeemed: its client is not revoked, the user is
     * active, and the password hasn't changed since the user approved.
     */
    public function authCodeIsBoundToUser(string $authCodeId): bool
    {
        $code = $this->connection()->table('zp_oauth_auth_codes as codes')
            ->join('zp_oauth_clients as clients', 'clients.id', '=', 'codes.client_id')
            ->where('codes.id', $authCodeId)
            ->where('clients.revoked', false)
            ->first(['codes.user_id', 'codes.password_fingerprint']);

        if ($code === null || empty($code->user_id) || empty($code->password_fingerprint)) {
            return false;
        }

        $currentFingerprint = $this->passwordFingerprintOfActiveUser((int) $code->user_id);

        return $currentFingerprint !== null && hash_equals($code->password_fingerprint, $currentFingerprint);
    }

    /**
     * Run $work while holding a row lock on one client, inside a transaction. Token issuance and
     * Disconnect for the same client run one after the other, on every node: two redemptions of
     * the same code or refresh token can't both succeed, and a refresh that started before a
     * Disconnect can't leave a fresh token pair behind it.
     *
     * @template TResult
     *
     * @param  callable(): TResult  $work
     * @return TResult
     */
    public function withClientLock(string $clientId, callable $work): mixed
    {
        return $this->connection()->transaction(function () use ($clientId, $work) {
            $this->connection()->table('zp_oauth_clients')
                ->where('id', $clientId)
                ->lockForUpdate()
                ->value('id');

            return $work();
        });
    }

    /**
     * Whether a refresh token may still be exchanged: the access token it belongs to must still be
     * bound to its user (password unchanged, user active, client not revoked). The access token
     * itself may have expired; that is what refreshing is for.
     */
    public function refreshTokenIsBoundToUser(string $refreshTokenId): bool
    {
        $token = $this->connection()->table('zp_oauth_refresh_tokens as refresh')
            ->join('zp_oauth_access_tokens as tokens', 'tokens.id', '=', 'refresh.access_token_id')
            ->join('zp_oauth_clients as clients', 'clients.id', '=', 'tokens.client_id')
            ->where('refresh.id', $refreshTokenId)
            ->where('clients.revoked', false)
            ->first(['tokens.user_id', 'tokens.password_fingerprint']);

        if ($token === null || empty($token->user_id) || empty($token->password_fingerprint)) {
            return false;
        }

        $currentFingerprint = $this->passwordFingerprintOfActiveUser((int) $token->user_id);

        return $currentFingerprint !== null && hash_equals($token->password_fingerprint, $currentFingerprint);
    }

    /**
     * The apps a user is connected to: one row per client that still holds a usable access or
     * refresh token for the user. "Last active" is when the app last got a token; apps refresh
     * hourly while in use, so it is accurate to about an hour.
     *
     * @return array<int, array{clientId: string, name: string, redirectHost: string, lastActiveAt: string}>
     */
    public function connectedApps(int $userId): array
    {
        $now = CarbonImmutable::now('UTC')->format('Y-m-d H:i:s');

        $rows = $this->connection()->table('zp_oauth_access_tokens as tokens')
            ->join('zp_oauth_clients as clients', 'clients.id', '=', 'tokens.client_id')
            ->leftJoin('zp_oauth_refresh_tokens as refresh', 'refresh.access_token_id', '=', 'tokens.id')
            ->where('tokens.user_id', $userId)
            ->where('clients.revoked', false)
            ->where(function ($query) use ($now) {
                $query->where(function ($accessTokenUsable) use ($now) {
                    $accessTokenUsable->where('tokens.revoked', false)->where('tokens.expires_at', '>', $now);
                })->orWhere(function ($refreshTokenUsable) use ($now) {
                    $refreshTokenUsable->where('refresh.revoked', false)->where('refresh.expires_at', '>', $now);
                });
            })
            ->groupBy('clients.id', 'clients.name', 'clients.redirect_uris')
            ->orderBy('clients.name')
            ->get([
                'clients.id',
                'clients.name',
                'clients.redirect_uris',
                $this->connection()->raw('MAX(tokens.created_at) as last_active_at'),
            ]);

        $connectedApps = [];

        foreach ($rows as $row) {
            $redirectUris = json_decode((string) $row->redirect_uris, true);
            $firstRedirectUri = is_array($redirectUris) ? (string) ($redirectUris[0] ?? '') : '';

            $connectedApps[] = [
                'clientId' => (string) $row->id,
                'name' => (string) $row->name,
                'redirectHost' => (string) (parse_url($firstRedirectUri, PHP_URL_HOST) ?: $firstRedirectUri),
                'lastActiveAt' => (string) $row->last_active_at,
            ];
        }

        return $connectedApps;
    }

    /**
     * Revoke every authorization code, access and refresh token one client holds for one user.
     *
     * @return int Number of access tokens revoked.
     */
    public function revokeForUserAndClient(int $userId, string $clientId): int
    {
        $this->connection()->table('zp_oauth_auth_codes')
            ->where('user_id', $userId)
            ->where('client_id', $clientId)
            ->update(['revoked' => true]);

        $tokenIds = $this->connection()->table('zp_oauth_access_tokens')
            ->where('user_id', $userId)
            ->where('client_id', $clientId)
            ->pluck('id')
            ->all();

        return $this->revokeAccessTokens($tokenIds);
    }

    /**
     * Revoke every OAuth code and token of a user.
     *
     * @return int Number of access tokens revoked.
     */
    public function revokeAllForUser(int $userId): int
    {
        $this->connection()->table('zp_oauth_auth_codes')
            ->where('user_id', $userId)
            ->update(['revoked' => true]);

        $tokenIds = $this->connection()->table('zp_oauth_access_tokens')
            ->where('user_id', $userId)
            ->pluck('id')
            ->all();

        return $this->revokeAccessTokens($tokenIds);
    }

    /**
     * Revoke every OAuth token on this instance (used when the signing key is rotated).
     *
     * @return int Number of access tokens revoked.
     */
    public function revokeAll(): int
    {
        $this->connection()->table('zp_oauth_refresh_tokens')->update(['revoked' => true]);
        $this->connection()->table('zp_oauth_auth_codes')->update(['revoked' => true]);

        return $this->connection()->table('zp_oauth_access_tokens')
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }

    /**
     * Number of access tokens that are neither revoked nor expired.
     */
    public function countActiveAccessTokens(): int
    {
        return $this->connection()->table('zp_oauth_access_tokens')
            ->where('revoked', false)
            ->where('expires_at', '>', CarbonImmutable::now('UTC')->format('Y-m-d H:i:s'))
            ->count();
    }

    /**
     * Delete revoked/expired tokens and codes older than a week, and registered clients no user ever
     * approved (dynamic registration is unauthenticated, so unused rows pile up).
     *
     * @return array{accessTokens: int, refreshTokens: int, authCodes: int, clients: int}
     */
    public function purge(): array
    {
        $now = CarbonImmutable::now('UTC');
        $purgeBefore = $now->subDays(self::PURGE_AFTER_DAYS)->format('Y-m-d H:i:s');
        $unusedClientBefore = $now->subHours(self::UNUSED_CLIENT_HOURS)->format('Y-m-d H:i:s');

        // Revoked refresh tokens can never be used again; expired ones are kept a week for support.
        $deadRefreshTokens = $this->connection()->table('zp_oauth_refresh_tokens')
            ->where(fn ($query) => $query->where('revoked', true)->orWhere('expires_at', '<', $purgeBefore))
            ->delete();

        // An access token row is deleted once it is dead, a week old, and no refresh token
        // points at it any more.
        $deadAccessTokens = $this->connection()->table('zp_oauth_access_tokens as tokens')
            ->where(fn ($query) => $query->where('tokens.revoked', true)->orWhere('tokens.expires_at', '<', $purgeBefore))
            ->where('tokens.created_at', '<', $purgeBefore)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('zp_oauth_refresh_tokens as refresh')
                    ->whereColumn('refresh.access_token_id', 'tokens.id');
            })
            ->delete();

        $deadAuthCodes = $this->connection()->table('zp_oauth_auth_codes')
            ->where(fn ($query) => $query->where('revoked', true)->orWhere('expires_at', '<', $now->format('Y-m-d H:i:s')))
            ->delete();

        $unusedClients = $this->connection()->table('zp_oauth_clients')
            ->whereNull('first_authorized_at')
            ->where('created_at', '<', $unusedClientBefore)
            ->delete();

        return [
            'accessTokens' => $deadAccessTokens,
            'refreshTokens' => $deadRefreshTokens,
            'authCodes' => $deadAuthCodes,
            'clients' => $unusedClients,
        ];
    }

    /**
     * @param  array<int, string>  $accessTokenIds
     */
    private function revokeAccessTokens(array $accessTokenIds): int
    {
        if ($accessTokenIds === []) {
            return 0;
        }

        $this->connection()->table('zp_oauth_refresh_tokens')
            ->whereIn('access_token_id', $accessTokenIds)
            ->update(['revoked' => true]);

        return $this->connection()->table('zp_oauth_access_tokens')
            ->whereIn('id', $accessTokenIds)
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }
}
