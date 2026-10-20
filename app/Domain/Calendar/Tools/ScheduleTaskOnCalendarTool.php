<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Schedule a task on the calendar by setting editFrom and editTo fields.
 */
#[IsIdempotent]
class ScheduleTaskOnCalendarTool extends Tool
{
    public function __construct(
        private Tickets $ticketService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('ID of the task to schedule.')
                ->required(),
            'editFrom' => $schema->string()
                ->description('Date time string of when the task should start in user timezone in ISO8601 format (example: 2024-04-30T15:00:00-04:00).')
                ->required(),
            'editTo' => $schema->string()
                ->description('Date time string of when the task should end in user timezone in ISO8601 format (example: 2024-04-30T15:00:00-04:00).')
                ->required(),
        ];
    }

    public function name(): string
    {
        return 'scheduleTaskOnCalendar';
    }

    public function title(): string
    {
        return 'Schedule Task On Calendar';
    }

    public function description(): string
    {
        return 'Schedules a task by setting the editFrom and editTo fields.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $id = (int) ($arguments['id'] ?? 0);
        $editFrom = $arguments['editFrom'];
        $editTo = $arguments['editTo'];

        if ($this->ticketService->patch($id, ['editFrom' => $editFrom, 'editTo' => $editTo])) {
            return Response::text('Task scheduled successfully.');
        }

        return Response::error('Failed to schedule task.');
    }
}
