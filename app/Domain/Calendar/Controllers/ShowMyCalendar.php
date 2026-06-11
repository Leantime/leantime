<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Permissions\CalendarPermissions;
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
    #[RequiresPermission(CalendarPermissions::VIEW)]
    public function get(array $params): Response
    {
        $this->tpl->assign('calendar', $this->calendarService->getCalendar(session('userdata.id')));

        session(['lastPage' => BASE_URL.'/calendar/showMyCalendar/']);

        $externalCalendars = $this->calendarService->getMyExternalCalendars(session('userdata.id'));
        $externalCalendars = self::dispatch_filter('showMyCalendar.externalCalendars', $externalCalendars);
        $this->tpl->assign('externalCalendars', $externalCalendars);

        return $this->tpl->display('calendar.showMyCalendar');
    }
}
