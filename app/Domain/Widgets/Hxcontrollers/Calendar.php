<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;

class Calendar extends HtmxController
{
    protected static string $view = 'widgets::partials.calendar';

    private CalendarService $calendarService;

    /**
     * Initializes dependencies.
     */
    public function init(CalendarService $calendarService): void
    {
        $this->calendarService = $calendarService;
    }

    public function get(): void
    {
        $userId = (int) session('userdata.id');

        $this->tpl->assign('externalCalendars', $this->calendarService->getMyExternalCalendars($userId));
        $this->tpl->assign('calendar', $this->calendarService->getCalendar($userId));
    }
}
