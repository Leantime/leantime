<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Permissions\CalendarPermissions;
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
    #[RequiresPermission(CalendarPermissions::CREATE)]
    public function get(array $params): Response
    {
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
    #[RequiresPermission(CalendarPermissions::CREATE)]
    public function post(array $params): Response
    {
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
