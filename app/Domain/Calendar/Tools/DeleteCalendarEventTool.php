<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Delete a calendar event.
 */
#[Name('deleteEvent')]
#[Description('Deletes a calendar event. Note: Can only delete Leantime calendar events, not external ones.')]
class DeleteCalendarEventTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('Event ID to delete.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->integer('id');
        $result = $this->calendarService->delEvent($id);

        if ($result) {
            return Response::text('Event deleted successfully.');
        }

        return Response::error('Failed to delete event.');
    }
}
