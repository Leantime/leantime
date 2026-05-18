<?php

namespace Leantime\Domain\WeeklyPlanning\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowCommitments — Team Lead view of all commitments across their team.
 */
class ShowCommitments extends Controller
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
        $teamLeadId = (int) session('userdata.id');
        $openOnly = ! isset($_GET['showAll']);
        $commitments = $this->weeklyPlanningService->getCommitmentsForTeamLead($teamLeadId, $openOnly);

        $this->tpl->assign('commitments', $commitments);
        $this->tpl->assign('openOnly', $openOnly);

        return $this->tpl->display('weeklyplanning.showCommitments');
    }
}
