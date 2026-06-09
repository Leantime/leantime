<?php

/**
 * editEvent Class - Add a new client
 */

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Calendar\Permissions\CalendarPermissions;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class EditEvent extends Controller
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
     * retrieves edit calendar event page data
     */
    #[RequiresPermission(CalendarPermissions::EDIT)]
    public function get(array $params): Response
    {
        $values = $this->calendarService->getEvent($params['id']);

        $this->tpl->assign('values', $values);

        return $this->tpl->displayPartial('calendar.editEvent');
    }

    /**
     * sets, creates, and updates edit calendar event page data
     */
    #[RequiresPermission(CalendarPermissions::EDIT)]
    public function post(array $params): Response
    {
        $params['id'] = $_GET['id'] ?? null;

        // Time comes in as 24:00 time from html5 element. Make it user date format
        $params['timeFrom'] = format(value: $params['timeFrom'], fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $params['timeTo'] = format(value: $params['timeTo'], fromFormat: FromFormat::User24hTime)->userTime24toUserTime();

        $result = $this->calendarService->editEvent($params);

        if ($result === true) {
            $this->tpl->setNotification('notification.event_edited_successfully', 'success');
        } else {
            $this->tpl->setNotification('notification.please_enter_title', 'error');
        }

        return Frontcontroller::redirect(BASE_URL.'/calendar/editEvent/'.$params['id']);
    }
}
