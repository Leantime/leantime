<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class ExternalCal extends Controller
{
    private CalendarService $calendarService;

    /**
     * Initializes dependencies.
     */
    public function init(CalendarService $calendarService): void
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Serves an external calendar's iCal content, with session-based caching.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        $content = $this->calendarService->getCachedExternalCalendarContent(
            (int) ($params['id'] ?? 0),
            (int) session('userdata.id')
        );

        return new Response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }
}
