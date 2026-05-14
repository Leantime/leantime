<?php

namespace Leantime\Domain\WeeklyPlanning\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;
use Symfony\Component\HttpFoundation\Response;

/**
 * NewPlan — Create a weekly plan for a team member.
 *
 * GET  /weekly-planning/newPlan?employeeId=X  → show creation form
 * POST /weekly-planning/newPlan               → create and redirect to plan
 */
class NewPlan extends Controller
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
        $employeeId = (int) ($_GET['employeeId'] ?? 0);

        // Guard: only team leads can create plans for their direct reports
        $teamMembers = $this->weeklyPlanningService->getTeamMembers((int) session('userdata.id'));
        $memberIds   = array_column($teamMembers, 'id');

        if ($employeeId === 0 || ! in_array($employeeId, $memberIds, true)) {
            return Frontcontroller::redirect(BASE_URL.'/weekly-planning/showTeam');
        }

        // Pre-fill: current week Monday→Friday
        $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $weekEnd   = now()->endOfWeek(\Carbon\Carbon::FRIDAY)->toDateString();

        // Find the employee in team members array
        $employeeKey = array_search($employeeId, $memberIds, true);
        $employee    = $employeeKey !== false ? $teamMembers[$employeeKey] : null;

        $this->tpl->assign('employee', $employee);
        $this->tpl->assign('employeeId', $employeeId);
        $this->tpl->assign('weekStart', $weekStart);
        $this->tpl->assign('weekEnd', $weekEnd);

        return $this->tpl->display('weeklyplanning.newPlan');
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function post(array $_params): Response
    {
        $employeeId     = (int) ($_POST['employeeId'] ?? 0);
        $weekStart      = (string) ($_POST['weekStart'] ?? '');
        $weekEnd        = (string) ($_POST['weekEnd'] ?? '');
        $dateOfOneOnOne = (string) ($_POST['dateOfOneOnOne'] ?? '');

        if ($employeeId === 0 || empty($weekStart)) {
            return Frontcontroller::redirect(BASE_URL.'/weekly-planning/showTeam');
        }

        $planId = $this->weeklyPlanningService->createPlan([
            'employeeId'     => $employeeId,
            'teamLeadId'     => (int) session('userdata.id'),
            'weekStart'      => $weekStart,
            'weekEnd'        => $weekEnd ?: now()->endOfWeek(\Carbon\Carbon::FRIDAY)->toDateString(),
            'dateOfOneOnOne' => $dateOfOneOnOne ?: null,
        ]);

        if ($planId) {
            return Frontcontroller::redirect(BASE_URL.'/weekly-planning/showPlan/'.$planId);
        }

        $this->tpl->setNotification(__('weeklyplanning.text.plan_create_error'), 'error');

        return Frontcontroller::redirect(BASE_URL.'/weekly-planning/showTeam');
    }
}
