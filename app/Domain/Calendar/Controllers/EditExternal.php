<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Permissions\CalendarPermissions;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class EditExternal extends Controller
{
    private CalendarService $calendarService;

    /**
     * Initializes dependencies.
     */
    public function init(CalendarService $calendarService): void
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Displays the edit external calendar form.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(CalendarPermissions::EDIT)]
    public function get(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $calendar = $this->calendarService->getExternalCalendar((int) $params['id'], session('userdata.id'));

        $this->tpl->assign('values', $calendar);

        return $this->tpl->displayPartial('calendar.editExternalCalendar');
    }

    /**
     * Handles external calendar update.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(CalendarPermissions::EDIT)]
    public function post(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];
        $calendar = $this->calendarService->getExternalCalendar($id, session('userdata.id'));
        $values = $calendar;

        if (isset($_POST['save'])) {
            $values = [
                'id' => $calendar['id'],
                'url' => $_POST['url'],
                'name' => $_POST['name'],
                'colorClass' => $_POST['colorClass'],
            ];

            $this->calendarService->editExternalCalendar($values, $id);
            $this->tpl->setNotification('notification.external_calendar_edited', 'success', 'externalCalendar_edited');
        }

        $this->tpl->assign('values', $values);

        return $this->tpl->displayPartial('calendar.editExternalCalendar');
    }
}
