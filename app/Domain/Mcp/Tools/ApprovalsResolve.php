<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Mcp\Repositories\Mcp as McpRepository;
use Leantime\Domain\Mcp\Services\ToolExecutor;

class ApprovalsResolve extends AbstractTool
{
    public function __construct(
        private McpRepository $mcpRepository,
        private ToolExecutor $toolExecutor,
    ) {}

    public function name(): string
    {
        return 'approvals.resolve';
    }

    public function title(): string
    {
        return 'Resolve MCP Approval';
    }

    public function description(): string
    {
        return 'Approves or rejects a pending MCP approval request.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['approvalId', 'decision'],
            'properties' => [
                'approvalId' => ['type' => 'integer'],
                'decision' => ['type' => 'string', 'enum' => ['approve', 'reject']],
                'reason' => ['type' => 'string'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:write'];
    }

    public function riskLevel(): string
    {
        return 'admin';
    }

    public function requiresIdempotency(): bool
    {
        return true;
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        if (! in_array($context->principal->role, ['manager', 'admin', 'owner'], true)) {
            throw new McpException('Only managers or above can resolve approvals', -32003, 403);
        }

        $approvalId = (int) ($arguments['approvalId'] ?? 0);
        $decision = (string) ($arguments['decision'] ?? '');
        $reason = $arguments['reason'] ?? null;

        if ($approvalId <= 0 || ! in_array($decision, ['approve', 'reject'], true)) {
            throw new McpException('approvalId and a valid decision are required', -32602, 400);
        }

        $approval = $this->mcpRepository->getApproval($approvalId);
        if ($approval === null) {
            throw new McpException('Approval request not found', -32004, 404);
        }

        if (($approval['status'] ?? '') !== 'pending') {
            throw new McpException('Approval request is not pending', -32009, 409);
        }

        if ($decision === 'reject') {
            $this->mcpRepository->updateApproval($approvalId, [
                'status' => 'rejected',
                'resolvedByUserId' => $context->principal->userId,
                'resolvedAt' => now(),
                'reason' => $reason,
            ]);

            if ($approval !== null) {
                $this->mcpRepository->updateToolCall((int) $approval['toolCallId'], [
                    'status' => 'rejected',
                    'completedAt' => now(),
                    'responseBody' => json_encode([
                        'content' => [[
                            'type' => 'text',
                            'text' => 'Approval request was rejected.',
                        ]],
                        'isError' => true,
                    ]),
                ]);

                $idempotencyRecord = $this->mcpRepository->findIdempotencyRecordByToolCallId((int) $approval['toolCallId']);
                if ($idempotencyRecord !== null) {
                    $this->mcpRepository->updateIdempotencyRecord((int) $idempotencyRecord['id'], [
                        'status' => 'rejected',
                        'responseBody' => json_encode([
                            'content' => [[
                                'type' => 'text',
                                'text' => 'Approval request was rejected.',
                            ]],
                            'isError' => true,
                        ]),
                    ]);
                }
            }

            return [
                'approvalId' => $approvalId,
                'status' => 'rejected',
            ];
        }

        $response = $this->toolExecutor->executeApprovedToolCall($approvalId, $context);

        return [
            'approvalId' => $approvalId,
            'status' => 'approved',
            'response' => $response,
        ];
    }
}
