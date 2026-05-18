<?php

namespace Leantime\Domain\WeeklyPlanning\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;

/**
 * Feedback — HTMX controller for 4-direction feedback entries.
 *
 * GET  /hx/weekly-planning/feedback/editForm?planId=X&type=Y  → feedback edit form
 * POST /hx/weekly-planning/feedback/save                       → save and re-render display
 * GET  /hx/weekly-planning/feedback/view?planId=X&type=Y       → re-render display (cancel)
 */
class Feedback extends HtmxController
{
    protected static string $view = 'weeklyplanning::partials.feedbackDisplay';

    private WeeklyPlanningService $service;

    public function init(WeeklyPlanningService $service): void
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $this->service = $service;
    }

    /** Render the inline edit form for one feedback type. */
    public function editForm(): void
    {
        $planId = (int) $this->incomingRequest->query->get('planId', 0);
        $plan = $this->service->getPlanById($planId);

        if (! $this->canAccessPlan($plan)) {
            $this->denyAccess();

            return;
        }

        static::$view = 'weeklyplanning::partials.feedbackForm';

        $type = (string) $this->incomingRequest->query->get('type', '');
        $feedback = $this->service->getFeedbackForPlan($planId);
        $existing = collect($feedback)->firstWhere('type', $type);

        $this->tpl->assign('planId', $planId);
        $this->tpl->assign('type', $type);
        $this->tpl->assign('currentMessage', $existing['message'] ?? '');
        $this->tpl->assign('feedbackTypes', $this->service->feedbackTypes);
        $this->tpl->assign('canEdit', $this->resolveCanEdit($type));
    }

    /** Save feedback and re-render the display block. */
    public function save(): void
    {
        $planId = (int) ($this->incomingRequest->request->get('planId') ?? 0);
        $type = (string) ($this->incomingRequest->request->get('type') ?? '');
        $message = trim((string) ($this->incomingRequest->request->get('message') ?? ''));
        $userId = (int) session('userdata.id');
        $plan = $this->service->getPlanById($planId);

        // Authorization: must be a participant in the plan (or admin/owner)…
        if (! $this->canAccessPlan($plan)) {
            $this->denyAccess();

            return;
        }

        // …and the current role must be allowed to write THIS feedback type.
        // (Without this, an employee could POST manager_* feedback, forging it.)
        if (! $this->resolveCanEdit($type)) {
            $this->denyAccess();

            return;
        }

        $toUserId = str_starts_with($type, 'manager_')
            ? (int) ($plan['employeeId'] ?? 0)
            : (int) ($plan['teamLeadId'] ?? 0);

        $this->service->saveFeedback($planId, $userId, $toUserId, $type, $message);

        $this->assignDisplay($planId, $type, $message);
    }

    /** Re-render the display block without saving (cancel). */
    public function view(): void
    {
        $planId = (int) $this->incomingRequest->query->get('planId', 0);
        $plan = $this->service->getPlanById($planId);

        if (! $this->canAccessPlan($plan)) {
            $this->denyAccess();

            return;
        }

        $type = (string) $this->incomingRequest->query->get('type', '');
        $feedback = $this->service->getFeedbackForPlan($planId);
        $existing = collect($feedback)->firstWhere('type', $type);

        $this->assignDisplay($planId, $type, $existing['message'] ?? '');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function resolveCanEdit(string $type): bool
    {
        $role = session('userdata.role');
        $isTeamLead = in_array($role, [Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        return ($isTeamLead && str_starts_with($type, 'manager_'))
            || (! $isTeamLead && str_starts_with($type, 'employee_'));
    }

    /**
     * Whether the current user is a participant in this plan (the employee or
     * the team lead) — or a manager/admin/owner who can see any plan.
     * Mirrors the access rule used by ShowPlan.
     */
    private function canAccessPlan(?array $plan): bool
    {
        if ($plan === null) {
            return false;
        }

        if (Auth::userIsAtLeast(Roles::$manager, true)) {
            return true;
        }

        $uid = (int) session('userdata.id');

        return (int) ($plan['employeeId'] ?? 0) === $uid
            || (int) ($plan['teamLeadId'] ?? 0) === $uid;
    }

    /** Emit a 403 notification for a denied feedback action. */
    private function denyAccess(): void
    {
        $this->tpl->setNotification('errors.error403', 'error');
        $this->setHTMXEvent('HTMX.ShowNotification');
    }

    private function assignDisplay(int $planId, string $type, string $message): void
    {
        $this->tpl->assign('planId', $planId);
        $this->tpl->assign('type', $type);
        $this->tpl->assign('message', $message);
        $this->tpl->assign('canEdit', $this->resolveCanEdit($type));
        $this->tpl->assign('feedbackTypes', $this->service->feedbackTypes);
    }
}
