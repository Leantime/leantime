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
 * Get all child goals associated with a parent goal (KPI).
 */
#[Name('getChildGoals')]
#[Description('Gets all child goals associated with a parent goal (KPI).')]
#[IsReadOnly]
class GetChildGoalsTool extends Tool
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
            'parentId' => $schema->integer()
                ->description('ID of the parent goal to get children for.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $parentId = $request->integer('parentId');
        $childGoals = $this->goalcanvasService->getChildrenbyKPI($parentId);

        if (empty($childGoals)) {
            return Response::text("No child goals found for parent goal ID: {$parentId}");
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

        return Response::text($response);
    }
}
