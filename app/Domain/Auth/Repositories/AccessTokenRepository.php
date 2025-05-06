<?php

namespace Leantime\Domain\Auth\Repositories;

use Illuminate\Support\Str;
use Leantime\Infrastructure\Database\Db;
use PDO;

class AccessTokenRepository
{
    private Db $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function createToken(int $userId, string $name, array $abilities = ['*']): array
    {
        $token = Str::random(40);
        $hashedToken = hash('sha256', $token);

        $query = 'INSERT INTO zp_access_tokens
                (tokenable_type, tokenable_id, name, token, abilities, created_at)
                VALUES
                (:tokenable_type, :tokenable_id, :name, :token, :abilities, NOW())';

        $statement = $this->db->database->prepare($query);
        $statement->bindValue(':tokenable_type', 'Leantime\\Domain\\Auth\\Services\\Auth', PDO::PARAM_STR);
        $statement->bindValue(':tokenable_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':name', $name, PDO::PARAM_STR);
        $statement->bindValue(':token', $hashedToken, PDO::PARAM_STR);
        $statement->bindValue(':abilities', json_encode($abilities), PDO::PARAM_STR);

        $statement->execute();
        $id = $this->db->database->lastInsertId();

        return [
            'id' => $id,
            'token' => $token,
        ];
    }

    public function findToken(string $token): ?array
    {
        $hashedToken = hash('sha256', $token);

        $query = 'SELECT * FROM zp_access_tokens WHERE token = :token';
        $statement = $this->db->database->prepare($query);
        $statement->bindValue(':token', $hashedToken, PDO::PARAM_STR);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function findTokenById(int $tokenId): ?array
    {

        $query = 'SELECT * FROM zp_access_tokens WHERE id = :tokenId';
        $statement = $this->db->database->prepare($query);
        $statement->bindValue(':tokenId', $tokenId, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function deleteToken(int $id): bool
    {
        $query = 'DELETE FROM zp_access_tokens WHERE id = :id';
        $statement = $this->db->database->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        return $statement->execute();
    }

    public function updateLastUsedAt(int $id): bool
    {
        $query = 'UPDATE zp_access_tokens SET last_used_at = NOW() WHERE id = :id';
        $statement = $this->db->database->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        return $statement->execute();
    }

    public function getTokenByUserId($userId, $name = null)
    {

        $query = 'SELECT * FROM zp_access_tokens
            WHERE tokenable_id = :userId
            AND ((expires_at IS NULL) OR expires_at > NOW())';

        if ($name !== null) {
            $query .= ' AND name = :name';
        }

        $statement = $this->db->database->prepare($query);
        $statement->bindValue(':userId', $userId, PDO::PARAM_STR);

        if ($name !== null) {
            $statement->bindValue(':name', $name, PDO::PARAM_STR);
        }

        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;

    }

    public function getAllTokensByUserId($userId, $name = null)
    {

        $query = 'SELECT * FROM zp_access_tokens
            WHERE tokenable_id = :userId';

        if ($name !== null) {
            $query .= ' AND name = :name';
        }

        $statement = $this->db->database->prepare($query);
        $statement->bindValue(':userId', $userId, PDO::PARAM_STR);

        if ($name !== null) {
            $statement->bindValue(':name', $name, PDO::PARAM_STR);
        }

        $statement->execute();

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result ?: null;

    }
}
