<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Create a new goal board.
 */
#[IsDestructive(false)]
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

    public function name(): string
    {
        return 'createGoalboard';
    }

    public function title(): string
    {
        return 'Create Goalboard';
    }

    public function description(): string
    {
        return 'Creates a new goal board for organizing goals within a project.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $values = [
            'title' => $arguments['title'],
            'description' => ($arguments['description'] ?? ''),
            'projectId' => (int) ($arguments['projectId'] ?? 0),
            'author' => session('userdata.id'),
        ];

        $boardId = $this->goalcanvasService->createGoalboard($values);

        if ($boardId) {
            return Response::text("Goal board created successfully with ID: {$boardId}");
        }

        return Response::error('Failed to create goal board. Please check the provided information.');
    }
}
