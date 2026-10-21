<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Update an existing goal.
 */
#[IsIdempotent]
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

    public function name(): string
    {
        return 'editGoal';
    }

    public function title(): string
    {
        return 'Edit Goal';
    }

    public function description(): string
    {
        return 'Updates an existing goal with the specified details.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $id = (int) ($arguments['id'] ?? 0);
        if ($id <= 0) {
            return Response::error('A valid goal id is required.');
        }

        $optionalFields = [
            'title', 'description', 'startValue', 'currentValue', 'endValue',
            'startDate', 'endDate', 'milestoneId', 'metricType', 'status',
        ];

        $params = [];
        foreach ($optionalFields as $field) {
            $value = $arguments[$field] ?? null;
            if ($value !== null) {
                $params[$field] = $value;
            }
        }

        if ($params === []) {
            return Response::error('Provide at least one field to update.');
        }

        try {
            $updated = $this->goalcanvasService->patchGoalItem($id, $params);
        } catch (AuthorizationException) {
            // Unknown, foreign, or unauthorized goal id — one message for all three, so the
            // response does not leak whether a goal id exists in another project.
            return Response::error("Goal with ID {$id} not found.");
        }

        if ($updated) {
            return Response::text('Goal updated successfully.');
        }

        return Response::error('Failed to update goal.');
    }
}
