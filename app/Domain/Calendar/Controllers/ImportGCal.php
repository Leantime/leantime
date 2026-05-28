<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class ImportGCal extends Controller
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
     * Displays the import calendar form.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $this->tpl->assign('values', [
            'url' => '',
            'name' => '',
            'colorClass' => '',
        ]);

        return $this->tpl->displayPartial('calendar.importGCal');
    }

    /**
     * Handles external calendar URL import.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $values = [
            'url' => $_POST['url'] ?? '',
            'name' => $_POST['name'] ?? 'My Calendar',
            'colorClass' => $_POST['colorClass'] ?? '#082236',
        ];

        if (isset($_POST['name']) || isset($_POST['url'])) {
            $this->calendarService->addExternalCalendarUrl($values);
            $this->tpl->setNotification('notification.gcal_imported_successfully', 'success', 'externalcalendar_created');
        }

        $this->tpl->assign('values', $values);

        return $this->tpl->displayPartial('calendar.importGCal');
    }
}
