<?php

namespace Leantime\Domain\Tickets\Controllers;

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
use Symfony\Component\HttpFoundation\Response;

/**
 * NewTicketForMilestone — Opens the New Task modal pre-filled and locked to a specific milestone and project.
 *
 * Called from the Milestone Review page "+ Add Task" button.
 * The milestone and project are pre-filled and locked; everything else is decided by the user.
 */
class NewTicketForMilestone extends Controller
{
    private ProjectService $projectService;

    private TicketService $ticketService;

    private SprintService $sprintService;

    private FileService $fileService;

    private TimesheetService $timesheetService;

    private UserService $userService;

    /**
     * @throws BindingResolutionException
     */
    public function init(
        ProjectService $projectService,
        TicketService $ticketService,
        SprintService $sprintService,
        FileService $fileService,
        TimesheetService $timesheetService,
        UserService $userService
    ): void {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$teamlead]);

        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->sprintService = $sprintService;
        $this->fileService = $fileService;
        $this->timesheetService = $timesheetService;
        $this->userService = $userService;
    }

    /**
     * Render the New Task form with milestone and project pre-filled and locked.
     *
     * @param  array<string, mixed>  $params
     *
     * @throws BindingResolutionException
     */
    public function get(array $params): Response
    {
        $milestoneId = (int) ($params['milestoneId'] ?? 0);
        $projectId = (int) ($params['projectId'] ?? session('currentProject'));

        $ticket = app()->make(TicketModel::class, [
            'values' => [
                'userLastname' => session('userdata.name'),
                'status' => 3,
                'projectId' => $projectId,
                'milestoneid' => $milestoneId,
                'sprint' => session('currentSprint') ?? '',
                'editorId' => session('userdata.id'),
            ],
        ]);
        $ticket->date = dtHelper()->userNow();

        $this->assignFormData($ticket, $milestoneId, $projectId);

        return $this->tpl->displayPartial('tickets.newTicketForMilestoneModal');
    }

    /**
     * Handle form submission: create ticket, upload optional reference file.
     *
     * @param  array<string, mixed>  $params
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        if (! isset($params['saveTicket']) && ! isset($params['saveAndCloseTicket'])) {
            return Frontcontroller::redirect(BASE_URL.'/tickets/newTicketForMilestone');
        }

        $milestoneId = (int) ($params['milestoneId'] ?? 0);
        $projectId = (int) ($params['projectId'] ?? session('currentProject'));

        // Force the locked values so they cannot be spoofed via form manipulation.
        if ($milestoneId > 0) {
            $params['milestoneid'] = $milestoneId;
        }
        $params['projectId'] = $projectId;

        $params['timeToFinish'] = format(value: $params['timeToFinish'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $params['timeFrom'] = format(value: $params['timeFrom'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
        $params['timeTo'] = format(value: $params['timeTo'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();

        $result = $this->ticketService->addTicket($params);

        if (! is_array($result)) {
            $ticketId = (int) $result;

            if (
                isset($_FILES['referenceFile'])
                && is_array($_FILES['referenceFile'])
                && ($_FILES['referenceFile']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
            ) {
                $this->fileService->upload(
                    ['file' => $_FILES['referenceFile']],
                    'ticket',
                    $ticketId
                );
            }

            $this->tpl->setNotification($this->language->__('notifications.ticket_saved'), 'success');

            if (isset($params['saveAndCloseTicket']) && $params['saveAndCloseTicket'] == 1) {
                return Frontcontroller::redirect(BASE_URL.'/tickets/showTicket/'.$ticketId.'?closeModal=1');
            }

            return Frontcontroller::redirect(BASE_URL.'/tickets/showTicket/'.$ticketId);
        }

        $this->tpl->setNotification($this->language->__($result['msg']), 'error');

        $ticket = app()->makeWith(TicketModel::class, ['values' => $params]);
        $ticket->userLastname = session('userdata.name');

        $this->assignFormData($ticket, $milestoneId, $projectId);

        return $this->tpl->displayPartial('tickets.newTicketForMilestoneModal');
    }

    /**
     * Push all template variables needed by the form.
     *
     * @throws BindingResolutionException
     */
    private function assignFormData(TicketModel $ticket, int $milestoneId, int $projectId): void
    {
        // Get milestone name for display
        $milestoneName = '';
        if ($milestoneId > 0) {
            $milestones = $this->ticketService->getAllMilestones([
                'sprint' => '',
                'type' => 'milestone',
                'currentProject' => $projectId,
            ]);
            foreach ($milestones as $m) {
                if ((int) $m->id === $milestoneId) {
                    $milestoneName = $m->headline ?? '';
                    break;
                }
            }
        }

        // Get project name for display
        $projectName = '';
        $projects = $this->projectService->getProjectsUserHasAccessTo(session('userdata.id'));
        foreach ($projects as $p) {
            if ((int) $p['id'] === $projectId) {
                $projectName = $p['name'] ?? '';
                break;
            }
        }

        $this->tpl->assign('ticket', $ticket);
        $this->tpl->assign('lockedMilestoneId', $milestoneId);
        $this->tpl->assign('lockedMilestoneName', $milestoneName);
        $this->tpl->assign('lockedProjectId', $projectId);
        $this->tpl->assign('lockedProjectName', $projectName);
        $this->tpl->assign('lockProjectMilestone', true);

        $this->tpl->assign('ticketParents', $this->ticketService->getAllPossibleParents($ticket));
        $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
        $this->tpl->assign('ticketTypes', $this->ticketService->getTicketTypes());
        $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
        $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());
        $this->tpl->assign('sprints', $this->sprintService->getAllSprints($projectId));

        // ticketDetails.sub.php iterates milestones — pass the project milestones so the locked one is pre-selected
        $milestones = $this->ticketService->getAllMilestones([
            'sprint' => '',
            'type' => 'milestone',
            'currentProject' => $projectId,
        ]);
        $this->tpl->assign('milestones', is_array($milestones) ? $milestones : []);

        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
        $this->tpl->assign('ticketHours', 0);
        $this->tpl->assign('userHours', 0);
        $this->tpl->assign('timesheetsAllHours', 0);
        $this->tpl->assign('remainingHours', 0);

        $this->tpl->assign('userInfo', $this->userService->getUser(session('userdata.id')));
        // ticketDetails.sub.php also iterates users — pass project members
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($projectId) ?: []);
        $this->tpl->assign('allAssignedprojects', $projects);
    }
}
