<?php

namespace Leantime\Domain\WeeklyPlanning\Controllers;

use Carbon\CarbonImmutable;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowMy — Developer's monthly weekly-plan overview.
 *
 * Displays all week slots for the selected month, showing assigned tasks
 * per week alongside plan metadata (status, TL, 1:1 date).
 */
class ShowMy extends Controller
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
    public function get(array $params): Response
    {
        $employeeId = (int) session('userdata.id');
        $now = CarbonImmutable::now();

        $year = isset($params['year']) ? (int) $params['year'] : (int) $now->year;
        $month = isset($params['month']) ? (int) $params['month'] : (int) $now->month;

        // Clamp to valid month range.
        $month = max(1, min(12, $month));

        $monthDate = CarbonImmutable::create($year, $month, 1);
        $prevMonth = $monthDate->subMonth();
        $nextMonth = $monthDate->addMonth();

        $weekSlots = $this->weeklyPlanningService->getMonthWeekSlots($employeeId, $year, $month);
        $feedbackTypes = $this->weeklyPlanningService->feedbackTypes;

        // Determine which slot is "this week" for visual highlighting.
        $todayStr = $now->toDateString();
        foreach ($weekSlots as &$slot) {
            $slot['isCurrent'] = ($slot['weekStart'] <= $todayStr && $slot['weekEnd'] >= $todayStr);
        }
        unset($slot);

        $this->tpl->assign('weekSlots', $weekSlots);
        $this->tpl->assign('monthDate', $monthDate);
        $this->tpl->assign('prevMonth', $prevMonth);
        $this->tpl->assign('nextMonth', $nextMonth);
        $this->tpl->assign('itemStatuses', $this->weeklyPlanningService->itemStatuses);
        $this->tpl->assign('feedbackTypes', $feedbackTypes);

        return $this->tpl->display('weeklyplanning.showMy');
    }
}
