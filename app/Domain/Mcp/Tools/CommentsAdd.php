<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Mcp\Services\CommentWriter;

class CommentsAdd extends AbstractTool
{
    public function __construct(
        private CommentWriter $commentWriter,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'comments.add';
    }

    public function title(): string
    {
        return 'Add Comment';
    }

    public function description(): string
    {
        return 'Adds a comment to a ticket or project using explicit actor and project context.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['entityType', 'entityId', 'text'],
            'properties' => [
                'entityType' => ['type' => 'string', 'enum' => ['ticket', 'project']],
                'entityId' => ['type' => 'integer'],
                'text' => ['type' => 'string'],
                'parentId' => ['type' => 'integer'],
                'status' => ['type' => 'string'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:write', 'comments:write'];
    }

    public function riskLevel(): string
    {
        return 'write';
    }

    public function requiresIdempotency(): bool
    {
        return true;
    }

    public function scopeProjectId(McpRequestContext $context, array $arguments): int
    {
        $entityType = (string) ($arguments['entityType'] ?? '');
        $entityId = (int) ($arguments['entityId'] ?? 0);

        if ($entityType === 'ticket' && $entityId > 0) {
            $ticket = $this->access->assertTicketAccess($context->principal, $entityId);

            return (int) $ticket->projectId;
        }

        if ($entityType === 'project' && $entityId > 0) {
            $this->access->assertProjectAccess($context->principal, $entityId);

            return $entityId;
        }

        return 0;
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $entityType = (string) ($arguments['entityType'] ?? '');
        $entityId = (int) ($arguments['entityId'] ?? 0);
        $text = trim((string) ($arguments['text'] ?? ''));

        if ($entityId <= 0 || $text === '' || ! in_array($entityType, ['ticket', 'project'], true)) {
            throw new McpException('entityType, entityId, and text are required', -32602, 400);
        }

        if ($entityType === 'ticket') {
            $entity = $this->access->assertTicketAccess($context->principal, $entityId);
            $projectId = (int) $entity->projectId;
        } else {
            $entity = $this->access->assertProjectAccess($context->principal, $entityId);
            $projectId = $entityId;
        }

        $commentId = $this->commentWriter->addComment(
            authorId: $context->principal->userId,
            authorName: (string) ($context->principal->user['firstname'] ?? $context->principal->tokenName),
            module: $entityType,
            entityId: $entityId,
            projectId: $projectId,
            text: $text,
            entity: $entity,
            parentId: (int) ($arguments['parentId'] ?? 0),
            status: (string) ($arguments['status'] ?? ''),
        );

        if ($commentId === false) {
            throw new McpException('Comment could not be created', -32000, 500);
        }

        return [
            'commentId' => (int) $commentId,
            'entityType' => $entityType,
            'entityId' => $entityId,
            'projectId' => $projectId,
            'status' => 'created',
        ];
    }
}
