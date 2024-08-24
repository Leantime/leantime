<?php

/**
 * newClient Class - Add a new client
 *
 */

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Services\Calendar;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class AddEvent extends Controller
{
    private Calendar $calendarService;

    /**
     * init - initialize private variables
     *
     * @param Calendar $calendarService
     * @return void
     */
    public function init(Calendar $calendarService): void
    {
        $this->calendarService = $calendarService;
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
    }

    /**
     * @param array $params
     *
     * @return Response
     */
    public function get(array $params): Response
    {
        $values = array(
            'description' => '',
            'dateFrom' => '',
            'dateTo' => '',
            'allDay' => '',
        );

        $this->tpl->assign('values', $values);
        return $this->tpl->displayPartial('calendar.addEvent');
    }

    /**
     * @param array $params
     *
     * @return Response
     */
    public function post(array $params): Response
    {

        //Time comes in as 24:00 time from html5 element. Make it user date format
        $params['timeFrom'] = format(value: $params['timeFrom'], fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $params['timeTo'] = format(value: $params['timeTo'], fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $result = $this->calendarService->addEvent($params);

        if (is_numeric($result) === true) {
            $this->tpl->setNotification('notification.event_created_successfully', 'success');

            return Frontcontroller::redirect(BASE_URL . "/calendar/editEvent/" . $result);
        } else {
            $this->tpl->setNotification('notification.please_enter_title', 'error');
            $this->tpl->assign('values', $params);

            return $this->tpl->displayPartial('calendar.addEvent');
        }
    }
}
