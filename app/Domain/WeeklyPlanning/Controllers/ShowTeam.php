<?php

namespace Leantime\Domain\WeeklyPlanning\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowTeam — Team Lead view of all employees' weekly plans, grouped by month.
 */
class ShowTeam extends Controller
{
    private WeeklyPlanningService $weeklyPlanningService;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function init(WeeklyPlanningService $weeklyPlanningService): void
    {
        Auth::authOrRedirect([Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $this->weeklyPlanningService = $weeklyPlanningService;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function get(array $params): Response
    {
        $teamLeadId    = (int) session('userdata.id');
        $selectedMonth = $_GET['month'] ?? null;

        $months      = $this->weeklyPlanningService->getMonthsForTeamLead($teamLeadId);
        $teamMembers = $this->weeklyPlanningService->getTeamDashboard($teamLeadId, $selectedMonth);

        // Default to most recent month if none selected and months exist
        if ($selectedMonth === null && ! empty($months)) {
            $selectedMonth = $months[0];
        }

        $this->tpl->assign('months', $months);
        $this->tpl->assign('selectedMonth', $selectedMonth);
        $this->tpl->assign('teamMembers', $teamMembers);
        $this->tpl->assign('itemStatuses', $this->weeklyPlanningService->itemStatuses);

        return $this->tpl->display('weeklyplanning.showTeam');
    }
}
