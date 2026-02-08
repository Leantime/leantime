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
 *
 * Plugins can mark calendars as plugin-managed (hides edit/delete UI) via the filter:
 * 'leantime.domain.calendar.controllers.calendarsettings.get.calendarSettings.externalCalendars'
 *
 * Section array structure:
 *   - id: string (unique identifier)
 *   - icon: string (FontAwesome class or SVG markup)
 *   - iconType: string ('fontawesome'|'svg', default 'fontawesome')
 *   - title: string (section heading)
 *   - description: string (optional description text)
 *   - content: string (raw HTML content - plugin must sanitize)
 *   - actions: array (optional action buttons with url, label, class, icon, type keys)
 */
class CalendarSettings extends Controller
{
    private CalendarRepository $calendarRepo;

    /**
     * Initialize the controller with dependencies.
     */
    public function init(CalendarRepository $calendarRepo): void
    {
        $this->calendarRepo = $calendarRepo;
    }

    /**
     * Display the Calendar Settings modal.
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        // Get external calendars for the current user
        $externalCalendars = $this->calendarRepo->getMyExternalCalendars(session('userdata.id'));

        // Plugins can augment calendar data (e.g., add 'managedByPlugin' => true to hide edit/delete)
        $externalCalendars = self::dispatchFilter('calendarSettings.externalCalendars', $externalCalendars);

        // Plugins can add their settings sections via this filter
        $pluginSections = self::dispatchFilter('calendarSettings.sections', []);

        $this->tpl->assign('externalCalendars', $externalCalendars);
        $this->tpl->assign('pluginSections', $pluginSections);

        return $this->tpl->displayPartial('calendar.calendarSettings');
    }
}
