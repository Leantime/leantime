<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Delete a calendar event.
 */
#[IsDestructive]
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

    public function name(): string
    {
        return 'deleteEvent';
    }

    public function title(): string
    {
        return 'Delete Event';
    }

    public function description(): string
    {
        return 'Deletes a calendar event.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $id = (int) ($arguments['id'] ?? 0);
        $result = $this->calendarService->delEvent($id);

        if ($result) {
            return Response::text('Event deleted successfully.');
        }

        return Response::error('Failed to delete event.');
    }
}
