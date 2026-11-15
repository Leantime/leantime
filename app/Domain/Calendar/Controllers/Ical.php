<?php

namespace Leantime\Domain\Calendar\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class Ical extends Controller
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
     * Serves the iCal feed for a given calendar hash.
     *
     * The route is public: calendar apps fetch it without a session, and the hashes in the
     * URL are the credential. Any failure (bad token, unknown hashes, an error building the
     * feed) is logged and answered with a plain 404. A redirect to the 404 page would send a
     * logged-out client on to the login page, which a calendar app can't use.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        try {
            $calendar = $this->calendarService->getIcalByRequestToken(
                $_GET['id'] ?? '',
                $params['act'] ?? ''
            );

            return new Response($calendar->get(), 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="leantime-calendar.ics"',
            ]);
        } catch (\Exception $e) {
            Log::error($e);

            return new Response('Not Found', Response::HTTP_NOT_FOUND, ['Content-Type' => 'text/plain; charset=utf-8']);
        }
    }
}
