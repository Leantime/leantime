<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Get all goals associated with a specific milestone.
 */
#[IsReadOnly]
class GetGoalsByMilestoneTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('milestoneId')->description('ID of the milestone to get goals for.')
            ->required();
    }

    public function name(): string
    {
        return 'getGoalsByMilestone';
    }

    public function description(): string
    {
        return 'Gets all goals associated with a specific milestone.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $milestoneId = (int) ($arguments['milestoneId'] ?? 0);
        $goals = $this->goalcanvasService->getGoalsByMilestone($milestoneId);

        if (empty($goals)) {
            return ToolResult::text("No goals found for milestone ID: {$milestoneId}");
        }

        $response = "## Goals for Milestone ID: {$milestoneId}\n";
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
                'startDate' => $goal['startDate'],
                'endDate' => $goal['endDate'],
                'status' => $goal['status'],
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return ToolResult::text($response);
    }
}
