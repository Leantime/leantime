<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class ShowAllGCals extends Controller
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
     * Displays all external calendars.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $this->tpl->assign('allCalendars', $this->calendarService->getMyExternalCalendars(session('userdata.id')));

        return $this->tpl->display('calendar.showAllGCals');
    }
}
