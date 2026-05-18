<?php

namespace Leantime\Domain\WeeklyPlanning\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;

/**
 * MyPlanTasks — renders the current week's plan items for the logged-in employee.
 * Embedded inside the MyToDos widget.
 */
class MyPlanTasks extends HtmxController
{
    protected static string $view = 'weeklyplanning::partials.myPlanTasks';

    private WeeklyPlanningService $service;

    private TicketsService $ticketsService;

    private TimesheetService $timesheetService;

    public function init(
        WeeklyPlanningService $service,
        TicketsService $ticketsService,
        TimesheetService $timesheetService,
    ): void {
        $this->service = $service;
        $this->ticketsService = $ticketsService;
        $this->timesheetService = $timesheetService;
    }

    /** @api */
    public function get(): void
    {
        $userId = (int) session('userdata.id');
        $plan = $this->service->getCurrentPlanForEmployee($userId);
        $items = $plan ? $this->service->getItemsForPlan((int) $plan['id']) : [];

        // Enrich each item with full ticket data for items that have a linked ticket
        $statusLabels = $this->ticketsService->getAllStatusLabelsByUserId($userId);
        $enrichedItems = [];

        foreach ($items as $item) {
            if (! empty($item['ticketId'])) {
                $ticketObj = $this->ticketsService->getTicket((int) $item['ticketId']);
                if ($ticketObj) {
                    $item['ticketData'] = (array) $ticketObj;
                    // Ensure statusLabels has this project
                    $projectId = $item['ticketData']['projectId'];
                    if (! isset($statusLabels[$projectId])) {
                        $statusLabels[$projectId] = $this->ticketsService->getStatusLabels($projectId);
                    }
                }
            }
            $enrichedItems[] = $item;
        }

        $this->tpl->assign('plan', $plan);
        $this->tpl->assign('items', $enrichedItems);
        $this->tpl->assign('statusLabels', $statusLabels);
        $this->tpl->assign('onTheClock', $this->timesheetService->isClocked($userId));
    }
}
