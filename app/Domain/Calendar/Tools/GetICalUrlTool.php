<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Get the iCal URL for the user calendar.
 */
#[Name('getICalUrl')]
#[Description('Gets the iCal URL for the user calendar which can be used to subscribe to their calendar in external apps.')]
#[IsReadOnly]
class GetICalUrlTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        try {
            $url = $this->calendarService->getICalUrl();

            return Response::text($url);
        } catch (\Exception $e) {
            return Response::error('No iCal URL available. Generate one first using generateIcalHash.');
        }
    }
}
