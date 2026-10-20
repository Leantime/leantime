<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Create a new goal.
 */
#[IsDestructive(false)]
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
                ->description('The goal as an outcome statement, e.g. "Increase early-detection screenings". NOT the metric — must differ from description.')
                ->required(),
            'description' => $schema->string()
                ->description('The metric being tracked, e.g. "Screenings at clinic walk-ins" (shown as "What are you tracking?"). Must differ from the title.')
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
                ->description('Type of metric (e.g., "percent", "currency", "number").'),
            'status' => $schema->string()
                ->description('Status of the goal (e.g., "status_ontrack", "status_atrisk", "status_miss").'),
        ];
    }

    public function name(): string
    {
        return 'createGoal';
    }

    public function title(): string
    {
        return 'Create Goal';
    }

    public function description(): string
    {
        return 'Creates a new goal with the specified details.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

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
            return Response::text("Goal created successfully with ID: {$goalId}");
        }

        return Response::error('Failed to create goal. Please check the provided information.');
    }
}
