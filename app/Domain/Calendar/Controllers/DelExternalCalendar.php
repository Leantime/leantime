<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class DelExternalCalendar extends Controller
{
    private CalendarService $calendarService;

    /**
     * init - initialize private variables
     *
     * @param CalendarService $calendarService
     *
     * @return void
     */
    public function init(CalendarService $calendarService): void
    {
        $this->calendarService = $calendarService;
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
    }

    /**
     * retrieves delete calendar event page data
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayPartial('calendar.delExternalCal');
    }

    /**
     * sets, creates, and updates edit calendar event page data
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     */
    public function post(array $params): Response
    {

        if (isset($_GET['id']) === false) {
            return Frontcontroller::redirect(BASE_URL . "/calendar/showMyCalendar/");
        }

        $id = (int)$_GET['id'];

        $result = $this->calendarService->deleteGCal($id);

        if ($result === true) {
            $this->tpl->setNotification('notification.calendar_removed_successfully', 'success');

            return Frontcontroller::redirect(BASE_URL . "/calendar/showMyCalendar/");
        } else {
            $this->tpl->setNotification('notification.could_not_delete_calendar', 'error');

            return $this->tpl->displayPartial('calendar.delEvent');
        }
    }
}
