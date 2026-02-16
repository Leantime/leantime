<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for the "My Timesheets" list view with inline editing.
 */
class ShowMyList extends Controller
{
    private TimesheetService $timesheetService;

    private ProjectService $projectService;

    private TicketService $ticketService;

    /**
     * Initialise controller dependencies.
     */
    public function init(
        TimesheetService $timesheetService,
        ProjectService $projectService,
        TicketService $ticketService,
    ): void {
        $this->timesheetService = $timesheetService;
        $this->projectService = $projectService;
        $this->ticketService = $ticketService;

        session(['lastPage' => BASE_URL.'/timesheets/showMyList']);
    }

    /**
     * Display the list view.
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $kind = session('timesheetListKind', 'all');

        $dateFrom = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();
        $dateTo = dtHelper()->userNow()->endOfWeek()->setToDbTimezone();

        if (session()->has('timesheetListDateFrom')) {
            $dateFrom = session('timesheetListDateFrom');
        }

        if (session()->has('timesheetListDateTo')) {
            $dateTo = session('timesheetListDateTo');
        }

        $this->assignTemplateVars($dateFrom, $dateTo, $kind);

        return $this->tpl->display('timesheets.showMyList');
    }

    /**
     * Handle form submissions (filter or save).
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $kind = $_POST['kind'] ?? 'all';

        $dateFrom = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();
        $dateTo = dtHelper()->userNow()->endOfWeek()->setToDbTimezone();

        if (! empty($_POST['dateFrom'])) {
            try {
                $dateFrom = dtHelper()->parseUserDateTime($_POST['dateFrom'])->setToDbTimezone();
            } catch (\Exception $e) {
                Log::warning($e);
                $this->tpl->setNotification('Could not parse date', 'error', 'save_timesheet');
            }
        }

        if (! empty($_POST['dateTo'])) {
            try {
                $dateTo = dtHelper()->parseUserDateTime($_POST['dateTo'])->setToDbTimezone();
            } catch (\Exception $e) {
                Log::warning($e);
                $this->tpl->setNotification('Could not parse date', 'error', 'save_timesheet');
            }
        }

        // Persist filter state in session so GET reloads keep the same filters
        session(['timesheetListDateFrom' => $dateFrom]);
        session(['timesheetListDateTo' => $dateTo]);
        session(['timesheetListKind' => $kind]);

        if (isset($_POST['saveTimeSheet'])) {
            $this->saveTimeSheet($_POST);
        }

        $this->assignTemplateVars($dateFrom, $dateTo, $kind);

        return $this->tpl->display('timesheets.showMyList');
    }

    /**
     * Save timesheet entries from the inline-editable form.
     *
     * Handles two types of entries:
     * 1. New entries via explicit fields (newDate, newHours, newTicketId, newKindId)
     * 2. Existing entries via pipe-delimited input keys: ticketId|kind|formattedDate|timestamp
     *
     * @throws BindingResolutionException
     */
    private function saveTimeSheet(array $postData): void
    {
        // Handle new entry (explicit form fields)
        $this->saveNewEntry($postData);

        // Handle existing entry updates (pipe-delimited keys)
        $this->saveExistingEntries($postData);
    }

    /**
     * Save a new timesheet entry from explicit form fields.
     */
    private function saveNewEntry(array $postData): void
    {
        $newDate = trim($postData['newDate'] ?? '');
        $newHours = (float) ($postData['newHours'] ?? 0);
        $newTicketId = (int) ($postData['newTicketId'] ?? 0);
        $newKind = $postData['newKindId'] ?? 'GENERAL_BILLABLE';

        // Skip if no hours or no date entered
        if ($newHours <= 0 || $newDate === '') {
            return;
        }

        if ($newTicketId === 0) {
            $this->tpl->setNotification('Please select a to-do for the new entry', 'error', 'save_timesheet');

            return;
        }

        try {
            $this->timesheetService->upsertTime($newTicketId, [
                'userId' => session('userdata.id'),
                'ticket' => $newTicketId,
                'dateString' => $newDate,
                'hours' => $newHours,
                'kind' => $newKind,
            ]);
            $this->tpl->setNotification('Timesheet saved successfully', 'success', 'save_timesheet');
        } catch (\Exception $e) {
            $this->tpl->setNotification('Error logging time: '.$e->getMessage(), 'error', 'save_timesheet');
            report($e);
        }
    }

    /**
     * Save updates to existing timesheet entries via pipe-delimited keys.
     *
     * Key format: ticketId|kind|formattedDate|timestamp
     */
    private function saveExistingEntries(array $postData): void
    {
        foreach ($postData as $key => $dateEntry) {
            $tempData = explode('|', $key);

            if (count($tempData) === 4) {
                $ticketId = (int) $tempData[0];
                $kind = $tempData[1];
                $timestamp = $tempData[3];
                $hours = $dateEntry;

                // Skip the new entry row (handled separately)
                if ($tempData[0] === 'new') {
                    continue;
                }

                $values = [
                    'userId' => session('userdata.id'),
                    'ticket' => $ticketId,
                    'timestamp' => $timestamp,
                    'hours' => $hours,
                    'kind' => $kind,
                ];

                if ($timestamp !== 'false' && $timestamp != false) {
                    try {
                        $this->timesheetService->upsertTime($ticketId, $values);
                    } catch (\Exception $e) {
                        $this->tpl->setNotification('Error logging time: '.$e->getMessage(), 'error', 'save_timesheet');
                        report($e);

                        continue;
                    }
                }
            }
        }
    }

    /**
     * Assign all template variables needed for the list view.
     */
    private function assignTemplateVars(mixed $dateFrom, mixed $dateTo, string $kind): void
    {
        $this->tpl->assign('dateFrom', $dateFrom);
        $this->tpl->assign('dateTo', $dateTo);
        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
        $this->tpl->assign('allTimesheets', $this->timesheetService->getAll(
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            projectId: -1,
            kind: $kind,
            userId: session('userdata.id'),
            invEmpl: '-1',
            invComp: '-1',
            paid: '-1',
        ));
        $this->tpl->assign('allProjects', $this->projectService->getProjectsAssignedToUser(
            userId: session('userdata.id'),
            projectTypes: 'project',
        ));
        $this->tpl->assign('allTickets', $this->ticketService->getAllOpenUserTickets(
            userId: session('userdata.id'),
        ));
    }
}
