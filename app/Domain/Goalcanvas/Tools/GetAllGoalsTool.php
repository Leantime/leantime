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
 * Get all goals for a project.
 */
#[Name('getAllGoals')]
#[Description('Gets all goals for a specific project. Goals are used to track OKRs and other measurable objectives.')]
#[IsReadOnly]
class GetAllGoalsTool extends Tool
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
            'projectId' => $schema->integer()
                ->description('Project ID to get goals for.')
                ->required(),
            'boardId' => $schema->integer()
                ->description('Specific goal board ID to filter by.'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->integer('projectId');
        $boardId = $request->get('boardId');

        $goals = $this->goalcanvasService->pollGoals($projectId, $boardId);

        if (empty($goals)) {
            return Response::text("No goals found for project ID: {$projectId}");
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

        return Response::text($response);
    }
}
