<?php

namespace Leantime\Domain\Mcp\Services;

use Illuminate\Support\Str;
use Leantime\Core\Mcp\Auth\McpPrincipal;
use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Mcp\Jobs\ExecuteQueuedToolCall;
use Leantime\Domain\Mcp\Repositories\Mcp as McpRepository;
use Leantime\Domain\Queue\Services\Queue;
use Leantime\Domain\Queue\Workers\Workers;

class ToolExecutor
{
    public function __construct(
        private ToolRegistry $toolRegistry,
        private McpRepository $mcpRepository,
        private McpAccess $access,
    ) {}

    public function execute(McpRequestContext $context, string $toolName, array $arguments, ?int $requestLogId = null): array
    {
        $tool = $this->toolRegistry->find($toolName);
        $this->access->assertAbility($context->principal, $tool->requiredAbilities());

        $meta = $this->extractMetaArguments($arguments);
        $projectId = $tool->scopeProjectId($context, $arguments);
        $argumentsHash = hash('sha256', json_encode($arguments));

        $idempotencyRecord = null;
        if ($tool->requiresIdempotency()) {
            $idempotencyKey = $meta['idempotencyKey'] ?? $context->idempotencyKey;
            if (empty($idempotencyKey)) {
                throw new McpException('Mutating tools require an idempotency key', -32602, 400);
            }

            $existing = $this->mcpRepository->findIdempotencyRecord(
                accessTokenId: $context->principal->accessTokenId,
                toolName: $tool->name(),
                projectId: $projectId,
                idempotencyKey: $idempotencyKey,
            );

            if ($existing !== null) {
                if ($existing['argumentsHash'] !== $argumentsHash) {
                    throw new McpException('Idempotency key was already used with different arguments', -32009, 409);
                }

                if (($existing['status'] ?? '') === 'completed' && ! empty($existing['responseBody'])) {
                    return json_decode((string) $existing['responseBody'], true) ?? [];
                }

                throw new McpException('A matching operation is already in progress', -32009, 409, [
                    'toolCallId' => $existing['toolCallId'] ?? null,
                ]);
            }
        }

        $operationId = (string) Str::uuid();
        $toolCallId = $this->mcpRepository->createToolCall([
            'operationId' => $operationId,
            'requestLogId' => $requestLogId,
            'agentId' => $context->principal->agentId,
            'accessTokenId' => $context->principal->accessTokenId,
            'userId' => $context->principal->userId,
            'projectId' => $projectId,
            'toolName' => $tool->name(),
            'toolVersion' => $tool->version(),
            'riskLevel' => $tool->riskLevel(),
            'status' => 'pending',
            'argumentsHash' => $argumentsHash,
            'context' => json_encode($context->toArray()),
            'arguments' => json_encode($arguments),
        ]);

        if ($tool->requiresIdempotency()) {
            $idempotencyRecord = $this->mcpRepository->createIdempotencyRecord([
                'accessTokenId' => $context->principal->accessTokenId,
                'userId' => $context->principal->userId,
                'projectId' => $projectId,
                'toolName' => $tool->name(),
                'idempotencyKey' => $meta['idempotencyKey'] ?? $context->idempotencyKey,
                'argumentsHash' => $argumentsHash,
                'status' => 'pending',
                'toolCallId' => $toolCallId,
            ]);
        }

        if (($meta['approvalMode'] ?? '') === 'request') {
            $approvalId = $this->mcpRepository->createApproval([
                'toolCallId' => $toolCallId,
                'projectId' => $projectId,
                'requestedByUserId' => $context->principal->userId,
                'toolName' => $tool->name(),
                'status' => 'pending',
                'reason' => $meta['approvalReason'] ?? null,
                'payload' => json_encode([
                    'toolName' => $tool->name(),
                    'arguments' => $arguments,
                    'context' => $context->toArray(),
                ]),
            ]);

            $result = $this->pendingResult('approval_requested', [
                'approvalId' => $approvalId,
                'operationId' => $operationId,
            ]);

            $this->mcpRepository->updateToolCall($toolCallId, [
                'status' => 'awaiting_approval',
                'responseBody' => json_encode($result),
            ]);

            if ($idempotencyRecord !== null) {
                $this->mcpRepository->updateIdempotencyRecord($idempotencyRecord, [
                    'status' => 'awaiting_approval',
                    'responseBody' => json_encode($result),
                ]);
            }

            return $result;
        }

        if (($meta['async'] ?? false) === true && $tool->supportsAsync()) {
            Queue::addJob(
                channel: Workers::DEFAULT,
                subject: ExecuteQueuedToolCall::class,
                message: ['toolCallId' => $toolCallId],
                userId: $context->principal->userId,
                projectId: $projectId > 0 ? $projectId : null,
            );

            $result = $this->pendingResult('queued', ['operationId' => $operationId]);
            $this->mcpRepository->updateToolCall($toolCallId, [
                'status' => 'queued',
                'responseBody' => json_encode($result),
            ]);

            if ($idempotencyRecord !== null) {
                $this->mcpRepository->updateIdempotencyRecord($idempotencyRecord, [
                    'status' => 'queued',
                    'responseBody' => json_encode($result),
                ]);
            }

            return $result;
        }

        return $this->runToolCall($toolCallId, $context, $arguments, $toolName, $idempotencyRecord);
    }

    public function executeQueuedToolCall(int $toolCallId): bool
    {
        $toolCall = $this->mcpRepository->getToolCall($toolCallId);
        if ($toolCall === null || ($toolCall['status'] ?? '') !== 'queued') {
            return false;
        }

        $contextData = json_decode((string) $toolCall['context'], true) ?? [];
        $arguments = json_decode((string) $toolCall['arguments'], true) ?? [];

        $principal = new McpPrincipal(
            userId: (int) ($contextData['principalUserId'] ?? $toolCall['userId']),
            roleId: (int) ($contextData['principalRoleId'] ?? 0),
            role: (string) ($contextData['principalRole'] ?? 'editor'),
            accessTokenId: (int) ($contextData['accessTokenId'] ?? $toolCall['accessTokenId']),
            tokenName: (string) ($contextData['tokenName'] ?? 'mcp-agent'),
            abilities: is_array($contextData['abilities'] ?? null) ? $contextData['abilities'] : ['*'],
            agentId: isset($contextData['agentId']) ? (int) $contextData['agentId'] : null,
        );

        $context = new McpRequestContext(
            requestId: (string) ($contextData['requestId'] ?? Str::uuid()),
            jsonRpcId: $contextData['jsonRpcId'] ?? null,
            method: 'tools/call',
            principal: $principal,
            mcpSessionId: $contextData['mcpSessionId'] ?? null,
            correlationId: $contextData['correlationId'] ?? (string) Str::uuid(),
            idempotencyKey: $contextData['idempotencyKey'] ?? null,
            remoteIp: $contextData['remoteIp'] ?? null,
            userAgent: $contextData['userAgent'] ?? null,
            protocolVersion: $contextData['protocolVersion'] ?? null,
            approvedByUserId: $contextData['approvedByUserId'] ?? null,
        );

        $this->runToolCall(
            toolCallId: $toolCallId,
            context: $context,
            arguments: $arguments,
            toolName: (string) $toolCall['toolName'],
            idempotencyRecordId: null,
        );

        return true;
    }

    public function executeApprovedToolCall(int $approvalId, McpRequestContext $approvalContext): array
    {
        $approval = $this->mcpRepository->getApproval($approvalId);
        if ($approval === null) {
            throw new McpException('Approval request not found', -32004, 404);
        }

        if (($approval['status'] ?? '') !== 'pending') {
            throw new McpException('Approval request is not pending', -32009, 409);
        }

        $payload = json_decode((string) $approval['payload'], true) ?? [];
        $toolContext = $payload['context'] ?? [];

        $approvedPrincipal = new McpPrincipal(
            userId: (int) ($toolContext['principalUserId'] ?? $approvalContext->principal->userId),
            roleId: (int) ($toolContext['principalRoleId'] ?? $approvalContext->principal->roleId),
            role: (string) ($toolContext['principalRole'] ?? $approvalContext->principal->role),
            accessTokenId: (int) ($toolContext['accessTokenId'] ?? $approvalContext->principal->accessTokenId),
            tokenName: (string) ($toolContext['tokenName'] ?? $approvalContext->principal->tokenName),
            abilities: is_array($toolContext['abilities'] ?? null) ? $toolContext['abilities'] : $approvalContext->principal->abilities,
            agentId: isset($toolContext['agentId']) ? (int) $toolContext['agentId'] : $approvalContext->principal->agentId,
        );

        $approvedContext = new McpRequestContext(
            requestId: (string) ($toolContext['requestId'] ?? Str::uuid()),
            jsonRpcId: $toolContext['jsonRpcId'] ?? null,
            method: 'tools/call',
            principal: $approvedPrincipal,
            mcpSessionId: $toolContext['mcpSessionId'] ?? null,
            correlationId: $toolContext['correlationId'] ?? (string) Str::uuid(),
            idempotencyKey: $toolContext['idempotencyKey'] ?? null,
            remoteIp: $toolContext['remoteIp'] ?? null,
            userAgent: $toolContext['userAgent'] ?? null,
            protocolVersion: $toolContext['protocolVersion'] ?? null,
            approvedByUserId: $approvalContext->principal->userId,
        );

        $this->mcpRepository->updateApproval($approvalId, [
            'status' => 'approved',
            'resolvedByUserId' => $approvalContext->principal->userId,
            'resolvedAt' => now(),
        ]);

        return $this->runToolCall(
            toolCallId: (int) $approval['toolCallId'],
            context: $approvedContext,
            arguments: $payload['arguments'] ?? [],
            toolName: (string) $approval['toolName'],
            idempotencyRecordId: null,
        );
    }

    private function runToolCall(int $toolCallId, McpRequestContext $context, array $arguments, string $toolName, ?int $idempotencyRecordId): array
    {
        if ($idempotencyRecordId === null) {
            $idempotencyRecord = $this->mcpRepository->findIdempotencyRecordByToolCallId($toolCallId);
            $idempotencyRecordId = $idempotencyRecord['id'] ?? null;
        }

        $tool = $this->toolRegistry->find($toolName);
        $cleanArguments = $this->stripMetaArguments($arguments);

        $this->mcpRepository->updateToolCall($toolCallId, [
            'status' => 'running',
            'startedAt' => now(),
        ]);

        try {
            $result = $tool->execute($context, $cleanArguments);
            $response = $this->successResult($result + ['operationId' => $this->mcpRepository->getToolCall($toolCallId)['operationId']]);

            $this->mcpRepository->updateToolCall($toolCallId, [
                'status' => 'completed',
                'completedAt' => now(),
                'responseBody' => json_encode($response),
            ]);

            if ($idempotencyRecordId !== null) {
                $this->mcpRepository->updateIdempotencyRecord($idempotencyRecordId, [
                    'status' => 'completed',
                    'responseBody' => json_encode($response),
                ]);
            }

            return $response;
        } catch (\Throwable $throwable) {
            $errorMessage = $throwable instanceof McpException ? $throwable->getMessage() : 'Tool execution failed';

            $this->mcpRepository->updateToolCall($toolCallId, [
                'status' => 'failed',
                'completedAt' => now(),
                'errorCode' => (string) $throwable->getCode(),
                'responseBody' => json_encode($this->errorResult($errorMessage)),
            ]);

            if ($idempotencyRecordId !== null) {
                $this->mcpRepository->updateIdempotencyRecord($idempotencyRecordId, [
                    'status' => 'failed',
                    'responseBody' => json_encode($this->errorResult($errorMessage)),
                ]);
            }

            throw $throwable;
        }
    }

    private function stripMetaArguments(array $arguments): array
    {
        return array_filter(
            $arguments,
            static fn (string $key) => ! str_starts_with($key, '_'),
            ARRAY_FILTER_USE_KEY,
        );
    }

    private function extractMetaArguments(array $arguments): array
    {
        return [
            'async' => (bool) ($arguments['_async'] ?? false),
            'approvalMode' => $arguments['_approvalMode'] ?? null,
            'approvalReason' => $arguments['_approvalReason'] ?? null,
            'idempotencyKey' => $arguments['_idempotencyKey'] ?? null,
            'projectId' => $arguments['projectId'] ?? null,
        ];
    }

    private function pendingResult(string $status, array $data): array
    {
        return [
            'content' => [[
                'type' => 'text',
                'text' => json_encode(['status' => $status] + $data, JSON_PRETTY_PRINT),
            ]],
            'structuredContent' => ['status' => $status] + $data,
        ];
    }

    private function successResult(array $data): array
    {
        return [
            'content' => [[
                'type' => 'text',
                'text' => json_encode($data, JSON_PRETTY_PRINT),
            ]],
            'structuredContent' => $data,
        ];
    }

    private function errorResult(string $message): array
    {
        return [
            'content' => [[
                'type' => 'text',
                'text' => $message,
            ]],
            'isError' => true,
        ];
    }
}
