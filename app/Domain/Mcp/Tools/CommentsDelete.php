<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Mcp\Services\CommentDeletionService;

class CommentsDelete extends AbstractTool
{
    public function __construct(private CommentDeletionService $commentDeletionService) {}

    public function name(): string
    {
        return 'comments.delete';
    }

    public function title(): string
    {
        return 'Delete Comment';
    }

    public function description(): string
    {
        return 'Deletes a ticket or project comment when the principal is allowed to remove it.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['commentId'],
            'properties' => [
                'commentId' => ['type' => 'integer'],
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
        $commentId = (int) ($arguments['commentId'] ?? 0);
        if ($commentId <= 0) {
            return 0;
        }

        $result = $this->commentDeletionService->inspectCommentDeletion($context->principal, $commentId);

        return (int) ($result['projectId'] ?? 0);
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $commentId = (int) ($arguments['commentId'] ?? 0);
        if ($commentId <= 0) {
            throw new McpException('commentId is required', -32602, 400);
        }

        return $this->commentDeletionService->deleteComment($context->principal, $commentId);
    }
}
