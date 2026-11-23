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
 * CalendarSettings Controller - Displays calendar settings modal and saves the personal
 * "Show planned days on my calendar" setting (on by default).
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
        $this->tpl->assign('showPlannedDays', $this->calendarService->getShowPlannedDays());

        return $this->tpl->displayPartial('calendar.calendarSettings');
    }

    /**
     * Saves "Show planned days on my calendar", then shows the settings again.
     */
    #[RequiresPermission(CalendarPermissions::VIEW)]
    public function post(array $params): Response
    {
        if (isset($params['savePlannedDays'])) {
            $this->calendarService->saveShowPlannedDays(! empty($params['showPlannedDays']));
            $this->tpl->setNotification($this->language->__('notifications.calendar_settings_saved'), 'success');
        }

        return $this->get($params);
    }
}
