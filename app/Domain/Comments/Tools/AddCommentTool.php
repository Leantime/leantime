<?php

namespace Leantime\Domain\Comments\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Add a new comment to a specific entity.
 */
#[Name('addComment')]
#[Description('Adds a new comment to a specific entity. When adding a comment to a project, you can include a status indicator (green, yellow, red) to create a project status update.')]
class AddCommentTool extends Tool
{
    public function __construct(
        private Comments $commentsService,
        private Projects $projectService,
        private Tickets $ticketService,
        private Goalcanvas $goalcanvasService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'text' => $schema->string()
                ->description('Comment text.')
                ->required(),
            'module' => $schema->string()
                ->description('Module type (ticket, project, goal, etc.).')
                ->required(),
            'entityId' => $schema->integer()
                ->description('ID of the entity to add comment to.')
                ->required(),
            'status' => $schema->string()
                ->description('Status indicator for project updates (green, yellow, red). Only used for project comments.'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $module = $request->string('module');
        $entityId = $request->integer('entityId');

        $entity = $this->getEntity($module, $entityId);
        if (! $entity) {
            return Response::error("Entity not found: {$module} ID {$entityId}");
        }

        $values = [
            'text' => $request->string('text'),
            'father' => 0,
            'status' => $request->string('status', ''),
        ];

        $result = $this->commentsService->addComment($values, $module, $entityId, $entity);

        if ($result) {
            return Response::text("Comment added successfully to {$module} #{$entityId}");
        }

        return Response::error('Failed to add comment. Please check the provided information.');
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
