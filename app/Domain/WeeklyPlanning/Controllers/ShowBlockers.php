<?php

namespace Leantime\Domain\WeeklyPlanning\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowBlockers — Team Lead view of all blocked / not_completed items across their team.
 */
class ShowBlockers extends Controller
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
    public function get(array $_params): Response
    {
        $teamLeadId    = (int) session('userdata.id');
        $blockedItems  = $this->weeklyPlanningService->getBlockedItemsForTeamLead($teamLeadId);

        $this->tpl->assign('blockedItems', $blockedItems);
        $this->tpl->assign('itemStatuses', $this->weeklyPlanningService->itemStatuses);

        return $this->tpl->display('weeklyplanning.showBlockers');
    }
}
