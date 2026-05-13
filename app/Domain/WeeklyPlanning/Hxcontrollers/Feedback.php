<?php

namespace Leantime\Domain\WeeklyPlanning\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;

/**
 * Feedback — HTMX controller for 4-direction feedback entries.
 *
 * GET  /hx/weeklyplanning/feedback/editForm?planId=X&type=Y  → feedback edit form
 * POST /hx/weeklyplanning/feedback/save                       → save and re-render display
 * GET  /hx/weeklyplanning/feedback/view?planId=X&type=Y       → re-render display (cancel)
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
        static::$view = 'weeklyplanning::partials.feedbackForm';

        $planId   = (int) $this->incomingRequest->query->get('planId', 0);
        $type     = (string) $this->incomingRequest->query->get('type', '');
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
        $planId  = (int) ($this->incomingRequest->request->get('planId') ?? 0);
        $type    = (string) ($this->incomingRequest->request->get('type') ?? '');
        $message = trim((string) ($this->incomingRequest->request->get('message') ?? ''));
        $userId  = (int) session('userdata.id');
        $plan    = $this->service->getPlanById($planId);

        $toUserId = str_starts_with($type, 'manager_')
            ? (int) ($plan['employeeId'] ?? 0)
            : (int) ($plan['teamLeadId'] ?? 0);

        $this->service->saveFeedback($planId, $userId, $toUserId, $type, $message);

        $this->assignDisplay($planId, $type, $message);
    }

    /** Re-render the display block without saving (cancel). */
    public function view(): void
    {
        $planId  = (int) $this->incomingRequest->query->get('planId', 0);
        $type    = (string) $this->incomingRequest->query->get('type', '');
        $feedback = $this->service->getFeedbackForPlan($planId);
        $existing = collect($feedback)->firstWhere('type', $type);

        $this->assignDisplay($planId, $type, $existing['message'] ?? '');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function resolveCanEdit(string $type): bool
    {
        $role       = session('userdata.role');
        $isTeamLead = in_array($role, [Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        return ($isTeamLead && str_starts_with($type, 'manager_'))
            || (! $isTeamLead && str_starts_with($type, 'employee_'));
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
