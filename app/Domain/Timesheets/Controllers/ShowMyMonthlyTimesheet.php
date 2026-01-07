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

class ShowMyMonthlyTimesheet extends Controller
{
    private TimesheetService $timesheetService;

    private TimesheetRepository $timesheetRepo;

    private ProjectRepository $projects;

    private TicketRepository $tickets;

    public function init(
        TimesheetService $timesheetService,
        TimesheetRepository $timesheetRepo,
        ProjectRepository $projects,
        TicketRepository $tickets,
    ): void {
        $this->timesheetService = $timesheetService;
        $this->timesheetRepo = $timesheetRepo;
        $this->projects = $projects;
        $this->tickets = $tickets;
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
        $this->saveTimeSheet($_POST);
    }

    $fromDateDb = $fromDate->copy()->setToDbTimezone();
    $myTimesheets = $this->timesheetService->getWeeklyTimesheets(-1, $fromDateDb, session('userdata.id'));
    $existingTicketIds = array_map(fn ($item) => $item['ticketId'], $myTimesheets);

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

    public function saveTimeSheet(array $postData): void
    {
        foreach ($postData as $key => $dateEntry) {

            $tempData = explode('|', $key);

            if (count($tempData) === 4) {
                $ticketId = $tempData[0];
                $kind = $tempData[1];
                $date = $tempData[2];
                $timestamp = $tempData[3];
                $hours = $dateEntry;

                if ($ticketId === 'new' || $ticketId === 0) {
                    $ticketId = (int) $postData['ticketId'];
                    $kind = $postData['kindId'];

                    if ($ticketId == 0 && $hours > 0) {
                        $this->tpl->setNotification('Task ID is required for new entries', 'error', 'save_timesheet');

                        return;
                    }
                }

                $parsedHours = $hours;
                if (!empty($hours) && !is_numeric($hours)) {
                    try {
                        $parser = app(\Leantime\Domain\Timesheets\Services\TimeParser::class);
                        $parsedHours = $parser->parseTimeToDecimal($hours);
                        
                        if ($parsedHours > 24 * 365) { 
                            throw new \InvalidArgumentException('Time value is unreasonably large. Please enter a valid amount of time.');
                        }
                    } catch (\InvalidArgumentException $e) {
                        $this->tpl->setNotification($e->getMessage(), 'error', 'time_parse_error');
                        
                        continue;
                    }
                }
                
                $values = [
                    'userId' => session('userdata.id'),
                    'ticket' => $ticketId,
                    'date' => $date,
                    'timestamp' => $timestamp,
                    'hours' => $parsedHours,
                    'kind' => $kind,
                ];

                if ($timestamp !== 'false' && $timestamp != false) {
                    try {
                        $this->timesheetService->upsertTime($ticketId, $values);
                        $this->tpl->setNotification('Timesheet saved successfully', 'success', 'save_timesheet');
                    } catch (\Exception $e) {
                        $this->tpl->setNotification('Error logging time: '.$e->getMessage(), 'error', 'save_timesheet');
                        report($e);
                        continue;
                    }
                }
            }
        }
    }
}
