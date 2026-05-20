<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Mcp\Repositories\Mcp as McpRepository;

class OperationsGet extends AbstractTool
{
    public function __construct(private McpRepository $mcpRepository) {}

    public function name(): string
    {
        return 'operations.get';
    }

    public function title(): string
    {
        return 'Get Operation Status';
    }

    public function description(): string
    {
        return 'Reads the state of a queued, approved, or completed MCP tool call.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['operationId'],
            'properties' => [
                'operationId' => ['type' => 'string'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:read'];
    }

    public function riskLevel(): string
    {
        return 'read';
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $operationId = (string) ($arguments['operationId'] ?? '');
        if ($operationId === '') {
            throw new McpException('operationId is required', -32602, 400);
        }

        $toolCall = $this->mcpRepository->getToolCallByOperationId($operationId);
        if ($toolCall === null) {
            throw new McpException('Operation not found', -32004, 404);
        }

        if ((int) $toolCall['userId'] !== $context->principal->userId && ! in_array($context->principal->role, ['manager', 'admin', 'owner'], true)) {
            throw new McpException('Operation access denied', -32003, 403);
        }

        return [
            'operation' => [
                'operationId' => $toolCall['operationId'],
                'toolName' => $toolCall['toolName'],
                'status' => $toolCall['status'],
                'riskLevel' => $toolCall['riskLevel'],
                'projectId' => (int) $toolCall['projectId'],
                'response' => json_decode((string) ($toolCall['responseBody'] ?? 'null'), true),
                'startedAt' => $toolCall['startedAt'],
                'completedAt' => $toolCall['completedAt'],
                'createdAt' => $toolCall['createdAt'],
            ],
        ];
    }
}
