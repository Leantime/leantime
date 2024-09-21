<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace Leantime\Domain\Calendar\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Calendar\Services\Calendar;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Ical extends Controller
{
    private Calendar $calendarService;

    /**
     * init - initialize private variables
     *
     * @param Calendar $calendarRepo
     *
     * @return void
     */
    public function init(Calendar $calendarService): void
    {
        $this->calendarService = $calendarService;
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

        $calId = $_GET['id'] ?? "";
        $idParts = explode("_", $calId);

        if (count($idParts) != 2) {
            return Frontcontroller::redirect(BASE_URL . "/errors/404");
        }

        try {

            $calendar = $this->calendarService->getIcalByHash($idParts[1], $idParts[0]);

            return new Response($calendar->get(), 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="leantime-calendar.ics"',
            ]);

        }catch(\Exception $e) {
            return Frontcontroller::redirect(BASE_URL . "/errors/404");
        }


    }
}
