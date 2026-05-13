<?php

namespace Leantime\Domain\WeeklyPlanning\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;

/**
 * MyPlanTasks — renders the current week's plan items for the logged-in employee.
 * Embedded inside the MyToDos widget.
 */
class MyPlanTasks extends HtmxController
{
    protected static string $view = 'weeklyplanning::partials.myPlanTasks';

    private WeeklyPlanningService $service;

    public function init(WeeklyPlanningService $service): void
    {
        $this->service = $service;
    }

    /** @api */
    public function get(): void
    {
        $userId = (int) session('userdata.id');
        $plan   = $this->service->getCurrentPlanForEmployee($userId);
        $items  = $plan ? $this->service->getItemsForPlan((int) $plan['id']) : [];

        $this->tpl->assign('plan', $plan);
        $this->tpl->assign('items', $items);
    }
}
