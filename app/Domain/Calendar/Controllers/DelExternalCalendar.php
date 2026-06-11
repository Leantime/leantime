<?php

/**
 * delClient Class - Deleting clients
 */

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Calendar\Permissions\CalendarPermissions;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class DelExternalCalendar extends Controller
{
    private CalendarService $calendarService;

    /**
     * init - initialize private variables
     */
    public function init(CalendarService $calendarService): void
    {
        $this->calendarService = $calendarService;
    }

    /**
     * retrieves delete calendar event page data
     */
    #[RequiresPermission(CalendarPermissions::DELETE)]
    public function get(array $params): Response
    {
        return $this->tpl->displayPartial('calendar.delExternalCal');
    }

    /**
     * sets, creates, and updates edit calendar event page data
     */
    #[RequiresPermission(CalendarPermissions::DELETE)]
    public function post(array $params): Response
    {

        if (isset($_GET['id']) === false) {
            return Frontcontroller::redirect(BASE_URL.'/calendar/showMyCalendar/');
        }

        $id = (int) $_GET['id'];

        $result = $this->calendarService->deleteGCal($id);

        if ($result === true) {
            $this->tpl->setNotification('notification.calendar_removed_successfully', 'success');

            return Frontcontroller::redirect(BASE_URL.'/calendar/showMyCalendar/');
        } else {
            $this->tpl->setNotification('notification.could_not_delete_calendar', 'error');

            return $this->tpl->displayPartial('calendar.delEvent');
        }
    }
}
