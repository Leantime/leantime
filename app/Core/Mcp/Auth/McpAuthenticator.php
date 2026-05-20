<?php

namespace Leantime\Core\Mcp\Auth;

use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Mcp\Repositories\Mcp as McpRepository;
use Leantime\Domain\Users\Services\Users;

class McpAuthenticator
{
    public function __construct(
        private AccessTokenRepository $tokenRepository,
        private Users $usersService,
        private McpRepository $mcpRepository,
    ) {}

    public function authenticate(IncomingRequest $request): McpPrincipal
    {
        $token = $request instanceof ApiRequest ? $request->getBearerToken() : $request->bearerToken();

        if (empty($token)) {
            throw new McpException('Missing bearer token', -32001, 401);
        }

        $tokenRow = $this->tokenRepository->findToken($token);
        if ($tokenRow === null) {
            throw new McpException('Invalid bearer token', -32001, 401);
        }

        if (! empty($tokenRow['expires_at']) && strtotime((string) $tokenRow['expires_at']) <= time()) {
            throw new McpException('Bearer token expired', -32001, 401);
        }

        $abilities = json_decode((string) ($tokenRow['abilities'] ?? '[]'), true);
        if (! is_array($abilities)) {
            throw new McpException('Token abilities are malformed', -32001, 401);
        }

        if (! in_array('*', $abilities, true) && ! in_array('mcp:connect', $abilities, true)) {
            throw new McpException('Token is not allowed to connect to MCP', -32003, 403);
        }

        $user = $this->usersService->getUser((int) $tokenRow['tokenable_id']);
        if ($user === false) {
            throw new McpException('Token user not found', -32001, 401);
        }

        $this->tokenRepository->updateLastUsedAt((int) $tokenRow['id']);
        $agentId = $this->mcpRepository->createOrTouchAgent(
            accessTokenId: (int) $tokenRow['id'],
            userId: (int) $user['id'],
            name: (string) ($tokenRow['name'] ?? 'mcp-agent'),
        );

        return new McpPrincipal(
            userId: (int) $user['id'],
            roleId: (int) $user['role'],
            role: Roles::getRoleString((int) $user['role']) ?: (string) $user['role'],
            accessTokenId: (int) $tokenRow['id'],
            tokenName: (string) ($tokenRow['name'] ?? 'mcp-agent'),
            abilities: $abilities,
            agentId: $agentId,
            user: $user,
        );
    }
}
