<?php

namespace Leantime\Domain\WeeklyPlanning\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowMyHistory — Employee view of all their past weekly plans, grouped by month.
 */
class ShowMyHistory extends Controller
{
    private WeeklyPlanningService $weeklyPlanningService;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function init(WeeklyPlanningService $weeklyPlanningService): void
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $this->weeklyPlanningService = $weeklyPlanningService;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function get(array $_params): Response
    {
        $employeeId = (int) session('userdata.id');
        $plans      = $this->weeklyPlanningService->getPlansForEmployee($employeeId);

        // Group plans by month label for accordion display
        $plansByMonth = [];
        foreach ($plans as $plan) {
            $monthKey = $plan['month'] ?? 'Unknown';
            $plansByMonth[$monthKey][] = $plan;
        }

        $this->tpl->assign('plansByMonth', $plansByMonth);
        $this->tpl->assign('totalPlans', count($plans));

        return $this->tpl->display('weeklyplanning.showMyHistory');
    }
}
