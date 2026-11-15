<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Events\CalendarSettingsSectionsFilter;
use Leantime\Domain\Calendar\Events\ExternalCalendarsFilter;
use Leantime\Domain\Calendar\Permissions\CalendarPermissions;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

/**
 * CalendarSettings Controller - Displays calendar settings modal.
 *
 * Plugins add settings sections via {@see CalendarSettingsSectionsFilter} and add or mark their
 * calendars via {@see ExternalCalendarsFilter}.
 */
class CalendarSettings extends Controller
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
     * Display the Calendar Settings modal.
     */
    #[RequiresPermission(CalendarPermissions::VIEW)]
    public function get(array $params): Response
    {
        // Get external calendars for the current user
        $externalCalendars = $this->calendarService->getMyExternalCalendars((int) session('userdata.id'));

        $externalCalendars = ExternalCalendarsFilter::dispatch(calendars: $externalCalendars ?: [], inSettings: true);
        $pluginSections = CalendarSettingsSectionsFilter::dispatch();

        $this->tpl->assign('externalCalendars', $externalCalendars);
        $this->tpl->assign('pluginSections', $pluginSections);

        return $this->tpl->displayPartial('calendar.calendarSettings');
    }
}
