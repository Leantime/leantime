<?php

namespace Leantime\Domain\WeeklyPlanning\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\WeeklyPlanning\Models\WeeklyPlan;
use Leantime\Domain\WeeklyPlanning\Models\WeeklyPlanCommitment;
use Leantime\Domain\WeeklyPlanning\Models\WeeklyPlanFeedback;
use Leantime\Domain\WeeklyPlanning\Models\WeeklyPlanItem;

/**
 * WeeklyPlanning repository — all DB access for weekly plans, items, feedback, and commitments.
 */
class WeeklyPlanning
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    // -------------------------------------------------------------------------
    // Plans
    // -------------------------------------------------------------------------

    /**
     * Get one plan by ID with employee and team lead names joined.
     *
     * @return array<string, mixed>|null
     */
    public function getPlanById(int $id): ?array
    {
        $row = $this->db->table('zp_weekly_plans as p')
            ->select(
                'p.*',
                'e.firstname as employeeFirstname',
                'e.lastname as employeeLastname',
                'e.profileId as employeeProfileId',
                'l.firstname as teamLeadFirstname',
                'l.lastname as teamLeadLastname',
            )
            ->leftJoin('zp_user as e', 'e.id', '=', 'p.employeeId')
            ->leftJoin('zp_user as l', 'l.id', '=', 'p.teamLeadId')
            ->where('p.id', $id)
            ->first();

        return $row ? (array) $row : null;
    }

    /**
     * Get all plans for one employee, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPlansForEmployee(int $employeeId): array
    {
        $rows = $this->db->table('zp_weekly_plans as p')
            ->select(
                'p.*',
                'e.firstname as employeeFirstname',
                'e.lastname as employeeLastname',
                'l.firstname as teamLeadFirstname',
                'l.lastname as teamLeadLastname',
            )
            ->leftJoin('zp_user as e', 'e.id', '=', 'p.employeeId')
            ->leftJoin('zp_user as l', 'l.id', '=', 'p.teamLeadId')
            ->where('p.employeeId', $employeeId)
            ->orderByDesc('p.weekStart')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Get the current-week plan for one employee (weekStart <= today <= weekEnd).
     *
     * @return array<string, mixed>|null
     */
    public function getCurrentPlanForEmployee(int $employeeId): ?array
    {
        $today = now()->toDateString();

        $row = $this->db->table('zp_weekly_plans as p')
            ->select(
                'p.*',
                'e.firstname as employeeFirstname',
                'e.lastname as employeeLastname',
                'l.firstname as teamLeadFirstname',
                'l.lastname as teamLeadLastname',
            )
            ->leftJoin('zp_user as e', 'e.id', '=', 'p.employeeId')
            ->leftJoin('zp_user as l', 'l.id', '=', 'p.teamLeadId')
            ->where('p.employeeId', $employeeId)
            ->where('p.weekStart', '<=', $today)
            ->where('p.weekEnd', '>=', $today)
            ->orderByDesc('p.weekStart')
            ->first();

        return $row ? (array) $row : null;
    }

    /**
     * Get all plans managed by a team lead, grouped by month, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPlansForTeamLead(int $teamLeadId, ?string $month = null): array
    {
        $query = $this->db->table('zp_weekly_plans as p')
            ->select(
                'p.*',
                'e.firstname as employeeFirstname',
                'e.lastname as employeeLastname',
                'e.profileId as employeeProfileId',
            )
            ->leftJoin('zp_user as e', 'e.id', '=', 'p.employeeId')
            ->where('p.teamLeadId', $teamLeadId)
            ->orderByDesc('p.weekStart');

        if ($month !== null) {
            $query->where('p.month', $month);
        }

        return array_map(fn ($r) => (array) $r, $query->get()->toArray());
    }

    /**
     * Get distinct months that have plans for a team lead.
     *
     * @return array<int, string>
     */
    public function getMonthsForTeamLead(int $teamLeadId): array
    {
        return $this->db->table('zp_weekly_plans')
            ->where('teamLeadId', $teamLeadId)
            ->whereNotNull('month')
            ->distinct()
            ->orderByDesc('weekStart')
            ->pluck('month')
            ->toArray();
    }

    /**
     * Create a new weekly plan. Returns the new plan ID.
     */
    public function createPlan(WeeklyPlan $plan): int
    {
        return $this->db->table('zp_weekly_plans')->insertGetId([
            'employeeId'            => $plan->employeeId,
            'teamLeadId'            => $plan->teamLeadId,
            'month'                 => $plan->month,
            'weekLabel'             => $plan->weekLabel,
            'weekStart'             => $plan->weekStart,
            'weekEnd'               => $plan->weekEnd,
            'dateOfOneOnOne'        => $plan->dateOfOneOnOne,
            'status'                => $plan->status,
            'topPriorities'         => $plan->topPriorities,
            'winsAndProgress'       => $plan->winsAndProgress,
            'challengesAndBlockers' => $plan->challengesAndBlockers,
            'managerSupportNeeded'  => $plan->managerSupportNeeded,
            'ideasAndSuggestions'   => $plan->ideasAndSuggestions,
            'growthCurrentFocus'    => $plan->growthCurrentFocus,
            'growthSupportNeeded'   => $plan->growthSupportNeeded,
            'growthNextMilestone'   => $plan->growthNextMilestone,
            'nextWeekPriorities'    => $plan->nextWeekPriorities,
            'summary'               => $plan->summary,
            'createdAt'             => now()->toDateTimeString(),
            'updatedAt'             => now()->toDateTimeString(),
        ]);
    }

    /**
     * Update text sections and header fields of an existing plan.
     *
     * @param array<string, mixed> $data
     */
    public function updatePlan(int $id, array $data): bool
    {
        $data['updatedAt'] = now()->toDateTimeString();

        return (bool) $this->db->table('zp_weekly_plans')->where('id', $id)->update($data);
    }

    // -------------------------------------------------------------------------
    // Plan items
    // -------------------------------------------------------------------------

    /**
     * Get all items for a plan with linked ticket headline.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getItemsForPlan(int $planId): array
    {
        $rows = $this->db->table('zp_weekly_plan_items as i')
            ->select('i.*', 't.headline as ticketHeadline', 't.dateToFinish as ticketDueDate', 't.status as ticketStatus')
            ->leftJoin('zp_tickets as t', 't.id', '=', 'i.ticketId')
            ->where('i.weeklyPlanId', $planId)
            ->orderBy('i.priority')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Get a single plan item by its own ID.
     *
     * @return array<string, mixed>|null
     */
    public function getItemById(int $id): ?array
    {
        $row = $this->db->table('zp_weekly_plan_items as i')
            ->select('i.*', 't.headline as ticketHeadline', 't.dateToFinish as ticketDueDate')
            ->leftJoin('zp_tickets as t', 't.id', '=', 'i.ticketId')
            ->where('i.id', $id)
            ->first();

        return $row ? (array) $row : null;
    }

    /**
     * Add a task to a weekly plan.
     */
    public function addItem(WeeklyPlanItem $item): int
    {
        return $this->db->table('zp_weekly_plan_items')->insertGetId([
            'weeklyPlanId'    => $item->weeklyPlanId,
            'ticketId'        => $item->ticketId,
            'priority'        => $item->priority,
            'expectedOutcome' => $item->expectedOutcome,
            'status'          => $item->status,
        ]);
    }

    /**
     * Update item status and optional blocker fields.
     *
     * @param array<string, mixed> $data
     */
    public function updateItem(int $id, array $data): bool
    {
        return (bool) $this->db->table('zp_weekly_plan_items')->where('id', $id)->update($data);
    }

    /**
     * Remove an item from a plan.
     */
    public function deleteItem(int $id): bool
    {
        return (bool) $this->db->table('zp_weekly_plan_items')->where('id', $id)->delete();
    }

    // -------------------------------------------------------------------------
    // Feedback
    // -------------------------------------------------------------------------

    /**
     * Get all 4-direction feedback entries for a plan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFeedbackForPlan(int $planId): array
    {
        $rows = $this->db->table('zp_weekly_plan_feedback')
            ->where('weeklyPlanId', $planId)
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Save (insert or update) a feedback entry for a given type.
     */
    public function saveFeedback(WeeklyPlanFeedback $feedback): bool
    {
        $existing = $this->db->table('zp_weekly_plan_feedback')
            ->where('weeklyPlanId', $feedback->weeklyPlanId)
            ->where('type', $feedback->type)
            ->first();

        if ($existing) {
            return (bool) $this->db->table('zp_weekly_plan_feedback')
                ->where('id', $existing->id)
                ->update(['message' => $feedback->message]);
        }

        return (bool) $this->db->table('zp_weekly_plan_feedback')->insert([
            'weeklyPlanId' => $feedback->weeklyPlanId,
            'fromUserId'   => $feedback->fromUserId,
            'toUserId'     => $feedback->toUserId,
            'type'         => $feedback->type,
            'message'      => $feedback->message,
            'createdAt'    => now()->toDateTimeString(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Commitments
    // -------------------------------------------------------------------------

    /**
     * Get all commitments for a plan with owner name.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCommitmentsForPlan(int $planId): array
    {
        $rows = $this->db->table('zp_weekly_plan_commitments as c')
            ->select('c.*', 'u.firstname as ownerFirstname', 'u.lastname as ownerLastname')
            ->leftJoin('zp_user as u', 'u.id', '=', 'c.ownerId')
            ->where('c.weeklyPlanId', $planId)
            ->orderBy('c.deadline')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Add a commitment to a plan.
     */
    public function addCommitment(WeeklyPlanCommitment $commitment): int
    {
        return $this->db->table('zp_weekly_plan_commitments')->insertGetId([
            'weeklyPlanId' => $commitment->weeklyPlanId,
            'task'         => $commitment->task,
            'ownerId'      => $commitment->ownerId,
            'deadline'     => $commitment->deadline,
            'status'       => $commitment->status,
            'createdAt'    => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get a single commitment by ID.
     *
     * @return array<string, mixed>|null
     */
    public function getCommitmentById(int $id): ?array
    {
        $row = $this->db->table('zp_weekly_plan_commitments as c')
            ->select('c.*', 'u.firstname as ownerFirstname', 'u.lastname as ownerLastname')
            ->leftJoin('zp_user as u', 'u.id', '=', 'c.ownerId')
            ->where('c.id', $id)
            ->first();

        return $row ? (array) $row : null;
    }

    /**
     * Update commitment status (pending → done).
     */
    public function updateCommitmentStatus(int $id, string $status): bool
    {
        return (bool) $this->db->table('zp_weekly_plan_commitments')
            ->where('id', $id)
            ->update(['status' => $status]);
    }

    /**
     * Get all team members (editor+) whose managerId matches the team lead.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTeamMembers(int $teamLeadId): array
    {
        $rows = $this->db->table('zp_user')
            ->select('id', 'firstname', 'lastname', 'profileId', 'jobTitle', 'role')
            ->where('managerId', $teamLeadId)
            ->where('status', 'A')
            ->orderBy('firstname')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Get all blocked / not_completed items across all plans managed by a team lead.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBlockedItemsForTeamLead(int $teamLeadId): array
    {
        $rows = $this->db->table('zp_weekly_plan_items as i')
            ->select(
                'i.*',
                'p.weekLabel', 'p.weekStart', 'p.month',
                'p.employeeId',
                'e.firstname as employeeFirstname', 'e.lastname as employeeLastname',
                't.headline as ticketHeadline'
            )
            ->join('zp_weekly_plans as p', 'p.id', '=', 'i.weeklyPlanId')
            ->leftJoin('zp_user as e', 'e.id', '=', 'p.employeeId')
            ->leftJoin('zp_tickets as t', 't.id', '=', 'i.ticketId')
            ->where('p.teamLeadId', $teamLeadId)
            ->whereIn('i.status', ['blocked', 'not_completed'])
            ->orderByDesc('p.weekStart')
            ->orderBy('e.firstname')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Get all commitments across all plans managed by a team lead.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCommitmentsForTeamLead(int $teamLeadId, bool $openOnly = false): array
    {
        $q = $this->db->table('zp_weekly_plan_commitments as c')
            ->select(
                'c.*',
                'p.weekLabel', 'p.weekStart', 'p.month',
                'p.employeeId',
                'e.firstname as employeeFirstname', 'e.lastname as employeeLastname',
                'u.firstname as ownerFirstname', 'u.lastname as ownerLastname'
            )
            ->join('zp_weekly_plans as p', 'p.id', '=', 'c.weeklyPlanId')
            ->leftJoin('zp_user as e', 'e.id', '=', 'p.employeeId')
            ->leftJoin('zp_user as u', 'u.id', '=', 'c.ownerId')
            ->where('p.teamLeadId', $teamLeadId)
            ->orderBy('c.deadline');

        if ($openOnly) {
            $q->where('c.status', '!=', 'done');
        }

        $rows = $q->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }
}
