<?php

namespace Leantime\Domain\Mcp\Services;

use Leantime\Core\Mcp\Auth\McpPrincipal;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Comments\Repositories\Comments;
use Leantime\Domain\Reactions\Repositories\Reactions;

class CommentDeletionService
{
    public function __construct(
        private Comments $commentsRepository,
        private Reactions $reactionsRepository,
        private McpAccess $access,
    ) {}

    public function inspectCommentDeletion(McpPrincipal $principal, int $commentId): array
    {
        $comment = $this->commentsRepository->getComment($commentId);
        if ($comment === false) {
            throw new McpException('Comment not found', -32004, 404);
        }

        if (($comment['module'] ?? '') === 'ticket') {
            $ticket = $this->access->assertTicketAccess($principal, (int) $comment['moduleId']);
            $projectId = (int) $ticket->projectId;
        } elseif (($comment['module'] ?? '') === 'project') {
            $this->access->assertProjectAccess($principal, (int) $comment['moduleId']);
            $projectId = (int) $comment['moduleId'];
        } else {
            throw new McpException('Only ticket and project comments are supported for MCP deletion', -32602, 400);
        }

        if ((int) $comment['userId'] !== $principal->userId && ! in_array($principal->role, ['manager', 'admin', 'owner'], true)) {
            throw new McpException('Only the author or a manager and above can delete comments', -32003, 403);
        }

        $replies = $this->commentsRepository->getReplies($commentId);
        if ($replies !== false && count($replies) > 0) {
            throw new McpException('Comments with replies cannot be deleted through MCP', -32009, 409);
        }

        $reactions = $this->reactionsRepository->getGroupedEntityReactions('comment', $commentId);
        if ($reactions !== false && count($reactions) > 0) {
            throw new McpException('Comments with reactions cannot be deleted through MCP', -32009, 409);
        }

        return [
            'comment' => $comment,
            'projectId' => $projectId,
        ];
    }

    public function deleteComment(McpPrincipal $principal, int $commentId): array
    {
        $inspection = $this->inspectCommentDeletion($principal, $commentId);
        $comment = $inspection['comment'];
        $projectId = (int) $inspection['projectId'];

        if (! $this->commentsRepository->deleteComment($commentId)) {
            throw new McpException('Comment could not be deleted', -32000, 500);
        }

        return [
            'commentId' => $commentId,
            'entityType' => $comment['module'],
            'entityId' => (int) $comment['moduleId'],
            'projectId' => $projectId,
            'status' => 'deleted',
        ];
    }
}
