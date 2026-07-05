<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Get detailed information about a specific goal.
 */
#[IsReadOnly]
class GetGoalTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('goalId')->description('ID of the goal to retrieve.')
            ->required();
    }

    public function name(): string
    {
        return 'getGoal';
    }

    public function description(): string
    {
        return 'Gets detailed information about a specific goal by its ID.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $goalId = (int) ($arguments['goalId'] ?? 0);
        $goal = $this->goalcanvasService->getSingleCanvas($goalId);

        if (! $goal) {
            return ToolResult::error("Goal with ID {$goalId} not found.");
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

        return ToolResult::text($response);
    }
}
