<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Update an existing goal.
 */
#[Name('editGoal')]
#[Description('Updates an existing goal with the specified details. You can update any of the goal properties including linking to a milestone.')]
class EditGoalTool extends Tool
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
            'id' => $schema->integer()
                ->description('ID of the goal to update.')
                ->required(),
            'title' => $schema->string()
                ->description('Updated title of the goal.'),
            'description' => $schema->string()
                ->description('Updated description of what the goal is measuring.'),
            'startValue' => $schema->number()
                ->description('Updated starting value for the goal metric.'),
            'currentValue' => $schema->number()
                ->description('Updated current value of the goal metric.'),
            'endValue' => $schema->number()
                ->description('Updated target value for the goal metric.'),
            'startDate' => $schema->string()
                ->description('Updated start date in ISO8601 format.'),
            'endDate' => $schema->string()
                ->description('Updated end date in ISO8601 format.'),
            'milestoneId' => $schema->integer()
                ->description('Updated ID of a milestone to attach to this goal.'),
            'metricType' => $schema->string()
                ->description('Updated type of metric (e.g., "percent", "currency", "number").'),
            'status' => $schema->string()
                ->description('Updated status of the goal (e.g., "status_ontrack", "status_atrisk", "status_miss").'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $values = ['id' => $request->integer('id')];

        $optionalFields = [
            'title', 'description', 'startValue', 'currentValue', 'endValue',
            'startDate', 'endDate', 'milestoneId', 'metricType', 'status',
        ];

        foreach ($optionalFields as $field) {
            $value = $request->get($field);
            if ($value !== null) {
                $values[$field] = $value;
            }
        }

        $this->goalcanvasService->editCanvasItem($values);

        return Response::text('Goal updated successfully.');
    }
}
