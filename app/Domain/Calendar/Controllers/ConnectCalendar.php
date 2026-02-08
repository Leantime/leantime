<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * ConnectCalendar Controller - Displays extensible modal for connecting external calendars.
 *
 * Plugins can register additional calendar providers via the filter:
 * 'leantime.domain.calendar.controllers.connectcalendar.get.connectOptions.providers'
 *
 * Provider array structure:
 *   - id: string (unique identifier)
 *   - icon: string (FontAwesome class or SVG markup)
 *   - iconType: string ('fontawesome'|'svg', default 'fontawesome')
 *   - title: string (display name)
 *   - description: string (short description)
 *   - actionUrl: string (URL to connect)
 *   - actionLabel: string (button text)
 *   - actionType: string ('link'|'modal', default 'link')
 */
class ConnectCalendar extends Controller
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
     * Display the Connect Calendar modal with available providers.
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $providers = self::dispatchFilter('connectOptions.providers', []);

        $this->tpl->assign('providers', $providers);

        return $this->tpl->displayPartial('calendar.connectCalendar');
    }

    /**
     * Handle iCal calendar import submission.
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        if (isset($params['name']) || isset($params['url'])) {
            $values = [
                'url' => $params['url'] ?? '',
                'name' => $params['name'] ?? 'My Calendar',
                'colorClass' => $params['colorClass'] ?? '#082236',
            ];

            $this->calendarRepo->addGUrl($values);
            $this->tpl->setNotification('notification.gcal_imported_successfully', 'success', 'externalcalendar_created');
        }

        return FrontcontrollerCore::redirect(BASE_URL.'/calendar/showMyCalendar');
    }
}
