<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;

/**
 * Class MyToDosCalendar
 *
 * HTMX controller for the Calendar view on the developer dashboard My Todos widget.
 * Shows the user's personal calendar (tasks + events) with the same 4-view toggle bar.
 */
class MyToDosCalendar extends HtmxController
{
    protected static string $view = 'widgets::partials.myToDosCalendar';

    private CalendarRepository $calendarRepo;

    private ProjectService $projectService;

    public function init(
        CalendarRepository $calendarRepo,
        ProjectService $projectService
    ): void {
        $this->calendarRepo = $calendarRepo;
        $this->projectService = $projectService;
        session(['lastPage' => BASE_URL.'/dashboard/home']);
    }

    /**
     * Load the calendar view with the user's events and tickets.
     *
     * @api
     */
    public function get(): void
    {
        $userId = (int) session('userdata.id');

        $this->tpl->assign('calendar', $this->calendarRepo->getCalendar($userId));
        $this->tpl->assign('externalCalendars', $this->calendarRepo->getMyExternalCalendars($userId));
        $this->tpl->assign('allAssignedprojects', $this->projectService->getProjectsAssignedToUser($userId, 'open'));
    }
}
