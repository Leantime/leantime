<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
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
            return Frontcontroller::redirect(BASE_URL.'/errors/404');
        }
    }
}
