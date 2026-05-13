<?php

namespace Leantime\Domain\WeeklyPlanning\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;

/**
 * PlanItems — Team Lead actions: add/remove tasks, edit text sections, manage commitments.
 *
 * All mutation methods re-render the relevant list section after the change,
 * following the same pattern used by Oneonone\Hxcontrollers\SessionItems.
 */
class PlanItems extends HtmxController
{
    protected static string $view = 'weeklyplanning::partials.planItemsList';

    private WeeklyPlanningService $service;

    private TicketService $ticketService;

    public function init(WeeklyPlanningService $service, TicketService $ticketService): void
    {
        Auth::authOrRedirect([Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $this->service       = $service;
        $this->ticketService = $ticketService;
    }

    /** Render the task add form. */
    public function addForm(): void
    {
        static::$view = 'weeklyplanning::partials.addItemForm';

        $planId = (int) $this->incomingRequest->query->get('planId', 0);

        $this->tpl->assign('planId', $planId);
    }

    /** Add a task to the plan, then re-render the items list. */
    public function add(): void
    {
        $planId          = (int) ($this->incomingRequest->request->get('planId') ?? 0);
        $expectedOutcome = trim((string) ($this->incomingRequest->request->get('expectedOutcome') ?? ''));

        if ($expectedOutcome) {
            $this->service->addItem($planId, null, $expectedOutcome);
        }

        $this->setHTMXEvent('weeklyplan_item_updated');
        $this->renderItemsList($planId);
    }

    /** Remove a plan item, then re-render the items list. */
    public function remove(): void
    {
        $itemId = (int) ($this->incomingRequest->query->get('itemId') ?? 0);
        $item   = $this->service->getItemById($itemId);
        $planId = $item ? (int) $item['weeklyPlanId'] : 0;

        $this->service->removeItem($itemId);
        $this->setHTMXEvent('weeklyplan_item_updated');
        $this->renderItemsList($planId);
    }

    /** Render an inline text editor for one plan section. */
    public function editSection(): void
    {
        static::$view = 'weeklyplanning::partials.editSectionForm';

        $planId = (int) $this->incomingRequest->query->get('planId', 0);
        $field  = (string) $this->incomingRequest->query->get('field', '');
        $plan   = $this->service->getPlanById($planId);

        $this->tpl->assign('planId', $planId);
        $this->tpl->assign('field', $field);
        $this->tpl->assign('currentValue', $plan[$field] ?? '');
    }

    /** Re-render the display view for a section without saving (cancel action). */
    public function viewSection(): void
    {
        static::$view = 'weeklyplanning::partials.sectionDisplay';

        $planId = (int) $this->incomingRequest->query->get('planId', 0);
        $field  = (string) $this->incomingRequest->query->get('field', '');
        $plan   = $this->service->getPlanById($planId);

        $this->tpl->assign('planId', $planId);
        $this->tpl->assign('field', $field);
        $this->tpl->assign('savedValue', $plan[$field] ?? '');
    }

    /** Save a text section, then re-render the display view. */
    public function saveSection(): void
    {
        static::$view = 'weeklyplanning::partials.sectionDisplay';

        $planId = (int) ($this->incomingRequest->request->get('planId') ?? 0);
        $field  = (string) ($this->incomingRequest->request->get('field') ?? '');
        $value  = (string) ($this->incomingRequest->request->get('value') ?? '');

        $this->service->updatePlan($planId, [$field => $value]);

        $this->tpl->assign('planId', $planId);
        $this->tpl->assign('field', $field);
        $this->tpl->assign('savedValue', $value);
    }

    /** Render the commitment add form. */
    public function commitmentForm(): void
    {
        static::$view = 'weeklyplanning::partials.addCommitmentForm';

        $planId = (int) $this->incomingRequest->query->get('planId', 0);
        $plan   = $this->service->getPlanById($planId);

        // Provide team members as owner options
        $teamMembers = $plan ? $this->service->getTeamMembers((int) $plan['teamLeadId']) : [];

        $this->tpl->assign('planId', $planId);
        $this->tpl->assign('teamMembers', $teamMembers);
    }

    /** Save a new commitment, then re-render the commitments list. */
    public function addCommitment(): void
    {
        $planId  = (int) ($this->incomingRequest->request->get('planId') ?? 0);
        $task    = trim((string) ($this->incomingRequest->request->get('task') ?? ''));
        $ownerId = (int) ($this->incomingRequest->request->get('ownerId') ?? session('userdata.id'));
        $deadline = (string) ($this->incomingRequest->request->get('deadline') ?? '');

        if ($task && $deadline) {
            $this->service->addCommitment($planId, $task, $ownerId, $deadline);
        }

        $this->renderCommitmentsList($planId);
    }

    /** Mark a commitment done, then re-render the commitments list. */
    public function markCommitmentDone(): void
    {
        $commitmentId = (int) ($this->incomingRequest->request->get('commitmentId')
            ?? $this->incomingRequest->query->get('commitmentId', 0));

        $commitment = $this->service->getCommitmentById($commitmentId);
        $planId     = $commitment ? (int) $commitment['weeklyPlanId'] : 0;

        $this->service->markCommitmentDone($commitmentId);
        $this->renderCommitmentsList($planId);
    }

    /** Carry unfinished items over to next week's plan, then re-render the items list. */
    public function carryOver(): void
    {
        $sourcePlanId = (int) ($this->incomingRequest->request->get('planId')
            ?? $this->incomingRequest->query->get('planId', 0));

        $result = $this->service->carryOverUnfinished($sourcePlanId);

        $messages = [
            'success'   => ['weeklyplanning.text.carry_over_success', 'success'],
            'no_target' => ['weeklyplanning.text.carry_over_no_target', 'error'],
            'nothing'   => ['weeklyplanning.text.carry_over_none', 'info'],
        ];
        [$key, $type] = $messages[$result] ?? $messages['nothing'];
        $this->tpl->setNotification(__($key), $type);
        $this->setHTMXEvent('HTMX.ShowNotification');

        $this->renderItemsList($sourcePlanId);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function renderItemsList(int $planId): void
    {
        static::$view = 'weeklyplanning::partials.planItemsList';

        $items = $this->service->getItemsForPlan($planId);

        $this->tpl->assign('planId', $planId);
        $this->tpl->assign('items', $items);
        $this->tpl->assign('itemStatuses', $this->service->itemStatuses);
        $this->tpl->assign('isTeamLead', true);
    }

    private function renderCommitmentsList(int $planId): void
    {
        static::$view = 'weeklyplanning::partials.commitmentsList';

        $commitments = $this->service->getCommitmentsForPlan($planId);

        $this->tpl->assign('commitments', $commitments);
        $this->tpl->assign('isTeamLead', true);
    }
}
