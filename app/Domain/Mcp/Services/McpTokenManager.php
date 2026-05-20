<?php

namespace Leantime\Domain\Mcp\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Mcp\Auth\McpAbilityCatalog;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;

class McpTokenManager
{
    public function __construct(private AccessTokenRepository $tokenRepository) {}

    public function createToken(int $userId, string $name, string $preset, array $extraAbilities = [], ?int $expiresDays = null): array
    {
        $presetAbilities = McpAbilityCatalog::abilitiesForPreset($preset);
        if ($presetAbilities === []) {
            throw new McpException('Unknown MCP token preset', -32602, 400);
        }

        if ($expiresDays !== null && $expiresDays <= 0) {
            throw new McpException('Expiration days must be positive', -32602, 400);
        }

        $abilities = McpAbilityCatalog::normalize(array_merge($presetAbilities, $extraAbilities));
        $expiresAt = $expiresDays !== null
            ? CarbonImmutable::now()->addDays($expiresDays)->toDateTimeString()
            : null;

        return $this->tokenRepository->createToken($userId, $name, $abilities, $expiresAt);
    }

    public function listTokens(int $userId, ?string $name = null): array
    {
        $tokens = $this->tokenRepository->getAllTokensByUserId($userId, $name) ?? [];

        return array_map(static function (array $token): array {
            $abilities = json_decode((string) ($token['abilities'] ?? '[]'), true);
            $token['abilitiesDecoded'] = is_array($abilities) ? $abilities : ['<invalid-json>'];

            return $token;
        }, $tokens);
    }

    public function revokeToken(int $tokenId, int $expectedUserId): bool
    {
        $token = $this->tokenRepository->findTokenById($tokenId);
        if ($token === null) {
            throw new McpException('Token not found', -32004, 404);
        }

        if ((int) $token['tokenable_id'] !== $expectedUserId) {
            throw new McpException('Token does not belong to this user', -32003, 403);
        }

        return $this->tokenRepository->deleteToken($tokenId);
    }
}
