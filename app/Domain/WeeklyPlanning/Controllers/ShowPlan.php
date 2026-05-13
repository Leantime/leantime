<?php

namespace Leantime\Domain\WeeklyPlanning\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowPlan — Full detail view of one weekly plan (used by both TL and employee).
 */
class ShowPlan extends Controller
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
        $planId = (int) ($params['id'] ?? 0);

        if (! $planId) {
            return $this->tpl->displayPartial('errors.error404', responseCode: 404);
        }

        $plan = $this->weeklyPlanningService->getPlanById($planId);

        if (! $plan) {
            return $this->tpl->displayPartial('errors.error404', responseCode: 404);
        }

        $userId = (int) session('userdata.id');

        // Allow only the employee, their team lead, and admins/managers to view
        if ($plan['employeeId'] !== $userId && $plan['teamLeadId'] !== $userId
            && ! Auth::userIsAtLeast(Roles::$manager)) {
            return $this->tpl->displayPartial('errors.error403', responseCode: 403);
        }

        $items       = $this->weeklyPlanningService->getItemsForPlan($planId);
        $feedback    = $this->weeklyPlanningService->getFeedbackForPlan($planId);
        $commitments = $this->weeklyPlanningService->getCommitmentsForPlan($planId);

        $this->tpl->assign('plan', $plan);
        $this->tpl->assign('items', $items);
        $this->tpl->assign('feedback', $feedback);
        $this->tpl->assign('commitments', $commitments);
        $this->tpl->assign('itemStatuses', $this->weeklyPlanningService->itemStatuses);
        $this->tpl->assign('feedbackTypes', $this->weeklyPlanningService->feedbackTypes);
        $this->tpl->assign('reasonRequiredStatuses', $this->weeklyPlanningService->reasonRequiredStatuses);
        $this->tpl->assign('currentUserId', $userId);
        $this->tpl->assign('isTeamLead', $plan['teamLeadId'] === $userId || Auth::userIsAtLeast(Roles::$manager));

        return $this->tpl->display('weeklyplanning.showPlan');
    }
}
