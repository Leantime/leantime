<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Symfony\Component\HttpFoundation\Response;

class ShowAllGCals extends Controller
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
     * @throws \Exception
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        // Assign vars
        $this->tpl->assign('allCalendars', $this->calendarRepo->getMyGoogleCalendars());

        return $this->tpl->display('calendar.showAllGCals');
    }
}
