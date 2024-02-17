<?php

/**
 * editEvent Class - Add a new client
 *
 */

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Core\Frontcontroller;
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
        $result = $this->calendarService->editEvent($params);

        if ($result === true) {
            $this->tpl->setNotification('notification.event_edited_successfully', 'success');
        } else {
            $this->tpl->setNotification('notification.please_enter_title', 'error');
        }

        return Frontcontroller::redirect(BASE_URL . '/calendar/editEvent/' . $params['id']);
    }
}
