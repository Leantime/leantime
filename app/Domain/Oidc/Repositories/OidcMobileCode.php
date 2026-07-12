<?php

namespace Leantime\Domain\Oidc\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use Leantime\Core\Db\Db;

/**
 * Short-lived, single-use codes for the mobile SSO bridge.
 *
 * The OIDC callback (mobile-origin branch) mints one of these AFTER the user is
 * authenticated and hands it to the app via the app-scheme redirect. The app
 * then POSTs it to /oidc/mobile/exchange to receive a bearer token. Passing a
 * code (not the token) through the redirect URL is what keeps a scheme-hijacking
 * app from lifting a usable token — it would have to win the HTTPS exchange race
 * for a <=60s, single-use code.
 *
 * Only the sha256 hash of the code is stored (same discipline as zp_access_tokens).
 */
class OidcMobileCode
{
    private ConnectionInterface $db;

    public function __construct(Db $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * Mint a code bound to a user id. Returns the RAW code (unrecoverable once
     * returned — only its hash is persisted).
     */
    public function createCode(int $userId, int $ttlSeconds = 60): string
    {
        // Opportunistically sweep expired rows so the table stays small.
        $this->db->table('zp_oidc_mobile_codes')
            ->where('expires_at', '<', now())
            ->delete();

        $code = Str::random(64);

        $this->db->table('zp_oidc_mobile_codes')->insert([
            'code' => hash('sha256', $code),
            'tokenable_id' => $userId,
            'expires_at' => now()->addSeconds($ttlSeconds),
            'created_at' => now(),
        ]);

        return $code;
    }

    /**
     * Validate + consume a code. Returns the bound user id, or null if the code
     * is unknown/expired. Single-use: the matching row is deleted on success, so
     * a replayed code fails.
     */
    public function consumeCode(string $rawCode): ?int
    {
        $hashed = hash('sha256', $rawCode);

        $row = $this->db->table('zp_oidc_mobile_codes')
            ->where('code', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        if (! $row) {
            return null;
        }

        $this->db->table('zp_oidc_mobile_codes')
            ->where('id', $row->id)
            ->delete();

        return (int) $row->tokenable_id;
    }
}
