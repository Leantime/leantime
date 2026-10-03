<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;

class Calendar extends HtmxController
{
    /**
     * Per-user toggle (stored with the other submenu toggles) holding 'hide' when done
     * To-Dos should be left out of the dashboard calendar. Unset = show them (#3236).
     */
    public const HIDE_DONE_TOGGLE = 'dashboardCalendarHideDone';

    protected static string $view = 'widgets::partials.calendar';

    private CalendarService $calendarService;

    private ApiService $apiService;

    /**
     * Initializes dependencies.
     */
    public function init(CalendarService $calendarService, ApiService $apiService): void
    {
        $this->calendarService = $calendarService;
        $this->apiService = $apiService;
    }

    /**
     * Render the calendar widget for the session user.
     */
    public function get(): void
    {
        $userId = (int) session('userdata.id');
        $hideDoneTickets = $this->hideDoneTickets();

        $this->tpl->assign('hideDoneTickets', $hideDoneTickets);
        $this->tpl->assign('externalCalendars', $this->calendarService->getMyExternalCalendars($userId));
        $this->tpl->assign('calendar', $this->calendarService->getCalendar($userId, includeDoneTickets: ! $hideDoneTickets));
    }

    /**
     * Flip the "hide done To-Dos" preference and re-render the widget.
     */
    public function toggleDone(): void
    {
        $newState = $this->hideDoneTickets() ? 'show' : 'hide';
        $this->apiService->setSubmenuState(self::HIDE_DONE_TOGGLE, $newState);

        $this->get();
    }

    /**
     * Whether the session user chose to hide done To-Dos in the widget.
     */
    private function hideDoneTickets(): bool
    {
        return $this->tpl->getToggleState(self::HIDE_DONE_TOGGLE) === 'hide';
    }
}
