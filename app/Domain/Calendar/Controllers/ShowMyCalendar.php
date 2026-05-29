<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class ShowMyCalendar extends Controller
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
     * Displays the user's calendar view.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $this->tpl->assign('calendar', $this->calendarService->getCalendar(session('userdata.id')));

        session(['lastPage' => BASE_URL.'/calendar/showMyCalendar/']);

        $externalCalendars = $this->calendarService->getMyExternalCalendars(session('userdata.id'));
        $externalCalendars = self::dispatch_filter('showMyCalendar.externalCalendars', $externalCalendars);
        $this->tpl->assign('externalCalendars', $externalCalendars);

        return $this->tpl->display('calendar.showMyCalendar');
    }
}
