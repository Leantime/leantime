<?php

namespace Leantime\Domain\WeeklyPlanning\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;

/**
 * StatusUpdate — inline task status control rendered via HTMX.
 *
 * GET  /hx/weeklyplanning/statusUpdate/get?itemId=X  → renders status dropdown
 * POST /hx/weeklyplanning/statusUpdate/save          → saves status, re-renders control
 *
 * The rendered partial (statusControl) shows the dropdown plus a blocker-reason
 * form inline when the selected status requires a reason.
 */
class StatusUpdate extends HtmxController
{
    protected static string $view = 'weeklyplanning::partials.statusControl';

    private WeeklyPlanningService $service;

    public function init(WeeklyPlanningService $service): void
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $this->service = $service;
    }

    /** Render the status dropdown for one item. */
    public function get(): void
    {
        $itemId = (int) ($_GET['itemId'] ?? 0);
        $item   = $this->service->getItemById($itemId);

        $this->assignCommon($item);
    }

    /** Save a status update, then re-render the control. */
    public function save(): void
    {
        $itemId  = (int) ($_POST['itemId'] ?? 0);
        $status  = $_POST['status'] ?? '';
        $reason  = $_POST['completionReason'] ?? '';
        $support = $_POST['supportNeeded'] ?? '';
        $newDue  = $_POST['newDueDate'] ?? null;

        // Guard: only the assigned employee may change item status.
        $item = $this->service->getItemById($itemId);
        if ($item) {
            $plan      = $this->service->getPlanById((int) $item['weeklyPlanId']);
            $currentId = (int) session('userdata.id');
            if ($plan && (int) $plan['employeeId'] !== $currentId) {
                // Non-employee (e.g. Team Lead) attempted to change status — silently re-render read-only.
                $this->assignCommon($item);

                return;
            }
        }

        $result = $this->service->updateItemStatus($itemId, [
            'status'           => $status,
            'completionReason' => $reason,
            'supportNeeded'    => $support,
            'newDueDate'       => $newDue ?: null,
        ], $this->currentUserIsEmployee());

        $updatedItem = $this->service->getItemById($itemId);

        // Pass validation error flag so the template can show inline error
        $this->tpl->assign('reasonRequired', $result === 'reason_required');
        $this->tpl->assign('selectedStatus', $status);
        $this->assignCommon($updatedItem);

        if ($result !== 'reason_required') {
            $this->setHTMXEvent('weeklyplan_item_updated');
        }
    }

    /**
     * Whether the current user is a plain employee (editor/developer).
     * Only employees must provide a reason for blocked / not-completed statuses.
     * Team Leads and above can change statuses freely.
     */
    private function currentUserIsEmployee(): bool
    {
        $role = session('userdata.role');

        return $role === Roles::$editor;
    }

    /** @param array<string, mixed>|null $item */
    private function assignCommon(?array $item): void
    {
        $this->tpl->assign('item', $item);
        $this->tpl->assign('itemStatuses', $this->service->itemStatuses);
        $this->tpl->assign('reasonRequiredStatuses', $this->service->reasonRequiredStatuses);
        $this->tpl->assign('isEmployee', $this->currentUserIsEmployee());
    }
}
