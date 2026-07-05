<?php

namespace Leantime\Domain\Comments\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Comments\Services\Comments;

/**
 * Get all comments for a specific entity.
 */
#[IsReadOnly]
class GetCommentsTool extends Tool
{
    public function __construct(
        private Comments $commentsService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('module')->description('Module type (ticket, project, goal, etc.).')
            ->required()
            ->integer('entityId')->description('ID of the entity to get comments for.')
            ->required()
            ->integer('commentOrder')->description('Order of comments (0 = newest first, 1 = oldest first).');
    }

    public function name(): string
    {
        return 'getComments';
    }

    public function description(): string
    {
        return 'Gets all comments for a specific entity.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $module = $arguments['module'];
        $entityId = (int) ($arguments['entityId'] ?? 0);
        $commentOrder = (int) ($arguments['commentOrder'] ?? 0);

        $comments = $this->commentsService->getComments($module, $entityId, $commentOrder);

        if (empty($comments)) {
            return ToolResult::text("No comments found for {$module} ID: {$entityId}");
        }

        $response = "## Comments for {$module} #{$entityId}\n";
        foreach ($comments as $comment) {
            $result = [
                'id' => $comment['id'],
                'text' => Str::sanitizeForLLM($comment['text']),
                'date' => $comment['date'],
                'userId' => $comment['userId'],
                'author' => $comment['firstname'].' '.$comment['lastname'],
                'status' => $comment['status'] ?: 'None',
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return ToolResult::text($response);
    }
}
