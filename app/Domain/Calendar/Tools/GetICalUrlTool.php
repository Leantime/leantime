<?php

namespace Leantime\Domain\Calendar\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Get the iCal URL for the user calendar.
 */
#[IsReadOnly]
class GetICalUrlTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema;
    }

    public function name(): string
    {
        return 'getICalUrl';
    }

    public function description(): string
    {
        return 'Gets the iCal URL for the user calendar.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        try {
            $url = $this->calendarService->getICalUrl();

            return ToolResult::text($url);
        } catch (\Exception $e) {
            return ToolResult::error('No iCal URL available. Generate one first using generateIcalHash.');
        }
    }
}
