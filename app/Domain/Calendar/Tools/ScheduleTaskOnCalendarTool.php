<?php

namespace Leantime\Domain\Calendar\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Schedule a task on the calendar by setting editFrom and editTo fields.
 */
class ScheduleTaskOnCalendarTool extends Tool
{
    public function __construct(
        private Tickets $ticketService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('id')->description('ID of the task to schedule.')
            ->required()
            ->string('editFrom')->description('Date time string of when the task should start in user timezone in ISO8601 format (example: 2024-04-30T15:00:00-04:00).')
            ->required()
            ->string('editTo')->description('Date time string of when the task should end in user timezone in ISO8601 format (example: 2024-04-30T15:00:00-04:00).')
            ->required();
    }

    public function name(): string
    {
        return 'scheduleTaskOnCalendar';
    }

    public function description(): string
    {
        return 'Schedules a task by setting the editFrom and editTo fields.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $id = (int) ($arguments['id'] ?? 0);
        $editFrom = $arguments['editFrom'];
        $editTo = $arguments['editTo'];

        if ($this->ticketService->patch($id, ['editFrom' => $editFrom, 'editTo' => $editTo])) {
            return ToolResult::text('Task scheduled successfully.');
        }

        return ToolResult::error('Failed to schedule task.');
    }
}
