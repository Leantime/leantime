<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Get all child goals associated with a parent goal (KPI).
 */
#[IsReadOnly]
class GetChildGoalsTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('parentId')->description('ID of the parent goal to get children for.')
            ->required();
    }

    public function name(): string
    {
        return 'getChildGoals';
    }

    public function description(): string
    {
        return 'Gets all child goals associated with a parent goal.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $parentId = (int) ($arguments['parentId'] ?? 0);
        $childGoals = $this->goalcanvasService->getChildrenbyKPI($parentId);

        if (empty($childGoals)) {
            return ToolResult::text("No child goals found for parent goal ID: {$parentId}");
        }

        $response = "## Child Goals for Parent ID: {$parentId}\n";
        foreach ($childGoals as $goal) {
            $result = [
                'id' => $goal['id'],
                'title' => Str::sanitizeForLLM($goal['title']),
                'startValue' => $goal['startValue'],
                'currentValue' => $goal['currentValue'],
                'endValue' => $goal['endValue'],
                'metricType' => $goal['metricType'],
                'boardTitle' => Str::sanitizeForLLM($goal['boardTitle']),
                'canvasId' => $goal['canvasId'],
                'projectName' => Str::sanitizeForLLM($goal['projectName']),
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return ToolResult::text($response);
    }
}
