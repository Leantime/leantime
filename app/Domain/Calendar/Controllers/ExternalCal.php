<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class ExternalCal extends Controller
{
    private CalendarService $calendarService;

    private int $cacheTime = 60 * 30; // 30min

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
        $calId = $params['id'] ?? '';

        if (! session()->exists('calendarCache')) {
            session(['calendarCache' => []]);
        }

        $content = '';
        if (session()->exists('calendarCache.'.$calId) && session()->exists('calendarCache.'.$calId.'.lastUpdate') && session('calendarCache.'.$calId.'.lastUpdate') > time() - $this->cacheTime) {
            $content = session('calendarCache.'.$calId.'.content');
        } else {
            $cal = $this->calendarService->getExternalCalendar((int) $calId, session('userdata.id'));

            if (isset($cal['url'])) {
                try {
                    // Use the service's loadIcalUrl which includes SSRF protection
                    $content = $this->calendarService->loadIcalUrl($cal['url']);
                    session(['calendarCache.'.$calId.'.lastUpdate' => time()]);
                    session(['calendarCache.'.$calId.'.content' => $content]);
                } catch (\Exception $e) {
                    $content = '';
                }
            }
        }

        return new Response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }
}
