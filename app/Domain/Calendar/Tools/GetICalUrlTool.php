<?php

namespace Leantime\Domain\Calendar\Tools;

use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
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

    public function name(): string
    {
        return 'getICalUrl';
    }

    public function title(): string
    {
        return 'Get iCal URL';
    }

    public function description(): string
    {
        return 'Gets the iCal URL for the user calendar.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(): Response
    {
        try {
            $url = $this->calendarService->getICalUrl();

            return Response::text($url);
        } catch (\Exception $e) {
            return Response::error('No iCal URL available. Generate one first using generateIcalHash.');
        }
    }
}
