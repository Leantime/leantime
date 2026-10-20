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
 * Get detailed information about a specific goal.
 */
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

    public function name(): string
    {
        return 'getGoal';
    }

    public function title(): string
    {
        return 'Get Goal';
    }

    public function description(): string
    {
        return 'Gets detailed information about a specific goal by its ID.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $goalId = (int) ($arguments['goalId'] ?? 0);
        $goal = $this->goalcanvasService->getGoalItem($goalId);

        if (! $goal) {
            return Response::error("Goal with ID {$goalId} not found.");
        }

        // Milestones come from the tracked_by edge model (many-to-many), not
        // the frozen legacy milestoneId column — a goal can have several, and
        // the column goes stale after edits.
        $milestones = array_map(
            static fn (array $m) => ($m['headline'] ?? '').' ('.((int) ($m['percentDone'] ?? 0)).'%, '.($m['statusType'] ?? 'NEW').')',
            $this->goalcanvasService->getGoalMilestones($goalId)['milestones']
        );

        $response = "## Goal Details\n";
        $result = [
            'id' => $goal['id'],
            'title' => Str::sanitizeForLLM($goal['title']),
            'description' => Str::sanitizeForLLM($goal['description']),
            'board' => Str::sanitizeForLLM($goal['boardTitle'] ?? ''),
            'startValue' => $goal['startValue'],
            'currentValue' => $goal['currentValue'],
            'endValue' => $goal['endValue'],
            'metricType' => $goal['metricType'],
            'status' => $goal['status'],
            'startDate' => $goal['startDate'],
            'endDate' => $goal['endDate'],
            'milestones' => $milestones !== [] ? Str::sanitizeForLLM(implode('; ', $milestones)) : 'None',
            'author' => $goal['authorFirstname'].' '.$goal['authorLastname'],
            'created' => $goal['created'],
        ];
        $response .= Str::toMarkdown($result)."\n";

        return Response::text($response);
    }
}
