<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * CalendarSettings Controller - Displays calendar settings modal.
 *
 * Plugins can register additional settings sections via the filter:
 * 'leantime.domain.calendar.controllers.calendarsettings.get.calendarSettings.sections'
 */
class CalendarSettings extends Controller
{
    private CalendarRepository $calendarRepo;

    /**
     * Initialize the controller.
     */
    public function init(CalendarRepository $calendarRepo): void
    {
        $this->calendarRepo = $calendarRepo;
    }

    /**
     * Display the Calendar Settings modal.
     */
    public function get(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        // Get external calendars for the current user
        $externalCalendars = $this->calendarRepo->getMyExternalCalendars(session('userdata.id'));

        // Plugins can add their settings sections via this filter
        $pluginSections = self::dispatchFilter('calendarSettings.sections', []);

        $this->tpl->assign('externalCalendars', $externalCalendars);
        $this->tpl->assign('pluginSections', $pluginSections);

        return $this->tpl->displayPartial('calendar.calendarSettings');
    }
}
