<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Get detailed information about a specific goal.
 */
#[Name('getGoal')]
#[Description('Gets detailed information about a specific goal by its ID.')]
#[IsReadOnly]
class GetGoalTool extends Tool
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
            'goalId' => $schema->integer()
                ->description('ID of the goal to retrieve.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $goalId = $request->integer('goalId');
        $goal = $this->goalcanvasService->getSingleCanvas($goalId);

        if (! $goal) {
            return Response::error("Goal with ID {$goalId} not found.");
        }

        $response = "## Goal Details\n";
        $result = [
            'id' => $goal['id'],
            'title' => Str::sanitizeForLLM($goal['title']),
            'description' => Str::sanitizeForLLM($goal['description']),
            'projectId' => $goal['projectId'],
            'author' => $goal['authorFirstname'].' '.$goal['authorLastname'],
            'created' => $goal['created'],
        ];
        $response .= Str::toMarkdown($result)."\n";

        return Response::text($response);
    }
}
