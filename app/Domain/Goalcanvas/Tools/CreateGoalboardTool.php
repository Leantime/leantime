<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Create a new goal board.
 */
#[Name('createGoalboard')]
#[Description('Creates a new goal board for organizing multiple related goals within a project.')]
class CreateGoalboardTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()
                ->description('Title of the goal board.')
                ->required(),
            'projectId' => $schema->integer()
                ->description('Project ID this goal board belongs to.')
                ->required(),
            'description' => $schema->string()
                ->description('Description of the goal board.'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $values = [
            'title' => $request->string('title'),
            'description' => $request->string('description', ''),
            'projectId' => $request->integer('projectId'),
            'author' => session('userdata.id'),
        ];

        $boardId = $this->goalcanvasService->createGoalboard($values);

        if ($boardId) {
            return Response::text("Goal board created successfully with ID: {$boardId}");
        }

        return Response::error('Failed to create goal board. Please check the provided information.');
    }
}
