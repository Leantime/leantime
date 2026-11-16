<?php

namespace Leantime\Domain\Calendar\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Core\Exceptions\NotFoundException;
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
     * URL are the credential. Every failure is answered with a plain 404. A redirect to the
     * 404 page would send a logged-out client on to the login page, which a calendar app can't use.
     *
     * A malformed token or hashes that match no feed are expected: a calendar app keeps polling
     * a feed whose secret was regenerated or whose owner was deleted. Those are not logged, so
     * they don't fill the error log every poll. Anything else (an error building the feed) is.
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
        } catch (NotFoundException|MissingParameterException) {
            return $this->notFound();
        } catch (\Exception $e) {
            Log::error($e);

            return $this->notFound();
        }
    }

    /**
     * The plain-text 404 a calendar app gets for a feed it can't have.
     */
    private function notFound(): Response
    {
        return new Response('Not Found', Response::HTTP_NOT_FOUND, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
