<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Create a new goal.
 */
class CreateGoalTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('title')->description('Title of the goal.')
            ->required()
            ->string('description')->description('Description of what the goal is measuring.')
            ->required()
            ->number('startValue')->description('Starting value for the goal metric.')
            ->required()
            ->number('currentValue')->description('Current value of the goal metric.')
            ->required()
            ->number('endValue')->description('Target value for the goal metric.')
            ->required()
            ->integer('canvasId')->description('Canvas ID this goal belongs to.')
            ->required()
            ->string('startDate')->description('Start date in ISO8601 format.')
            ->string('endDate')->description('End date in ISO8601 format.')
            ->integer('milestoneId')->description('ID of a milestone to attach to this goal.')
            ->string('metricType')->description('Type of metric (e.g., "percent", "currency", "number").')
            ->string('status')->description('Status of the goal (e.g., "status_ontrack", "status_atrisk", "status_miss").');
    }

    public function name(): string
    {
        return 'createGoal';
    }

    public function description(): string
    {
        return 'Creates a new goal with the specified details.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $values = [
            'title' => $arguments['title'],
            'description' => $arguments['description'],
            'box' => 'goal',
            'author' => session('userdata.id'),
            'canvasId' => (int) ($arguments['canvasId'] ?? 0),
            'startValue' => ($arguments['startValue'] ?? null),
            'currentValue' => ($arguments['currentValue'] ?? null),
            'endValue' => ($arguments['endValue'] ?? null),
            'metricType' => ($arguments['metricType'] ?? 'number'),
            'status' => ($arguments['status'] ?? 'status_ontrack'),
        ];

        $startDate = ($arguments['startDate'] ?? null);
        if ($startDate !== null) {
            $values['startDate'] = $startDate;
        }

        $endDate = ($arguments['endDate'] ?? null);
        if ($endDate !== null) {
            $values['endDate'] = $endDate;
        }

        $milestoneId = ($arguments['milestoneId'] ?? null);
        if ($milestoneId !== null) {
            $values['milestoneId'] = $milestoneId;
        }

        $goalId = $this->goalcanvasService->createGoal($values);

        if ($goalId) {
            return ToolResult::text("Goal created successfully with ID: {$goalId}");
        }

        return ToolResult::error('Failed to create goal. Please check the provided information.');
    }
}
