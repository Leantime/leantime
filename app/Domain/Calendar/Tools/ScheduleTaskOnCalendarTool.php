<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Schedule a task on the calendar by setting editFrom and editTo fields.
 */
#[Name('scheduleTaskOnCalendar')]
#[Description('Schedules a task by setting the editFrom and editTo fields which will show up on the calendar. Also known as timeboxing. Task scheduling is used to show the user their tasks on the calendar and ical exports. It is the primary indicator to know when a task should be worked on. A task can only have one scheduled block at a time. If a task is already scheduled, the editFrom and editTo fields will be updated. If a task is too large for a given timeslot you can suggest to break the task down into multiple subtasks.')]
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

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->integer('id');
        $editFrom = $request->string('editFrom');
        $editTo = $request->string('editTo');

        if ($this->ticketService->patch($id, ['editFrom' => $editFrom, 'editTo' => $editTo])) {
            return Response::text('Task scheduled successfully.');
        }

        return Response::error('Failed to schedule task.');
    }
}
