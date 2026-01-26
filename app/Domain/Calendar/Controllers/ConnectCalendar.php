<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * ConnectCalendar Controller - Displays extensible modal for connecting external calendars.
 *
 * Plugins can register additional calendar providers via the filter:
 * 'leantime.domain.calendar.controllers.connectcalendar.run.connectOptions.providers'
 */
class ConnectCalendar extends Controller
{
    /**
     * Display the Connect Calendar modal with available providers.
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        // iCal import is embedded directly in the template
        // Plugins can add their providers via this filter
        $providers = self::dispatchFilter('connectOptions.providers', []);

        $this->tpl->assign('providers', $providers);

        return $this->tpl->displayPartial('calendar.connectCalendar');
    }

    /**
     * Display the Connect Calendar modal (GET).
     */
    public function get(): Response
    {
        return $this->run();
    }
}
