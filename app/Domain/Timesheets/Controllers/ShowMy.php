<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\CarbonInterface;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

class ShowMy extends Controller
{
    private TimesheetService $timesheetService;

    private ProjectService $projectService;

    /**
     * Initializes dependencies.
     */
    public function init(
        TimesheetService $timesheetService,
        ProjectService $projectService
    ): void {
        $this->timesheetService = $timesheetService;
        $this->projectService = $projectService;
    }

    /**
     * Displays the weekly timesheet view.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $fromDate = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();

        $this->assignTemplateVars($fromDate);

        return $this->tpl->display('timesheets.showMy');
    }

    /**
     * Handles weekly timesheet search and save operations.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $fromDate = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();

        if (isset($_POST['search']) && ! empty($_POST['startDate'])) {
            $parsed = $this->timesheetService->parseWeeklyStartDate($_POST['startDate'], $fromDate);
            $fromDate = $parsed['date'];

            if ($parsed['failed']) {
                $this->tpl->setNotification('Could not parse date', 'error', 'save_timesheet');
            }
        }

        if (isset($_POST['saveTimeSheet'])) {
            $notifications = $this->timesheetService->saveWeeklyTimesheetEntries($_POST);
            foreach ($notifications as $notification) {
                $this->tpl->setNotification($notification['message'], $notification['type'], 'save_timesheet');
            }
        }

        $this->assignTemplateVars($fromDate);

        return $this->tpl->display('timesheets.showMy');
    }

    /**
     * Assigns common template variables for the weekly timesheet view.
     */
    private function assignTemplateVars(CarbonInterface $fromDate): void
    {
        $weekly = $this->timesheetService->getWeeklyTimesheetsWithTicketIds(-1, $fromDate, session('userdata.id'));

        $this->tpl->assign('existingTicketIds', $weekly['existingTicketIds']);
        $this->tpl->assign('dateFrom', $fromDate);
        $this->tpl->assign('actKind', 'all');
        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
        $this->tpl->assign('allProjects', $this->projectService->getProjectsAssignedToUser(
            userId: session('userdata.id'),
            projectTypes: 'project'
        ));
        $this->tpl->assign('allTickets', $this->timesheetService->getUsersTickets(
            userId: session('userdata.id'),
            limit: -1
        ));
        $this->tpl->assign('allTimesheets', $weekly['timesheets']);
    }
}
