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
 * Create a new goal.
 */
#[Name('createGoal')]
#[Description('Creates a new goal with the specified details. Goals can be linked to milestones to track progress.')]
class CreateGoalTool extends Tool
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
            'title' => $schema->string()
                ->description('Title of the goal.')
                ->required(),
            'description' => $schema->string()
                ->description('Description of what the goal is measuring.')
                ->required(),
            'startValue' => $schema->number()
                ->description('Starting value for the goal metric.')
                ->required(),
            'currentValue' => $schema->number()
                ->description('Current value of the goal metric.')
                ->required(),
            'endValue' => $schema->number()
                ->description('Target value for the goal metric.')
                ->required(),
            'canvasId' => $schema->integer()
                ->description('Canvas ID this goal belongs to.')
                ->required(),
            'startDate' => $schema->string()
                ->description('Start date in ISO8601 format.'),
            'endDate' => $schema->string()
                ->description('End date in ISO8601 format.'),
            'milestoneId' => $schema->integer()
                ->description('ID of a milestone to attach to this goal.'),
            'metricType' => $schema->string()
                ->description('Type of metric (e.g., "percent", "currency", "number").')
                ->default('number'),
            'status' => $schema->string()
                ->description('Status of the goal (e.g., "status_ontrack", "status_atrisk", "status_miss").')
                ->default('status_ontrack'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $values = [
            'title' => $request->string('title'),
            'description' => $request->string('description'),
            'box' => 'goal',
            'author' => session('userdata.id'),
            'canvasId' => $request->integer('canvasId'),
            'startValue' => $request->get('startValue'),
            'currentValue' => $request->get('currentValue'),
            'endValue' => $request->get('endValue'),
            'metricType' => $request->string('metricType', 'number'),
            'status' => $request->string('status', 'status_ontrack'),
        ];

        $startDate = $request->get('startDate');
        if ($startDate !== null) {
            $values['startDate'] = $startDate;
        }

        $endDate = $request->get('endDate');
        if ($endDate !== null) {
            $values['endDate'] = $endDate;
        }

        $milestoneId = $request->get('milestoneId');
        if ($milestoneId !== null) {
            $values['milestoneId'] = $milestoneId;
        }

        $goalId = $this->goalcanvasService->createGoal($values);

        if ($goalId) {
            return Response::text("Goal created successfully with ID: {$goalId}");
        }

        return Response::error('Failed to create goal. Please check the provided information.');
    }
}
