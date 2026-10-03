<?php

namespace Leantime\Domain\Comments\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Comments\Services\Comments;

/**
 * Add a new comment to a specific entity.
 */
class AddCommentTool extends Tool
{
    public function __construct(
        private Comments $commentsService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('text')->description('Comment text.')
            ->required()
            ->string('module')->description('Module type: ticket, project, article (wiki), idea, goal or {type}canvasitem.')
            ->required()
            ->integer('entityId')->description('ID of the entity to add comment to.')
            ->required()
            ->string('status')->description('Status indicator for project updates (green, yellow, red). Only used for project comments.');
    }

    public function name(): string
    {
        return 'addComment';
    }

    public function description(): string
    {
        return 'Adds a new comment to a specific entity.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $module = (string) ($arguments['module'] ?? '');
        $entityId = (int) ($arguments['entityId'] ?? 0);

        if (trim((string) ($arguments['text'] ?? '')) === '') {
            return ToolResult::error('The comment text is empty.');
        }

        $values = [
            'text' => $arguments['text'],
            'father' => 0,
            'status' => ($arguments['status'] ?? ''),
        ];

        // The service resolves (and access-checks) the entity from module + id, normalizes module
        // aliases such as "tickets"/"goal", and reports unknown modules or missing entities as
        // errors. A placeholder entity used to be passed for unknown modules, which stored
        // comments under modules nothing reads (#3704).
        try {
            $result = $this->commentsService->addComment($values, $module, $entityId);
        } catch (LeantimeExceptionInterface $e) {
            return ToolResult::error($e->getClientMessage());
        }

        if ($result) {
            return ToolResult::text("Comment added successfully to {$module} #{$entityId}");
        }

        return ToolResult::error('Failed to add comment. Please check the provided information.');
    }
}
