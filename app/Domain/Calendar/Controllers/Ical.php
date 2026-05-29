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
        $actParts = explode('.', $params['act'] ?? '');

        if (is_array($actParts) && count($actParts) === 3) {
            $calId = $actParts[2];
            $idParts = explode('_', $calId);
        } else {
            $calId = $_GET['id'] ?? '';
            $idParts = explode('_', $calId);
        }

        if (count($idParts) != 2) {
            return Frontcontroller::redirect(BASE_URL.'/errors/404');
        }

        try {
            $calendar = $this->calendarService->getIcalByHash($idParts[1], $idParts[0]);

            return new Response($calendar->get(), 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="leantime-calendar.ics"',
            ]);
        } catch (\Exception $e) {
            return Frontcontroller::redirect(BASE_URL.'/errors/404');
        }
    }
}
