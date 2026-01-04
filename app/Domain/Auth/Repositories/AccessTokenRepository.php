<?php

namespace Leantime\Domain\Auth\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use Leantime\Core\Db\Db;

class AccessTokenRepository
{
    private ConnectionInterface $db;

    public function __construct(Db $db)
    {
        $this->db = $db->getConnection();
    }

    public function createToken(int $userId, string $name, array $abilities = ['*']): array
    {
        $token = Str::random(40);
        $hashedToken = hash('sha256', $token);

        $id = $this->db->table('zp_access_tokens')->insertGetId([
            'tokenable_type' => 'Leantime\\Domain\\Auth\\Services\\Auth',
            'tokenable_id' => $userId,
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => json_encode($abilities),
            'created_at' => now(),
        ]);

        return [
            'id' => $id,
            'token' => $token,
        ];
    }

    public function findToken(string $token): ?array
    {
        $hashedToken = hash('sha256', $token);

        $result = $this->db->table('zp_access_tokens')
            ->where('token', $hashedToken)
            ->first();

        return $result ? (array) $result : null;
    }

    public function findTokenById(int $tokenId): ?array
    {
        $result = $this->db->table('zp_access_tokens')
            ->where('id', $tokenId)
            ->first();

        return $result ? (array) $result : null;
    }

    public function deleteToken(int $id): bool
    {
        return $this->db->table('zp_access_tokens')
            ->where('id', $id)
            ->delete() > 0;
    }

    public function updateLastUsedAt(int $id): bool
    {
        return $this->db->table('zp_access_tokens')
            ->where('id', $id)
            ->update(['last_used_at' => now()]) > 0;
    }

    public function getTokenByUserId(int|string $userId, ?string $name = null): ?array
    {
        $query = $this->db->table('zp_access_tokens')
            ->where('tokenable_id', $userId)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });

        if ($name !== null) {
            $query->where('name', $name);
        }

        $result = $query->first();

        return $result ? (array) $result : null;
    }

    public function getAllTokensByUserId(int|string $userId, ?string $name = null): ?array
    {
        $query = $this->db->table('zp_access_tokens')
            ->where('tokenable_id', $userId);

        if ($name !== null) {
            $query->where('name', $name);
        }

        $results = $query->get();

        if ($results->isEmpty()) {
            return null;
        }

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }
}
