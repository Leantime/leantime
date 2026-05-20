<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Users\Services\Users;

class UsersGet extends AbstractTool
{
    public function __construct(
        private Users $usersService,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'users.get';
    }

    public function title(): string
    {
        return 'Get User';
    }

    public function description(): string
    {
        return 'Returns a project-visible user profile.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['projectId', 'userId'],
            'properties' => [
                'projectId' => ['type' => 'integer'],
                'userId' => ['type' => 'integer'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:read', 'users:read'];
    }

    public function riskLevel(): string
    {
        return 'read';
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $userId = (int) ($arguments['userId'] ?? 0);

        if ($projectId <= 0 || $userId <= 0) {
            throw new McpException('projectId and userId are required', -32602, 400);
        }

        $this->access->assertProjectAccess($context->principal, $projectId);
        $visibleUsers = $this->usersService->getUsersWithProjectAccess($context->principal->userId, $projectId);

        $visibleIds = array_map(static fn (array $user) => (int) $user['id'], $visibleUsers);
        if (! in_array($userId, $visibleIds, true) && ! in_array($context->principal->role, ['manager', 'admin', 'owner'], true)) {
            throw new McpException('User is not visible in this project', -32003, 403);
        }

        $user = $this->usersService->getUser($userId);
        if ($user === false) {
            throw new McpException('User not found', -32004, 404);
        }

        return [
            'user' => [
                'id' => (int) $user['id'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'username' => $user['username'],
                'role' => $user['role'],
                'clientId' => $user['clientId'],
                'jobTitle' => $user['jobTitle'],
                'jobLevel' => $user['jobLevel'],
                'department' => $user['department'],
                'status' => $user['status'],
                'modified' => $user['modified'],
            ],
        ];
    }
}
