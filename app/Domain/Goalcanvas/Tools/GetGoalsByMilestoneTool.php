<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
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

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'milestoneId' => $schema->integer()
                ->description('ID of the milestone to get goals for.')
                ->required(),
        ];
    }

    public function name(): string
    {
        return 'getGoalsByMilestone';
    }

    public function title(): string
    {
        return 'Get Goals By Milestone';
    }

    public function description(): string
    {
        return 'Gets all goals associated with a specific milestone.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $milestoneId = (int) ($arguments['milestoneId'] ?? 0);
        $goals = $this->goalcanvasService->getGoalsByMilestone($milestoneId);

        if (empty($goals)) {
            return Response::text("No goals found for milestone ID: {$milestoneId}");
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

        return Response::text($response);
    }
}
