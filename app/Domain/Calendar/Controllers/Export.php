<?php

namespace Leantime\Domain\Calendar\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Symfony\Component\HttpFoundation\Response;

class Export extends Controller
{
    private SettingService $settingService;

    private CalendarService $calendarService;

    /**
     * Initializes dependencies.
     */
    public function init(
        SettingService $settingService,
        CalendarService $calendarService
    ): void {
        $this->settingService = $settingService;
        $this->calendarService = $calendarService;
    }

    /**
     * Displays the calendar export page.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        if (isset($_GET['remove'])) {
            $this->settingService->deleteSetting('usersettings.'.session('userdata.id').'.icalSecret');
            $this->tpl->setNotification('notifications.ical_removed_success', 'success');
        }

        $this->assignUrl();

        return $this->tpl->displayPartial('calendar.export');
    }

    /**
     * Handles iCal URL generation.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        if (isset($_POST['generateUrl'])) {
            try {
                $this->calendarService->generateIcalHash();
                $this->tpl->setNotification('notifications.ical_success', 'success');
            } catch (\Exception $e) {
                $this->tpl->setNotification('There was a problem generating the ical hash', 'error');
            }
        }

        $this->assignUrl();

        return $this->tpl->displayPartial('calendar.export');
    }

    /**
     * Assigns the iCal URL to the template.
     */
    private function assignUrl(): void
    {
        $icalUrl = '';
        try {
            $icalUrl = $this->calendarService->getICalUrl();
        } catch (\Exception $e) {
            $this->tpl->setNotification('Could not find ical URL', 'error');
        }

        $this->tpl->assign('url', $icalUrl);
    }
}
