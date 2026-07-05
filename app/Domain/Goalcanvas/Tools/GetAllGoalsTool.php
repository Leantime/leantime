<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Get all goals for a project.
 */
#[IsReadOnly]
class GetAllGoalsTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('projectId')->description('Project ID to get goals for.')
            ->required()
            ->integer('boardId')->description('Specific goal board ID to filter by.');
    }

    public function name(): string
    {
        return 'getAllGoals';
    }

    public function description(): string
    {
        return 'Gets all goals for a specific project.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $boardId = ($arguments['boardId'] ?? null);

        $goals = $this->goalcanvasService->pollGoals($projectId, $boardId);

        if (empty($goals)) {
            return ToolResult::text("No goals found for project ID: {$projectId}");
        }

        $response = "## Goals\n";
        foreach ($goals as $goal) {
            $result = [
                'id' => $goal['id'],
                'title' => Str::sanitizeForLLM($goal['title']),
                'description' => Str::sanitizeForLLM($goal['description']),
                'startValue' => $goal['startValue'],
                'currentValue' => $goal['currentValue'],
                'endValue' => $goal['endValue'],
                'metricType' => $goal['metricType'],
                'canvasId' => $goal['canvasId'],
                'milestoneId' => $goal['milestoneId'] ?: 'None',
                'startDate' => $goal['startDate'],
                'endDate' => $goal['endDate'],
                'status' => $goal['status'],
                'setting' => $goal['setting'],
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return ToolResult::text($response);
    }
}
