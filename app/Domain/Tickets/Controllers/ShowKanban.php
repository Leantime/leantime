<?php

namespace Leantime\Domain\Tickets\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

class ShowKanban extends Controller
{
    private TicketService $ticketService;

    public function init(
        TicketService $ticketService
    ): void {
        $this->ticketService = $ticketService;

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

            $swimlaneValue = $_POST['swimlane'] ?? null;
            $groupBy = $_POST['groupBy'] ?? null;
            $stayOpen = isset($_POST['stay_open']) && $_POST['stay_open'] === '1';

            $result = $this->ticketService->quickAddTicketFromKanban($formParams, $swimlaneValue, $groupBy, $stayOpen);

            if ($result['success'] === false) {
                $this->tpl->setNotification($result['message'], 'error');
            } else {
                $this->tpl->setNotification('Task created: '.htmlspecialchars($result['headline']), 'success');
            }

            return Frontcontroller::redirect(CURRENT_URL.'#status-'.$result['status']);
        }

        return Frontcontroller::redirect(CURRENT_URL);
    }
}
