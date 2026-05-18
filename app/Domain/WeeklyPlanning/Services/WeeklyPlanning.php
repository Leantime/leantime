<?php

namespace Leantime\Domain\WeeklyPlanning\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Notifications\Services\Notifications as NotificationsService;
use Leantime\Domain\Oneonone\Services\Oneonone as OneononeService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketsRepo;
use Leantime\Domain\WeeklyPlanning\Models\WeeklyPlan;
use Leantime\Domain\WeeklyPlanning\Models\WeeklyPlanCommitment;
use Leantime\Domain\WeeklyPlanning\Models\WeeklyPlanFeedback;
use Leantime\Domain\WeeklyPlanning\Models\WeeklyPlanItem;
use Leantime\Domain\WeeklyPlanning\Repositories\WeeklyPlanning as WeeklyPlanningRepo;

/**
 * WeeklyPlanning service — business logic for the weekly team assignment workflow.
 */
class WeeklyPlanning
{
    use DispatchesEvents;

    /** Valid item statuses. */
    public array $itemStatuses = [
        'not_started' => 'weeklyplanning.status.not_started',
        'in_progress' => 'weeklyplanning.status.in_progress',
        'blocked' => 'weeklyplanning.status.blocked',
        'completed' => 'weeklyplanning.status.completed',
        'not_completed' => 'weeklyplanning.status.not_completed',
    ];

    /** Statuses that require a reason from the employee. */
    public array $reasonRequiredStatuses = ['blocked', 'not_completed'];

    /** 4-direction feedback types. */
    public array $feedbackTypes = [
        'manager_to_employee_working' => 'weeklyplanning.feedback.manager_to_employee_working',
        'manager_to_employee_improve' => 'weeklyplanning.feedback.manager_to_employee_improve',
        'employee_to_manager_helping' => 'weeklyplanning.feedback.employee_to_manager_helping',
        'employee_to_manager_improve' => 'weeklyplanning.feedback.employee_to_manager_improve',
    ];

    public function __construct(
        private WeeklyPlanningRepo $repo,
        private NotificationsService $notificationsService,
        private OneononeService $oneononeService,
        private TicketsRepo $ticketsRepo,
    ) {}

    // -------------------------------------------------------------------------
    // Plans
    // -------------------------------------------------------------------------

    /**
     * @api
     */
    public function getPlanById(int $id): ?array
    {
        return $this->repo->getPlanById($id);
    }

    /**
     * Get current-week plan for an employee, or null if none exists yet.
     *
     * @api
     */
    public function getCurrentPlanForEmployee(int $employeeId): ?array
    {
        return $this->repo->getCurrentPlanForEmployee($employeeId);
    }

    /**
     * Get all plans for an employee (history), newest first.
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPlansForEmployee(int $employeeId): array
    {
        return $this->repo->getPlansForEmployee($employeeId);
    }

    /**
     * Return all plans for an employee that fall within a given month, grouped by
     * the calendar week they belong to. Each entry contains the plan + its items.
     *
     * Returns an array of week-slot arrays, ordered by weekStart ascending:
     * [
     *   ['weekNum' => 1, 'weekStart' => '2026-05-04', 'weekEnd' => '2026-05-10', 'plan' => [...|null], 'items' => [...] ],
     *   ...
     * ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMonthWeekSlots(int $employeeId, int $year, int $month): array
    {
        $monthStart = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->endOfMonth()->endOfDay();

        // Always start on Monday so slots align with how plans store weekStart (Monday).
        $slots = [];
        $weekStart = $monthStart->startOfWeek(\Carbon\Carbon::MONDAY);

        while ($weekStart->lte($monthEnd)) {
            $weekEnd = $weekStart->endOfWeek(\Carbon\Carbon::SUNDAY);
            $slots[] = [
                'weekNum' => (int) $weekStart->format('W'),
                'weekStart' => $weekStart->toDateString(),
                'weekEnd' => $weekEnd->toDateString(),
                'plan' => null,
                'items' => [],
            ];
            $weekStart = $weekStart->addWeek();
        }

        // Fetch all plans for the employee in this month and match to slots.
        $allPlans = $this->repo->getPlansForEmployee($employeeId);
        $monthPlans = array_filter($allPlans, function (array $p) use ($monthStart, $monthEnd) {
            $ws = CarbonImmutable::parse($p['weekStart']);
            $we = CarbonImmutable::parse($p['weekEnd']);

            return $ws->between($monthStart, $monthEnd) || $we->between($monthStart, $monthEnd);
        });

        foreach ($monthPlans as $plan) {
            $planStart = CarbonImmutable::parse($plan['weekStart']);
            foreach ($slots as &$slot) {
                $slotStart = CarbonImmutable::parse($slot['weekStart']);
                $slotEnd = CarbonImmutable::parse($slot['weekEnd']);
                // Match: plan's weekStart falls anywhere within the slot's Mon–Sun range.
                if ($planStart->between($slotStart, $slotEnd)) {
                    // Prefer TL-assigned plans over self-created ones.
                    $existingHasTL = ! empty($slot['plan']['teamLeadId']);
                    $newHasTL = ! empty($plan['teamLeadId']);
                    if ($slot['plan'] === null || ($newHasTL && ! $existingHasTL)) {
                        $slot['plan'] = $plan;
                        $slot['items'] = $this->repo->getItemsForPlan((int) $plan['id']);
                    }
                    break;
                }
            }
            unset($slot);
        }

        return $slots;
    }

    /**
     * Get all plans for a team lead, optionally filtered by month label.
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPlansForTeamLead(int $teamLeadId, ?string $month = null): array
    {
        return $this->repo->getPlansForTeamLead($teamLeadId, $month);
    }

    /**
     * Distinct months that have at least one plan for this team lead.
     *
     * @api
     *
     * @return array<int, string>
     */
    public function getMonthsForTeamLead(int $teamLeadId): array
    {
        return $this->repo->getMonthsForTeamLead($teamLeadId);
    }

    /**
     * Create a new weekly plan for an employee.
     * weekStart must be a Monday; weekEnd is computed as the following Friday.
     *
     * @api
     *
     * @param  array<string, mixed>  $params
     */
    public function createPlan(array $params): int|false
    {
        try {
            $plan = new WeeklyPlan;
            $plan->employeeId = (int) ($params['employeeId'] ?? 0);
            $plan->teamLeadId = (int) ($params['teamLeadId'] ?? session('userdata.id'));
            $plan->weekStart = $params['weekStart'] ?? now()->startOfWeek()->toDateString();
            $plan->weekEnd = $params['weekEnd'] ?? now()->endOfWeek()->toDateString();
            $plan->month = $params['month'] ?? now()->format('M y');
            $plan->weekLabel = $params['weekLabel'] ?? $this->generateWeekLabel($plan->weekStart);
            $plan->dateOfOneOnOne = $params['dateOfOneOnOne'] ?? null;
            $plan->status = 'active';

            $planId = $this->repo->createPlan($plan);

            // When a 1:1 date is set on the plan, automatically create a real
            // 1:1 session record so it appears on both the TL's and employee's
            // "1:1 Sessions" pages. The datetime is stored as noon UTC on that day
            // to avoid timezone edge cases with a date-only value.
            if ($planId && ! empty($plan->dateOfOneOnOne)) {
                try {
                    $this->oneononeService->scheduleSession([
                        'employeeId' => $plan->employeeId,
                        'meetingDate' => $plan->dateOfOneOnOne.'T12:00:00Z',
                        'title' => 'Weekly 1:1 — '.$plan->weekLabel,
                    ]);
                } catch (\Throwable $e) {
                    // Non-fatal: plan was saved; log but don't fail the whole request.
                    Log::error('WeeklyPlanning::createPlan — could not auto-create 1:1 session: '.$e->getMessage());
                }
            }

            return $planId;
        } catch (\Throwable $e) {
            Log::error('WeeklyPlanning::createPlan — '.$e->getMessage());

            return false;
        }
    }

    /**
     * Update text sections of a plan (called when employee or TL saves their sections).
     *
     * @api
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePlan(int $id, array $data): bool
    {
        $allowed = [
            'topPriorities',
            'winsAndProgress',
            'challengesAndBlockers',
            'managerSupportNeeded',
            'ideasAndSuggestions',
            'growthCurrentFocus',
            'growthSupportNeeded',
            'growthNextMilestone',
            'nextWeekPriorities',
            'summary',
            'status',
            'dateOfOneOnOne',
        ];

        // Snapshot the current date before the update to detect when it actually changes.
        $currentPlan = $this->repo->getPlanById($id);
        $oldDate = $currentPlan['dateOfOneOnOne'] ?? null;

        $result = $this->repo->updatePlan($id, array_intersect_key($data, array_flip($allowed)));

        // Only schedule a new 1:1 session when the date is first set or explicitly changed.
        $newDate = $data['dateOfOneOnOne'] ?? null;
        if ($result && ! empty($newDate) && $newDate !== $oldDate && $currentPlan) {
            try {
                $this->oneononeService->scheduleSession([
                    'employeeId' => (int) ($currentPlan['employeeId'] ?? 0),
                    'meetingDate' => $newDate.'T12:00:00Z',
                    'title' => 'Weekly 1:1 — '.($currentPlan['weekLabel'] ?? ''),
                ]);
            } catch (\Throwable $e) {
                Log::error('WeeklyPlanning::updatePlan — could not auto-create 1:1 session: '.$e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Delete a plan. Only the team lead who created it or an admin may delete.
     *
     * @api
     */
    public function deletePlan(int $id): bool
    {
        $plan = $this->repo->getPlanById($id);
        if (! $plan) {
            return false;
        }

        $userId = (int) (session('userdata.id') ?? 0);

        $isOwner = (int) ($plan['teamLeadId'] ?? 0) === $userId;
        $isAdmin = \Leantime\Domain\Auth\Services\Auth::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$admin);

        if (! $isOwner && ! $isAdmin) {
            return false;
        }

        try {
            return $this->repo->deletePlan($id);
        } catch (\Throwable $e) {
            Log::error('WeeklyPlanning::deletePlan — '.$e->getMessage());

            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Items
    // -------------------------------------------------------------------------

    /**
     * Get items for a plan and auto-sync item status from the linked ticket's current status.
     * If the ticket has been marked DONE in Leantime, the plan item is automatically set to
     * 'completed'. This keeps the weekly plan in sync without requiring manual double-entry.
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getItemsForPlan(int $planId): array
    {
        $items = $this->repo->getItemsForPlan($planId);

        // Build a map of projectId -> status labels (cached per project within this call)
        $labelCache = [];

        foreach ($items as &$item) {
            $ticketId = isset($item['ticketId']) ? (int) $item['ticketId'] : 0;
            $ticketProjectId = isset($item['ticketProjectId']) ? (int) $item['ticketProjectId'] : 0;
            $ticketStatus = $item['ticketStatus'] ?? null;
            $currentStatus = $item['status'] ?? 'not_started';

            if (! $ticketId || $ticketProjectId === 0 || $ticketStatus === null) {
                continue;
            }

            if (! isset($labelCache[$ticketProjectId])) {
                $labelCache[$ticketProjectId] = $this->ticketsRepo->getStateLabels($ticketProjectId);
            }

            $labels = $labelCache[$ticketProjectId];
            $statusType = $labels[$ticketStatus]['statusType'] ?? null;
            $isDone = $statusType === 'DONE';
            $isInProgress = $statusType === 'INPROGRESS';

            // Sync DONE: ticket done but item not yet marked completed
            if ($isDone && $currentStatus !== 'completed') {
                $this->repo->updateItem((int) $item['id'], ['status' => 'completed']);
                $item['status'] = 'completed';
            }

            // Sync re-opened: ticket back to in-progress but item was completed
            if ($isInProgress && $currentStatus === 'completed') {
                $this->repo->updateItem((int) $item['id'], ['status' => 'in_progress']);
                $item['status'] = 'in_progress';
            }
        }
        unset($item);

        return $items;
    }

    /**
     * Get all ticket IDs linked to the current week's plan for a given employee.
     * Returns an empty array if there is no current plan or no linked tickets.
     *
     * @api
     *
     * @return int[]
     */
    public function getCurrentPlanTicketIds(int $employeeId): array
    {
        $currentUserId = (int) (session('userdata.id') ?? 0);
        if ($employeeId !== $currentUserId && ! Auth::userIsAtLeast(Roles::$teamlead)) {
            return [];
        }

        $plan = $this->repo->getCurrentPlanForEmployee($employeeId);
        if (! $plan) {
            return [];
        }

        $items = $this->repo->getItemsForPlan((int) $plan['id']);

        return array_values(array_filter(array_map(
            fn (array $item) => isset($item['ticketId']) ? (int) $item['ticketId'] : null,
            $items
        )));
    }

    /**
     * @api
     *
     * @return array<string, mixed>|null
     */
    public function getItemById(int $itemId): ?array
    {
        return $this->repo->getItemById($itemId);
    }

    /**
     * Add a task to a weekly plan (free-text title or linked ticket).
     *
     * @api
     */
    public function addItem(int $planId, ?int $ticketId, ?string $expectedOutcome = null): int|false
    {
        try {
            $item = new WeeklyPlanItem;
            $item->weeklyPlanId = $planId;
            $item->ticketId = $ticketId;
            $item->expectedOutcome = $expectedOutcome;
            $item->priority = $this->nextPriority($planId);

            return $this->repo->addItem($item);
        } catch (\Throwable $e) {
            Log::error('WeeklyPlanning::addItem — '.$e->getMessage());

            return false;
        }
    }

    /**
     * Update an item's status.
     * When $enforceReason is true (employee/developer role), blocked and not_completed
     * statuses require a completionReason. Team Leads and above bypass this check.
     *
     * @api
     *
     * @param  array<string, mixed>  $data  Keys: status, completionReason, supportNeeded, newDueDate
     * @param  bool  $enforceReason  Pass false to skip the reason requirement (Team Lead+)
     */
    public function updateItemStatus(int $itemId, array $data, bool $enforceReason = true): bool|string
    {
        $status = $data['status'] ?? '';

        if ($enforceReason && in_array($status, $this->reasonRequiredStatuses, true) && empty($data['completionReason'])) {
            return 'reason_required';
        }

        $allowed = ['status', 'completionReason', 'supportNeeded', 'newDueDate'];

        return $this->repo->updateItem($itemId, array_intersect_key($data, array_flip($allowed)));
    }

    /**
     * Remove a task from a plan.
     *
     * @api
     */
    public function removeItem(int $itemId): bool
    {
        return $this->repo->deleteItem($itemId);
    }

    // -------------------------------------------------------------------------
    // Feedback
    // -------------------------------------------------------------------------

    /**
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFeedbackForPlan(int $planId): array
    {
        return $this->repo->getFeedbackForPlan($planId);
    }

    /**
     * Save one feedback entry. Upserts by (planId, type).
     *
     * @api
     */
    public function saveFeedback(int $planId, int $fromUserId, int $toUserId, string $type, string $message): bool
    {
        if (! array_key_exists($type, $this->feedbackTypes)) {
            return false;
        }

        $feedback = new WeeklyPlanFeedback;
        $feedback->weeklyPlanId = $planId;
        $feedback->fromUserId = $fromUserId;
        $feedback->toUserId = $toUserId;
        $feedback->type = $type;
        $feedback->message = $message;

        return $this->repo->saveFeedback($feedback);
    }

    // -------------------------------------------------------------------------
    // Commitments
    // -------------------------------------------------------------------------

    /**
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCommitmentsForPlan(int $planId): array
    {
        return $this->repo->getCommitmentsForPlan($planId);
    }

    /**
     * @api
     */
    public function addCommitment(int $planId, string $task, int $ownerId, string $deadline): int|false
    {
        try {
            $c = new WeeklyPlanCommitment;
            $c->weeklyPlanId = $planId;
            $c->task = $task;
            $c->ownerId = $ownerId;
            $c->deadline = $deadline;

            return $this->repo->addCommitment($c);
        } catch (\Throwable $e) {
            Log::error('WeeklyPlanning::addCommitment — '.$e->getMessage());

            return false;
        }
    }

    /**
     * @api
     */
    public function markCommitmentDone(int $commitmentId): bool
    {
        return $this->repo->updateCommitmentStatus($commitmentId, 'done');
    }

    // -------------------------------------------------------------------------
    // Team
    // -------------------------------------------------------------------------

    /**
     * Get a single commitment by ID.
     *
     * @api
     *
     * @return array<string, mixed>|null
     */
    public function getCommitmentById(int $id): ?array
    {
        return $this->repo->getCommitmentById($id);
    }

    /**
     * Get direct reports for a team lead (no plan summary).
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTeamMembers(int $teamLeadId): array
    {
        return $this->repo->getTeamMembers($teamLeadId);
    }

    /**
     * Get direct reports for a team lead with their current-week plan summary.
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTeamDashboard(int $teamLeadId, ?string $month = null): array
    {
        $members = $this->repo->getTeamMembers($teamLeadId);
        $plans = $this->getPlansForTeamLead($teamLeadId, $month);

        // Index plans by employeeId for quick lookup
        $plansByEmployee = [];
        foreach ($plans as $plan) {
            $plansByEmployee[$plan['employeeId']][] = $plan;
        }

        foreach ($members as &$member) {
            $memberPlans = $plansByEmployee[$member['id']] ?? [];

            // Attach items to each plan (triggers ticket-status sync via getItemsForPlan)
            foreach ($memberPlans as &$plan) {
                $plan['_items'] = $this->getItemsForPlan((int) $plan['id']);
            }
            unset($plan);

            $member['plans'] = $memberPlans;
            $member['planCount'] = count($memberPlans);
            $member['currentPlan'] = $this->repo->getCurrentPlanForEmployee($member['id']);
        }

        return $members;
    }

    /**
     * All blocked / not_completed items across a team lead's reports.
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBlockedItemsForTeamLead(int $teamLeadId): array
    {
        return $this->repo->getBlockedItemsForTeamLead($teamLeadId);
    }

    /**
     * All commitments across a team lead's reports.
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCommitmentsForTeamLead(int $teamLeadId, bool $openOnly = false): array
    {
        return $this->repo->getCommitmentsForTeamLead($teamLeadId, $openOnly);
    }

    /**
     * Carry over unfinished items from one plan to the next-week plan for the same employee.
     *
     * Returns:
     *   - 'success'    : items were copied
     *   - 'no_target'  : no next-week plan exists (caller must create one first)
     *   - 'nothing'    : nothing to move (all items completed)
     *
     * @api
     */
    public function carryOverUnfinished(int $sourcePlanId): string
    {
        $sourcePlan = $this->repo->getPlanById($sourcePlanId);
        if (! $sourcePlan) {
            return 'no_target';
        }

        $nextWeekStart = \Carbon\Carbon::parse($sourcePlan['weekStart'])->addWeek()->toDateString();
        $nextWeekEnd = \Carbon\Carbon::parse($sourcePlan['weekEnd'])->addWeek()->toDateString();

        $candidates = $this->repo->getPlansForEmployee((int) $sourcePlan['employeeId']);
        $targetPlanId = null;
        foreach ($candidates as $c) {
            if ($c['weekStart'] === $nextWeekStart && $c['weekEnd'] === $nextWeekEnd) {
                $targetPlanId = (int) $c['id'];
                break;
            }
        }

        if (! $targetPlanId) {
            return 'no_target';
        }

        $items = $this->repo->getItemsForPlan($sourcePlanId);
        if (empty($items)) {
            return 'nothing';
        }

        // Move ALL items: copy to next week, then delete from this week.
        foreach ($items as $item) {
            $this->addItem(
                $targetPlanId,
                $item['ticketId'] ? (int) $item['ticketId'] : null,
                $item['expectedOutcome'] ?? null
            );
            $this->repo->deleteItem((int) $item['id']);
        }

        // Notify the developer (plan owner) that their tasks were carried over.
        $actorId = (int) session('userdata.id');
        $actorName = session('userdata.name') ?? '';
        $employeeId = (int) $sourcePlan['employeeId'];

        if ($employeeId && $employeeId !== $actorId) {
            $this->notificationsService->addNotifications([
                [
                    'userId' => $employeeId,
                    'type' => 'info',
                    'module' => 'weeklyplanning',
                    'moduleId' => $targetPlanId,
                    'message' => sprintf(
                        __('weeklyplanning.text.carry_over_notification'),
                        $actorName,
                        $nextWeekStart
                    ),
                    'datetime' => CarbonImmutable::now()->toDateTimeString(),
                    'url' => '/weekly-planning/myPlan',
                    'authorId' => $actorId,
                ],
            ]);
        }

        return 'success';
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Generate a human-readable week label like "1st Week", "2nd Week".
     */
    private function generateWeekLabel(string $weekStart): string
    {
        $weekOfMonth = (int) date('W', strtotime($weekStart)) - (int) date('W', strtotime(date('Y-m-01', strtotime($weekStart)))) + 1;
        $suffixes = ['', '1st', '2nd', '3rd', '4th', '5th'];

        return ($suffixes[$weekOfMonth] ?? $weekOfMonth.'th').' Week';
    }

    /**
     * Get the next priority index for items in a plan.
     */
    private function nextPriority(int $planId): int
    {
        $items = $this->repo->getItemsForPlan($planId);

        return count($items);
    }
}
