<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Calendar\Events\CalendarProvidersFilter;
use Leantime\Domain\Calendar\Permissions\CalendarPermissions;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ConnectCalendar Controller - Displays extensible modal for connecting external calendars.
 *
 * Plugins add calendar providers via {@see CalendarProvidersFilter}.
 */
class ConnectCalendar extends Controller
{
    private CalendarService $calendarService;

    /**
     * Initialize the controller with dependencies.
     */
    public function init(CalendarService $calendarService): void
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Display the Connect Calendar modal with available providers.
     */
    #[RequiresPermission(CalendarPermissions::CREATE)]
    public function get(array $params): Response
    {
        $providers = CalendarProvidersFilter::dispatch();

        $this->tpl->assign('providers', $providers);

        return $this->tpl->displayPartial('calendar.connectCalendar');
    }

    /**
     * Handle iCal calendar import submission.
     */
    #[RequiresPermission(CalendarPermissions::CREATE)]
    public function post(array $params): Response
    {
        if (isset($params['name']) || isset($params['url'])) {
            $values = [
                'url' => $params['url'] ?? '',
                'name' => $params['name'] ?? 'My Calendar',
                'colorClass' => $params['colorClass'] ?? '#082236',
            ];

            $this->calendarService->addExternalCalendarUrl($values);
            $this->tpl->setNotification('notification.gcal_imported_successfully', 'success', 'externalcalendar_created');
        }

        return FrontcontrollerCore::redirect(BASE_URL.'/calendar/showMyCalendar');
    }
}
