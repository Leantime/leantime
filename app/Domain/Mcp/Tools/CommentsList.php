<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;

class CommentsList extends AbstractTool
{
    public function __construct(
        private CommentRepository $commentsRepository,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'comments.list';
    }

    public function title(): string
    {
        return 'List Comments';
    }

    public function description(): string
    {
        return 'Lists comments for a ticket or project the principal can access.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['entityType', 'entityId'],
            'properties' => [
                'entityType' => ['type' => 'string', 'enum' => ['ticket', 'project']],
                'entityId' => ['type' => 'integer'],
                'includeReplies' => ['type' => 'boolean', 'default' => true],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:read', 'comments:read'];
    }

    public function riskLevel(): string
    {
        return 'read';
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $entityType = (string) ($arguments['entityType'] ?? '');
        $entityId = (int) ($arguments['entityId'] ?? 0);

        if ($entityId <= 0 || ! in_array($entityType, ['ticket', 'project'], true)) {
            throw new McpException('entityType and entityId are required', -32602, 400);
        }

        if ($entityType === 'ticket') {
            $this->access->assertTicketAccess($context->principal, $entityId);
        } else {
            $this->access->assertProjectAccess($context->principal, $entityId);
        }

        $parent = ($arguments['includeReplies'] ?? true) ? -1 : 0;

        return [
            'comments' => $this->commentsRepository->getComments($entityType, $entityId, $parent) ?: [],
        ];
    }
}
