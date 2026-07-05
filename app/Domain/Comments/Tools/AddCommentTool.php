<?php

namespace Leantime\Domain\Comments\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Add a new comment to a specific entity.
 */
class AddCommentTool extends Tool
{
    public function __construct(
        private Comments $commentsService,
        private Projects $projectService,
        private Tickets $ticketService,
        private Goalcanvas $goalcanvasService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('text')->description('Comment text.')
            ->required()
            ->string('module')->description('Module type (ticket, project, goal, etc.).')
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
        $module = $arguments['module'];
        $entityId = (int) ($arguments['entityId'] ?? 0);

        $entity = $this->getEntity($module, $entityId);
        if (! $entity) {
            return ToolResult::error("Entity not found: {$module} ID {$entityId}");
        }

        $values = [
            'text' => $arguments['text'],
            'father' => 0,
            'status' => ($arguments['status'] ?? ''),
        ];

        $result = $this->commentsService->addComment($values, $module, $entityId, $entity);

        if ($result) {
            return ToolResult::text("Comment added successfully to {$module} #{$entityId}");
        }

        return ToolResult::error('Failed to add comment. Please check the provided information.');
    }

    /**
     * Helper method to get an entity based on module type and ID.
     */
    private function getEntity(string $module, int $entityId): mixed
    {
        return match ($module) {
            'ticket' => $this->ticketService->getTicket($entityId),
            'project' => $this->projectService->getProject($entityId),
            'goal', 'goalcanvas', 'goalcanvasitem' => $this->goalcanvasService->getSingleCanvas($entityId),
            default => ['id' => $entityId],
        };
    }
}
