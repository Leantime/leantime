<?php

namespace Leantime\Domain\Calendar\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Delete a calendar event.
 */
class DeleteCalendarEventTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('id')->description('Event ID to delete.')
            ->required();
    }

    public function name(): string
    {
        return 'deleteEvent';
    }

    public function description(): string
    {
        return 'Deletes a calendar event.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $id = (int) ($arguments['id'] ?? 0);
        $result = $this->calendarService->delEvent($id);

        if ($result) {
            return ToolResult::text('Event deleted successfully.');
        }

        return ToolResult::error('Failed to delete event.');
    }
}
