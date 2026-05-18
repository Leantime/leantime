<?php

namespace Leantime\Domain\Tickets\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users as UserService;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;
use Symfony\Component\HttpFoundation\Response;

/**
 * NewTicketForPlan — Opens the full New To-Do creation form pre-assigned to a specific employee.
 *
 * Called from weekly-planning/showPlan/{planId} when a Team Lead creates a task for an employee.
 * The "Assigned to" field is pre-filled and locked to the target employee.
 * On successful save the ticket is also registered as a WeeklyPlanItem on the plan.
 */
class NewTicketForPlan extends Controller
{
    private ProjectService $projectService;

    private TicketService $ticketService;

    private SprintService $sprintService;

    private FileService $fileService;

    private TimesheetService $timesheetService;

    private UserService $userService;

    private WeeklyPlanningService $weeklyPlanningService;

    /**
     * @throws BindingResolutionException
     */
    public function init(
        ProjectService $projectService,
        TicketService $ticketService,
        SprintService $sprintService,
        FileService $fileService,
        TimesheetService $timesheetService,
        UserService $userService,
        WeeklyPlanningService $weeklyPlanningService
    ): void {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$teamlead], true);

        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->sprintService = $sprintService;
        $this->fileService = $fileService;
        $this->timesheetService = $timesheetService;
        $this->userService = $userService;
        $this->weeklyPlanningService = $weeklyPlanningService;
    }

    /**
     * Render the New To-Do form with the employee pre-filled and locked.
     *
     * @param  array<string, mixed>  $params
     *
     * @throws BindingResolutionException
     */
    public function get(array $params): Response
    {
        $planId = (int) ($params['planId'] ?? 0);
        $employeeId = (int) ($params['employeeId'] ?? 0);
        $projectId = (int) session('currentProject');

        $ticket = app()->make(TicketModel::class, [
            'values' => [
                'userLastname' => session('userdata.name'),
                'status' => 3,
                'projectId' => $projectId,
                'sprint' => session('currentSprint') ?? '',
                'editorId' => $employeeId,
            ],
        ]);
        $ticket->date = dtHelper()->userNow();
        $plan = $planId > 0 ? $this->weeklyPlanningService->getPlanById($planId) : null;
        if (! empty($plan['weekEnd'])) {
            $ticket->dateToFinish = $plan['weekEnd'];
        }

        $this->assignFormData($ticket, $planId, $employeeId, $projectId);

        return $this->tpl->displayPartial('tickets.newTicketForPlanModal');
    }

    /**
     * Handle form submission: create ticket, upload optional reference file, link to plan.
     *
     * @param  array<string, mixed>  $params
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        if (! isset($params['saveTicket']) && ! isset($params['saveAndCloseTicket'])) {
            return Frontcontroller::redirect(BASE_URL.'/tickets/newTicketForPlan');
        }

        $planId = (int) ($params['planId'] ?? 0);
        $employeeId = (int) ($params['employeeId'] ?? 0);

        // Always force the assignee to the locked employee so it cannot be spoofed.
        if ($employeeId > 0) {
            $params['editorId'] = $employeeId;
        }

        $this->forceDueDateIntoPlanWeek($params, $planId);

        $params['timeToFinish'] = format(value: $params['timeToFinish'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $params['timeFrom'] = format(value: $params['timeFrom'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $params['timeTo'] = format(value: $params['timeTo'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();

        $result = $this->ticketService->addTicket($params);

        if (! is_array($result)) {
            $ticketId = (int) $result;

            // Handle optional reference file upload.
            if (
                isset($_FILES['referenceFile'])
                && is_array($_FILES['referenceFile'])
                && ($_FILES['referenceFile']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
            ) {
                $this->fileService->upload(
                    ['file' => $_FILES['referenceFile']],
                    'tickets',
                    $ticketId
                );
            }

            // Link the new ticket to the weekly plan as a plan item.
            if ($planId > 0) {
                $this->weeklyPlanningService->addItem($planId, $ticketId, null);
            }

            $this->tpl->setNotification($this->language->__('notifications.ticket_saved'), 'success');

            if (isset($params['saveAndCloseTicket']) && $params['saveAndCloseTicket'] == 1) {
                return Frontcontroller::redirect(BASE_URL.'/tickets/showTicket/'.$ticketId.'?closeModal=1');
            }

            return Frontcontroller::redirect(BASE_URL.'/tickets/showTicket/'.$ticketId);
        }

        // Validation failed — re-render the form with the error.
        $this->tpl->setNotification($this->language->__($result['msg']), 'error');

        $ticket = app()->makeWith(TicketModel::class, ['values' => $params]);
        $ticket->userLastname = session('userdata.name');
        $projectId = (int) ($params['projectId'] ?? session('currentProject'));

        $this->assignFormData($ticket, $planId, $employeeId, $projectId);

        return $this->tpl->displayPartial('tickets.newTicketForPlanModal');
    }

    /**
     * Push all template variables needed by the form.
     *
     * @throws BindingResolutionException
     */
    private function assignFormData(TicketModel $ticket, int $planId, int $employeeId, int $projectId): void
    {
        $plan = $planId > 0 ? $this->weeklyPlanningService->getPlanById($planId) : null;
        $employee = $employeeId > 0 ? $this->userService->getUser($employeeId) : null;
        $employeeName = $employee
            ? trim(($employee['firstname'] ?? '').' '.($employee['lastname'] ?? ''))
            : '';

        $this->tpl->assign('ticket', $ticket);
        $this->tpl->assign('planId', $planId);
        $this->tpl->assign('lockedEmployeeId', $employeeId);
        $this->tpl->assign('lockedEmployeeName', $employeeName);
        $this->tpl->assign('planWeekStart', $plan['weekStart'] ?? '');
        $this->tpl->assign('planWeekEnd', $plan['weekEnd'] ?? '');

        $this->tpl->assign('ticketParents', $this->ticketService->getAllPossibleParents($ticket));
        $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
        $this->tpl->assign('ticketTypes', $this->ticketService->getTicketTypes());
        $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
        $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

        $this->tpl->assign('milestones', $this->ticketService->getAllMilestones([
            'sprint' => '',
            'type' => 'milestone',
            'currentProject' => $projectId,
        ]));
        $this->tpl->assign('sprints', $this->sprintService->getAllSprints($projectId));

        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
        $this->tpl->assign('ticketHours', 0);
        $this->tpl->assign('userHours', 0);
        $this->tpl->assign('timesheetsAllHours', 0);
        $this->tpl->assign('remainingHours', 0);

        $this->tpl->assign('userInfo', $this->userService->getUser(session('userdata.id')));
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($projectId));
        $this->tpl->assign('allAssignedprojects', $this->projectService->getProjectsUserHasAccessTo(session('userdata.id'), 'open'));
    }

    private function forceDueDateIntoPlanWeek(array &$params, int $planId): void
    {
        if ($planId <= 0) {
            return;
        }

        $plan = $this->weeklyPlanningService->getPlanById($planId);
        if (empty($plan['weekStart']) || empty($plan['weekEnd'])) {
            return;
        }

        $weekStart = Carbon::parse($plan['weekStart'])->startOfDay();
        $weekEnd = Carbon::parse($plan['weekEnd'])->endOfDay();
        try {
            $dueDate = ! empty($params['dateToFinish'])
                ? Carbon::instance(dtHelper()->parseUserDateTime($params['dateToFinish'], 'end'))
                : $weekEnd->copy();
        } catch (\Exception) {
            $dueDate = $weekEnd->copy();
        }

        if ($dueDate->lt($weekStart) || $dueDate->gt($weekEnd)) {
            $dueDate = $weekEnd->copy();
        }

        $params['dateToFinish'] = $dueDate->toDateString();
    }
}
