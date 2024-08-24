<?php

/**
 * editEvent Class - Add a new client
 *
 */

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class EditEvent extends Controller
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
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
        $this->calendarService = $calendarService;
    }

    /**
     * retrieves edit calendar event page data
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     */
    public function get(array $params): Response
    {
        $values = $this->calendarService->getEvent($params['id']);

        $this->tpl->assign('values', $values);

        return $this->tpl->displayPartial('calendar.editEvent');
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
        $params['id'] = $_GET['id'] ?? null;

        //Time comes in as 24:00 time from html5 element. Make it user date format
        $params['timeFrom'] = format(value: $params['timeFrom'], fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $params['timeTo'] = format(value: $params['timeTo'], fromFormat: FromFormat::User24hTime)->userTime24toUserTime();

        $result = $this->calendarService->editEvent($params);

        if ($result === true) {
            $this->tpl->setNotification('notification.event_edited_successfully', 'success');
        } else {
            $this->tpl->setNotification('notification.please_enter_title', 'error');
        }

        return Frontcontroller::redirect(BASE_URL . '/calendar/editEvent/' . $params['id']);
    }
}
