<?php

/**
 * showAll Class - show My Calender
 */

namespace Leantime\Domain\Calendar\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Symfony\Component\HttpFoundation\Response;

class ShowMyCalendar extends Controller
{
    private CalendarRepository $calendarRepo;

    /**
     * init - initialize private variables
     */
    public function init(CalendarRepository $calendarRepo): void
    {
        $this->calendarRepo = $calendarRepo;
    }

    /**
     * run - display template and edit data
     *
     *
     *
     * @throws BindingResolutionException
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $this->tpl->assign('calendar', $this->calendarRepo->getCalendar(session('userdata.id')));
        // $this->tpl->assign('gCalLink', $this->calendarRepo->getMyGoogleCalendars());

        session(['lastPage' => BASE_URL.'/calendar/showMyCalendar/']);

        $externalCalendars = $this->calendarRepo->getMyExternalCalendars(session('userdata.id'));
        $externalCalendars = self::dispatch_filter('showMyCalendar.externalCalendars', $externalCalendars);
        $this->tpl->assign('externalCalendars', $externalCalendars);

        // @TODO: This should come from the ticket repo...
        // $this->tpl->assign('ticketEditDates', $this->calendarRepo->getTicketEditDates());
        // $this->tpl->assign('ticketWishDates', $this->calendarRepo->getTicketWishDates());
        // $this->tpl->assign('dates', $this->calendarRepo->getAllDates($dateFrom, $dateTo));

        return $this->tpl->display('calendar.showMyCalendar');
    }
}
