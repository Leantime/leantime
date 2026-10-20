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
 * Get all goals for a project.
 */
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

    public function name(): string
    {
        return 'getAllGoals';
    }

    public function title(): string
    {
        return 'Get All Goals';
    }

    public function description(): string
    {
        return 'Gets all goals for a specific project.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $projectId = (int) ($arguments['projectId'] ?? 0);
        $boardId = ($arguments['boardId'] ?? null);

        $goals = $this->goalcanvasService->pollGoals($projectId, $boardId);

        if (empty($goals)) {
            return Response::text("No goals found for project ID: {$projectId}");
        }

        // Milestone links come from the tracked_by edge model (many-to-many),
        // hydrated in ONE batched call — the legacy milestoneId column goes
        // stale after edits and misses every link beyond the first.
        $milestonesByGoal = $this->goalcanvasService->getMilestonesForGoals(
            array_map(static fn ($g) => (int) $g['id'], $goals)
        );

        $response = "## Goals\n";
        foreach ($goals as $goal) {
            $milestones = array_map(
                static fn (array $m) => ($m['headline'] ?? '').' ('.((int) ($m['percentDone'] ?? 0)).'%)',
                $milestonesByGoal[(int) $goal['id']] ?? []
            );
            $result = [
                'id' => $goal['id'],
                'title' => Str::sanitizeForLLM($goal['title']),
                'description' => Str::sanitizeForLLM($goal['description']),
                'startValue' => $goal['startValue'],
                'currentValue' => $goal['currentValue'],
                'endValue' => $goal['endValue'],
                'metricType' => $goal['metricType'],
                'canvasId' => $goal['canvasId'],
                'milestones' => $milestones !== [] ? Str::sanitizeForLLM(implode('; ', $milestones)) : 'None',
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
