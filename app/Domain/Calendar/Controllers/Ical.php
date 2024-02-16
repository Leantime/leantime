<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace Leantime\Domain\Calendar\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Core\Frontcontroller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Ical extends Controller
{
    private CalendarRepository $calendarRepo;

    /**
     * init - initialize private variables
     *
     * @param CalendarRepository $calendarRepo
     *
     * @return void
     */
    public function init(CalendarRepository $calendarRepo): void
    {
        $this->calendarRepo = $calendarRepo;
    }

    /**
     * run - display template and edit data
     *
     * @access public
     *
     * @return RedirectResponse|Response
     *
     * @throws BindingResolutionException
     */
    public function run(): RedirectResponse|Response
    {
        $calId = $_GET['id'];

        $idParts = explode("_", $calId);

        if (count($idParts) != 2) {
            return Frontcontroller::redirect(BASE_URL . "/errors/404");
        }

        $calendar = $this->calendarRepo->getCalendarBySecretHash($idParts[1], $idParts[0]);

        $this->tpl->assign("calendar", $calendar);

        header('Content-type: text/calendar; charset=utf-8');
        header('Content-disposition: attachment;filename="leantime.ics"');

        return $this->tpl->display("calendar.ical", "blank");
    }
}
