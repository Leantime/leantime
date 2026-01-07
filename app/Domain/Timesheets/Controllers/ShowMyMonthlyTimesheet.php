<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Domain\Timesheets\Services\ShowMyMonthlyTimesheetService as MonthlyTimesheetService;

class ShowMyMonthlyTimesheet extends Controller
{
    private TimesheetService $timesheetService;

    private TimesheetRepository $timesheetRepo;

    private ProjectRepository $projects;

    private TicketRepository $tickets;

    private MonthlyTimesheetService $monthlyTimesheetService;

    public function init(
        TimesheetService $timesheetService,
        TimesheetRepository $timesheetRepo,
        ProjectRepository $projects,
        TicketRepository $tickets,
        MonthlyTimesheetService $monthlyTimesheetService
    ): void {
        $this->timesheetService = $timesheetService;
        $this->timesheetRepo = $timesheetRepo;
        $this->projects = $projects;
        $this->tickets = $tickets;
        $this->monthlyTimesheetService = $monthlyTimesheetService;
    }

    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $fromDate = dtHelper()->userNow()->startOfMonth();

        $kind = 'all';
        if (isset($_POST['search'])) {
            if (! empty($_POST['startDate'])) {
                try {
                    $fromDate = dtHelper()->parseUserDateTime($_POST['startDate'])->startOfMonth();
                } catch (\Exception $e) {
                    $this->tpl->setNotification('Could not parse date', 'error', 'save_timesheet');
                }
            }
        }

        if (isset($_POST['saveTimeSheet'])) {
            $this->monthlyTimesheetService->saveTimeSheet($_POST);
        }

        $fromDateDb = $fromDate->copy()->setToDbTimezone();
        $myTimesheets = $this->timesheetService->getWeeklyTimesheets(-1, $fromDateDb, session('userdata.id'));
        $existingTicketIds = array_map(fn($item) => $item['ticketId'], $myTimesheets);

        $this->tpl->assign('existingTicketIds', $existingTicketIds);
        $this->tpl->assign('dateFrom', $fromDate);
        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('kind', $this->timesheetRepo->kind);
        $this->tpl->assign('allProjects', $this->projects->getUserProjects(
            userId: session('userdata.id'),
            projectTypes: 'project'
        ));
        $this->tpl->assign('allTickets', $this->tickets->getUsersTickets(
            id: session('userdata.id'),
            limit: -1
        ));
        $this->tpl->assign('allTimesheets', $myTimesheets);

        return $this->tpl->display('timesheets.showMyMonthlyTimesheet');
    }
}
