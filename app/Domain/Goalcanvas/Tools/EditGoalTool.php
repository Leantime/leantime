<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Update an existing goal.
 */
class EditGoalTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('id')->description('ID of the goal to update.')
            ->required()
            ->string('title')->description('Updated title of the goal.')
            ->string('description')->description('Updated description of what the goal is measuring.')
            ->number('startValue')->description('Updated starting value for the goal metric.')
            ->number('currentValue')->description('Updated current value of the goal metric.')
            ->number('endValue')->description('Updated target value for the goal metric.')
            ->string('startDate')->description('Updated start date in ISO8601 format.')
            ->string('endDate')->description('Updated end date in ISO8601 format.')
            ->integer('milestoneId')->description('Updated ID of a milestone to attach to this goal.')
            ->string('metricType')->description('Updated type of metric (e.g., "percent", "currency", "number").')
            ->string('status')->description('Updated status of the goal (e.g., "status_ontrack", "status_atrisk", "status_miss").');
    }

    public function name(): string
    {
        return 'editGoal';
    }

    public function description(): string
    {
        return 'Updates an existing goal with the specified details.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $id = (int) ($arguments['id'] ?? 0);
        if ($id <= 0) {
            return ToolResult::error('A valid goal id is required.');
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
            return ToolResult::error('Provide at least one field to update.');
        }

        try {
            $updated = $this->goalcanvasService->patchGoalItem($id, $params);
        } catch (AuthorizationException) {
            // Unknown, foreign, or unauthorized goal id — one message for all three, so the
            // response does not leak whether a goal id exists in another project.
            return ToolResult::error("Goal with ID {$id} not found.");
        }

        if ($updated) {
            return ToolResult::text('Goal updated successfully.');
        }

        return ToolResult::error('Failed to update goal.');
    }
}
