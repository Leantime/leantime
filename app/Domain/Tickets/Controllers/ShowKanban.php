<?php

namespace Leantime\Domain\Tickets\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

class ShowKanban extends Controller
{
    private ProjectService $projectService;

    private TicketService $ticketService;

    private SprintService $sprintService;

    private TimesheetService $timesheetService;

    public function init(
        ProjectService $projectService,
        TicketService $ticketService,
        SprintService $sprintService,
        TimesheetService $timesheetService
    ): void {
        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->sprintService = $sprintService;
        $this->timesheetService = $timesheetService;

        session(['lastPage' => CURRENT_URL]);
        session(['lastTicketView' => 'kanban']);
        session(['lastFilterdTicketKanbanView' => CURRENT_URL]);
    }

    /**
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        // Status groupBy is redundant on Kanban (status already shown as columns)
        // Auto-reset to "all" (no grouping) for cleaner default view
        if (isset($params['groupBy']) && $params['groupBy'] === 'status') {
            $params['groupBy'] = 'all';
        }

        $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
        $allKanbanColumns = $this->ticketService->getKanbanColumns();

        // NEW: Calculate status breakdown for swimlane visualizations
        $statusBreakdown = $this->ticketService->getStatusBreakdownBySwimlane(
            $template_assignments['allTickets'],
            $allKanbanColumns
        );

        array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));
        $this->tpl->assign('allKanbanColumns', $allKanbanColumns);
        $this->tpl->assign('statusBreakdown', $statusBreakdown);

        return $this->tpl->display('tickets.showKanban');
    }

    /**
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        // QuickAdd
        if (isset($_POST['quickadd']) && $_POST['quickadd'] == 1) {
            $formParams = [
                'headline' => $_POST['headline'] ?? '',
                'status' => $_POST['status'] ?? '',
                'milestone' => $_POST['milestone'] ?? '',
                'sprint' => $_POST['sprint'] ?? '',
                'projectId' => session('currentProject'),
                'editorId' => session('userdata.id'),
            ];

            // Swimlane context inheritance - map swimlane value to correct field based on groupBy
            $swimlaneValue = $_POST['swimlane'] ?? null;
            $groupBy = $_POST['groupBy'] ?? null;

            if (! empty($swimlaneValue) && ! empty($groupBy)) {
                // Map groupBy field to the parameter name expected by quickAddTicket()
                $fieldMapping = [
                    'priority' => 'priority',
                    'storypoints' => 'storypoints',
                    'effort' => 'storypoints',  // effort maps to storypoints field
                    'milestoneid' => 'milestone',  // Note: service expects 'milestone' not 'milestoneid'
                    'editorId' => 'editorId',
                    'sprint' => 'sprint',
                    'type' => 'type',
                ];

                if (isset($fieldMapping[$groupBy])) {
                    $paramName = $fieldMapping[$groupBy];
                    $formParams[$paramName] = $swimlaneValue;
                }
            }

            $result = $this->ticketService->quickAddTicket($formParams);

            if (is_array($result) && isset($result['status']) && $result['status'] === 'error') {
                // Error: reopen form with error
                session()->flash('quickadd_reopen', [
                    'status' => $formParams['status'],
                    'swimlane' => $_POST['swimlane'] ?? null,
                    'headline' => $formParams['headline'],
                    'error' => $result['message'],
                ]);
                $this->tpl->setNotification($result['message'], 'error');
            } else {
                // Success
                $stayOpen = isset($_POST['stay_open']) && $_POST['stay_open'] === '1';

                $this->tpl->setNotification('Task created: '.htmlspecialchars($formParams['headline']), 'success');

                if ($stayOpen) {
                    session()->flash('quickadd_reopen', [
                        'status' => $formParams['status'],
                        'swimlane' => $_POST['swimlane'] ?? null,
                        'headline' => '',
                    ]);
                }
            }

            return Frontcontroller::redirect(CURRENT_URL.'#status-'.$formParams['status']);
        }

        return Frontcontroller::redirect(CURRENT_URL);
    }
}
