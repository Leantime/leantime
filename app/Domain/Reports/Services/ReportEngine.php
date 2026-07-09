<?php

declare(strict_types=1);

namespace Leantime\Domain\Reports\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\Domains\BaseService;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Models\ReportPeriod;
use Leantime\Domain\Reports\Permissions\ReportsPermissions;
use Leantime\Domain\Reports\Repositories\ReportEngine as ReportEngineRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

/**
 * Shared, period-aware reporting engine feeding the project, plan and strategy report screens.
 *
 * All methods operate on explicit project-id sets so higher levels (plan = child projects,
 * strategy = descendant projects, later company = all projects) compose the same building
 * blocks. Ids are filtered to projects the requesting user may view before any query runs,
 * and the repository re-applies the SQL access predicate — defense in depth.
 */
class ReportEngine extends BaseService
{
    /**
     * A project with no status update for this many days counts as silent/stale.
     */
    private const STALE_AFTER_DAYS = 30;

    public function __construct(
        private ReportEngineRepository $reportEngineRepository,
        private TicketRepository $ticketRepository,
        private ProjectService $projectService,
        private GoalcanvasService $goalService,
    ) {}

    /**
     * Builds the full report view model for a set of projects in one pass: needs-attention,
     * milestone buckets, slippage, goals, status updates, effort, prior-period deltas and
     * summary stats. This is the single entry point the report screens consume.
     *
     * @param  int[]  $projectIds
     * @return array<string, mixed>
     *
     * @api
     */
    public function buildReport(array $projectIds, ReportPeriod $period): array
    {
        $projectIds = $this->filterAuthorizedProjects($projectIds);

        $summaries = $this->getProjectSummaries($projectIds);
        $milestoneReport = $this->getMilestoneReportForProjects($projectIds, $period);
        $goalReport = $this->getGoalReportForProjects($projectIds);
        $statusUpdates = $this->getStatusUpdatesForProjects($projectIds, $period);
        $effort = $this->getEffortForProjects($projectIds, $period);

        $priorPeriod = $period->priorPeriod();
        $priorEffort = $this->getEffortForProjects($projectIds, $priorPeriod);
        $completedPrior = count(array_filter(
            $milestoneReport['allDone'],
            fn (object $milestone) => $milestone->completedOn !== null
                && $priorPeriod->contains($milestone->completedOn)
        ));

        $completedCount = count($milestoneReport['completed']);
        $deltas = [
            'completedPrior' => $completedPrior,
            'completedDelta' => $completedCount - $completedPrior,
            'hoursPrior' => $priorEffort['total'],
            'hoursDelta' => round($effort['total'] - $priorEffort['total'], 2),
            'priorPeriodLabel' => $priorPeriod->label(),
        ];

        $needsAttention = $this->buildNeedsAttention($summaries, $milestoneReport, $goalReport);

        return [
            'period' => $period,
            'projectIds' => $projectIds,
            'summaries' => $summaries,
            'milestones' => $milestoneReport,
            'goals' => $goalReport,
            'statusUpdates' => $statusUpdates,
            'effort' => $effort,
            'deltas' => $deltas,
            'needsAttention' => $needsAttention,
            'stats' => [
                'completed' => $completedCount,
                'inFlight' => count($milestoneReport['inProgress']),
                'overdue' => count($milestoneReport['overdue']),
                'upcoming' => count($milestoneReport['upcoming']),
                'goalsOnTrack' => $goalReport['counts']['ontrack'],
                'goalsTotal' => count($goalReport['goals']),
                'hoursLogged' => $effort['total'],
            ],
        ];
    }

    /**
     * Buckets the projects' milestones for the period.
     *
     * - completed: DONE status and completed inside the period (completion date derived from
     *   the ticket status-change history, falling back to due date, then last modified)
     * - inProgress: not done, scheduled to overlap the period (or unscheduled), not overdue
     * - overdue: not done and due date in the past
     * - upcoming: starting after the period, within the next two quarters, grouped by quarter
     * - allDone: every done milestone with its completion date (feeds prior-period deltas)
     * - slippage: due dates pushed out of the period + milestones added mid-period
     *
     * @param  int[]  $projectIds
     * @return array<string, mixed>
     *
     * @api
     */
    public function getMilestoneReportForProjects(array $projectIds, ReportPeriod $period): array
    {
        $projectIds = $this->filterAuthorizedProjects($projectIds);
        [$userId, $clientId] = $this->requestContext();

        $milestones = $this->reportEngineRepository->getMilestonesForProjects($projectIds, $userId, $clientId);
        $doneStatusesByProject = $this->getDoneStatusesByProject($projectIds);

        $tasksByMilestone = $this->groupTasksByMilestone(
            $this->reportEngineRepository->getTasksForMilestones(array_map(fn ($m) => (int) $m->id, $milestones))
        );

        $now = CarbonImmutable::now('UTC');
        $horizon = $period->upcomingHorizon();

        $completed = [];
        $inProgress = [];
        $overdue = [];
        $upcoming = [];
        $allDone = [];

        $doneMilestoneIds = [];
        foreach ($milestones as $milestone) {
            if ($this->hasDoneStatus($milestone, $doneStatusesByProject)) {
                $doneMilestoneIds[] = (int) $milestone->id;
            }
        }
        $completionDates = $this->deriveCompletionDates($doneMilestoneIds, $milestones, $doneStatusesByProject);

        foreach ($milestones as $milestone) {
            $milestoneId = (int) $milestone->id;
            $milestone->startDate = $this->parseDbDateOrNull($milestone->editFrom);
            $milestone->dueDate = $this->parseDbDateOrNull($milestone->editTo);
            $milestone->taskStats = $this->buildTaskStats(
                $tasksByMilestone[$milestoneId] ?? [],
                $doneStatusesByProject[(int) $milestone->projectId] ?? []
            );
            $milestone->keyTasks = $this->pickKeyTasks(
                $tasksByMilestone[$milestoneId] ?? [],
                $doneStatusesByProject[(int) $milestone->projectId] ?? []
            );

            if ($this->hasDoneStatus($milestone, $doneStatusesByProject)) {
                $milestone->completedOn = $completionDates[$milestoneId] ?? null;
                $milestone->percentDone = 100.0;
                $allDone[] = $milestone;

                if ($milestone->completedOn !== null && $period->contains($milestone->completedOn)) {
                    $completed[] = $milestone;
                }

                continue;
            }

            $milestone->completedOn = null;
            $milestone->percentDone = $this->calculateMilestoneProgress(
                $tasksByMilestone[$milestoneId] ?? [],
                $doneStatusesByProject[(int) $milestone->projectId] ?? []
            );

            if ($milestone->dueDate !== null && $milestone->dueDate->lessThan($now)) {
                $overdue[] = $milestone;

                continue;
            }

            if ($milestone->startDate !== null && $milestone->startDate->greaterThan($period->to)) {
                if ($milestone->startDate->lessThanOrEqualTo($horizon)) {
                    $milestone->quarterLabel = $this->quarterLabel($milestone->startDate);
                    $upcoming[] = $milestone;
                }

                continue;
            }

            // Overlapping the period or entirely unscheduled: active work.
            $inProgress[] = $milestone;
        }

        usort($completed, fn ($a, $b) => ($b->completedOn?->getTimestamp() ?? 0) <=> ($a->completedOn?->getTimestamp() ?? 0));
        usort($overdue, fn ($a, $b) => ($a->dueDate?->getTimestamp() ?? 0) <=> ($b->dueDate?->getTimestamp() ?? 0));
        usort($upcoming, fn ($a, $b) => ($a->startDate?->getTimestamp() ?? 0) <=> ($b->startDate?->getTimestamp() ?? 0));

        $slippage = $this->buildSlippage($milestones, $doneStatusesByProject, $period);

        return [
            'completed' => $completed,
            'inProgress' => $inProgress,
            'overdue' => $overdue,
            'upcoming' => $upcoming,
            'upcomingByQuarter' => $this->groupByQuarter($upcoming),
            'allDone' => $allDone,
            'slippage' => $slippage,
        ];
    }

    /**
     * Goals of the given projects with metric progress and roll-up values resolved.
     *
     * @param  int[]  $projectIds
     * @return array{goals: array<int, object>, byProject: array<int, array<int, object>>, counts: array<string, int>}
     *
     * @api
     */
    public function getGoalReportForProjects(array $projectIds): array
    {
        $projectIds = $this->filterAuthorizedProjects($projectIds);
        [$userId, $clientId] = $this->requestContext();

        $goals = $this->reportEngineRepository->getGoalsForProjects($projectIds, $userId, $clientId);

        $byProject = [];
        $counts = ['ontrack' => 0, 'atrisk' => 0, 'miss' => 0];

        foreach ($goals as $goal) {
            if ($goal->setting === 'linkAndReport') {
                $goal->currentValue = $this->goalService->getChildGoalsForReporting((int) $goal->id);
            }

            $goal->goalProgress = $this->calculateGoalProgress($goal);

            $statusKey = str_replace('status_', '', (string) $goal->status);
            if (array_key_exists($statusKey, $counts)) {
                $counts[$statusKey]++;
            }

            $byProject[(int) $goal->projectId][] = $goal;
        }

        return [
            'goals' => $goals,
            'byProject' => $byProject,
            'counts' => $counts,
        ];
    }

    /**
     * Status updates posted within the period, grouped by project, newest first.
     *
     * @param  int[]  $projectIds
     * @param  int  $limitPerProject  0 = no limit
     * @return array<int, array<int, object>> projectId => updates
     *
     * @api
     */
    public function getStatusUpdatesForProjects(array $projectIds, ReportPeriod $period, int $limitPerProject = 0): array
    {
        $projectIds = $this->filterAuthorizedProjects($projectIds);
        [$userId, $clientId] = $this->requestContext();

        $updates = $this->reportEngineRepository->getStatusUpdatesForProjects(
            $projectIds,
            $period->fromDbString(),
            $period->toDbString(),
            $userId,
            $clientId
        );

        $byProject = [];
        foreach ($updates as $update) {
            $projectId = (int) $update->projectId;
            if ($limitPerProject > 0 && count($byProject[$projectId] ?? []) >= $limitPerProject) {
                continue;
            }
            $update->dateParsed = $this->parseDbDateOrNull($update->date);
            $byProject[$projectId][] = $update;
        }

        return $byProject;
    }

    /**
     * Summary header data per project: name, one-line description, progress, latest status
     * update (the status pill), staleness and timeline.
     *
     * @param  int[]  $projectIds
     * @return array<int, object> Keyed by project id
     *
     * @api
     */
    public function getProjectSummaries(array $projectIds): array
    {
        $projectIds = $this->filterAuthorizedProjects($projectIds);
        [$userId, $clientId] = $this->requestContext();

        $meta = $this->reportEngineRepository->getProjectsMeta($projectIds, $userId, $clientId);
        $latestUpdates = $this->reportEngineRepository->getLatestStatusUpdateForProjects(array_keys($meta));

        $now = CarbonImmutable::now('UTC');

        foreach ($meta as $projectId => $project) {
            $project->descriptionExcerpt = $this->excerpt((string) ($project->details ?? ''));
            $project->progress = Cache::remember(
                'reportengine.progress.'.$projectId,
                600,
                fn () => $this->projectService->getProjectProgress($projectId)
            );

            $latest = $latestUpdates[$projectId] ?? null;
            $project->latestStatus = $latest?->status ?: null;
            $project->latestStatusDate = $latest !== null ? $this->parseDbDateOrNull($latest->date) : null;
            $project->latestStatusText = $latest !== null ? $this->excerpt((string) $latest->text) : null;
            $project->isStale = $project->latestStatusDate === null
                || $project->latestStatusDate->lessThan($now->subDays(self::STALE_AFTER_DAYS));
        }

        return $meta;
    }

    /**
     * Hours logged in the period, totaled and broken down by project and milestone.
     *
     * @param  int[]  $projectIds
     * @return array{total: float, byProject: array<int, float>, byMilestone: array<int, float>}
     *
     * @api
     */
    public function getEffortForProjects(array $projectIds, ReportPeriod $period): array
    {
        $projectIds = $this->filterAuthorizedProjects($projectIds);

        $rows = $this->reportEngineRepository->getHoursLoggedForProjects(
            $projectIds,
            $period->fromDbString(),
            $period->toDbString()
        );

        $total = 0.0;
        $byProject = [];
        $byMilestone = [];

        foreach ($rows as $row) {
            $hours = (float) $row->loggedHours;
            $total += $hours;
            $byProject[(int) $row->projectId] = ($byProject[(int) $row->projectId] ?? 0.0) + $hours;
            if ((int) $row->milestoneId > 0) {
                $byMilestone[(int) $row->milestoneId] = ($byMilestone[(int) $row->milestoneId] ?? 0.0) + $hours;
            }
        }

        return [
            'total' => round($total, 2),
            'byProject' => $byProject,
            'byMilestone' => $byMilestone,
        ];
    }

    /**
     * Restricts a set of project ids to those the requesting user may view reports for.
     *
     * @param  int[]  $projectIds
     * @return int[]
     */
    private function filterAuthorizedProjects(array $projectIds): array
    {
        $projectIds = array_values(array_unique(array_map('intval', $projectIds)));

        return array_values(array_filter(
            $projectIds,
            fn (int $projectId) => $projectId > 0 && $this->can(ReportsPermissions::VIEW, $projectId)
        ));
    }

    /**
     * The requesting user's id and client id, used by the repository access predicate. Reports
     * always run in a request context (web, HTMX or authenticated API — all populate session
     * userdata); cron/system paths must not call the engine.
     *
     * @return array{0: int, 1: int}
     */
    private function requestContext(): array
    {
        return [
            (int) (session('userdata.id') ?? -1),
            (int) (session('userdata.clientId') ?? -1),
        ];
    }

    /**
     * DONE-type status ids per project, from the (cached) project status label settings.
     *
     * @param  int[]  $projectIds
     * @return array<int, int[]>
     */
    private function getDoneStatusesByProject(array $projectIds): array
    {
        $map = [];
        foreach ($projectIds as $projectId) {
            $doneStatuses = [];
            foreach ($this->ticketRepository->getStateLabels($projectId) as $statusId => $label) {
                if (($label['statusType'] ?? '') === 'DONE') {
                    $doneStatuses[] = (int) $statusId;
                }
            }
            $map[$projectId] = $doneStatuses;
        }

        return $map;
    }

    private function hasDoneStatus(object $milestone, array $doneStatusesByProject): bool
    {
        return in_array((int) $milestone->status, $doneStatusesByProject[(int) $milestone->projectId] ?? [], true);
    }

    /**
     * Completion dates for done milestones: the latest status-history transition into a
     * DONE-type status; for rows predating history coverage the due date, then last modified.
     *
     * @param  int[]  $doneMilestoneIds
     * @param  array<int, object>  $milestones
     * @return array<int, ?CarbonImmutable> milestoneId => completion date
     */
    private function deriveCompletionDates(array $doneMilestoneIds, array $milestones, array $doneStatusesByProject): array
    {
        $milestonesById = [];
        foreach ($milestones as $milestone) {
            $milestonesById[(int) $milestone->id] = $milestone;
        }

        $latestDoneTransition = [];
        foreach ($this->reportEngineRepository->getStatusHistoryForTickets($doneMilestoneIds) as $row) {
            $ticketId = (int) $row->ticketId;
            $milestone = $milestonesById[$ticketId] ?? null;
            if ($milestone === null) {
                continue;
            }

            $doneStatuses = $doneStatusesByProject[(int) $milestone->projectId] ?? [];
            if (in_array((int) $row->changeValue, $doneStatuses, true)) {
                // Rows arrive oldest-first, so the last hit per ticket wins.
                $latestDoneTransition[$ticketId] = $row->dateModified;
            }
        }

        $completionDates = [];
        foreach ($doneMilestoneIds as $milestoneId) {
            $milestone = $milestonesById[$milestoneId];
            $completionDates[$milestoneId] = $this->parseDbDateOrNull($latestDoneTransition[$milestoneId] ?? null)
                ?? $this->parseDbDateOrNull($milestone->editTo)
                ?? $this->parseDbDateOrNull($milestone->modified);
        }

        return $completionDates;
    }

    /**
     * Commitment integrity: milestones whose due date was pushed past the period end during
     * the period, and milestones created mid-period.
     *
     * @param  array<int, object>  $milestones
     * @return array{pushedOut: array<int, object>, addedMidPeriod: array<int, object>}
     */
    private function buildSlippage(array $milestones, array $doneStatusesByProject, ReportPeriod $period): array
    {
        $candidates = [];
        foreach ($milestones as $milestone) {
            if ($this->hasDoneStatus($milestone, $doneStatusesByProject)) {
                continue;
            }
            $dueDate = $milestone->dueDate ?? $this->parseDbDateOrNull($milestone->editTo);
            if ($dueDate !== null && $dueDate->greaterThan($period->to)) {
                $candidates[(int) $milestone->id] = $milestone;
            }
        }

        $changes = $this->reportEngineRepository->getDueDateChangesForTickets(
            array_keys($candidates),
            $period->fromDbString(),
            $period->toDbString()
        );

        $moveCounts = [];
        foreach ($changes as $change) {
            $moveCounts[(int) $change->ticketId] = ($moveCounts[(int) $change->ticketId] ?? 0) + 1;
        }

        $pushedOut = [];
        foreach ($moveCounts as $milestoneId => $moves) {
            $milestone = $candidates[$milestoneId];
            $milestone->dueDateMoves = $moves;
            $pushedOut[] = $milestone;
        }

        $addedMidPeriod = [];
        foreach ($milestones as $milestone) {
            $created = $this->parseDbDateOrNull($milestone->date);
            if ($created !== null && $created->greaterThan($period->from) && $created->lessThanOrEqualTo($period->to)) {
                $addedMidPeriod[] = $milestone;
            }
        }

        return [
            'pushedOut' => $pushedOut,
            'addedMidPeriod' => $addedMidPeriod,
        ];
    }

    /**
     * Attention items across the project set: red/yellow projects, silent projects, overdue
     * milestones and at-risk goals — the "what needs my intervention" block.
     *
     * @param  array<int, object>  $summaries
     * @return array<string, array>
     */
    private function buildNeedsAttention(array $summaries, array $milestoneReport, array $goalReport): array
    {
        $statusAlerts = [];
        $staleProjects = [];

        foreach ($summaries as $project) {
            if ((int) $project->state === -1) {
                continue; // Closed projects don't need status nudging.
            }
            if (in_array($project->latestStatus, ['red', 'yellow'], true)) {
                $statusAlerts[] = $project;
            }
            if ($project->isStale) {
                $staleProjects[] = $project;
            }
        }

        $goalsAtRisk = array_values(array_filter(
            $goalReport['goals'],
            fn (object $goal) => in_array($goal->status, ['status_atrisk', 'status_miss'], true)
        ));

        return [
            'statusAlerts' => $statusAlerts,
            'staleProjects' => $staleProjects,
            'overdueMilestones' => $milestoneReport['overdue'],
            'goalsAtRisk' => $goalsAtRisk,
        ];
    }

    /**
     * Weighted milestone progress over its tasks. The weights MUST stay in sync with
     * {@see \Leantime\Domain\Tickets\Services\Tickets::getMilestoneProgress} — this bulk
     * variant exists so report rollups don't re-query tasks per milestone.
     *
     * @param  array<int, object>  $tasks
     * @param  int[]  $doneStatuses
     */
    private function calculateMilestoneProgress(array $tasks, array $doneStatuses): float
    {
        $priorityFactor = [1 => 2, 2 => 1.75, 3 => 1.5, 4 => 1.25, 5 => 1];

        $totalScore = 0.0;
        $doneScore = 0.0;

        foreach ($tasks as $task) {
            $effort = empty($task->storypoints) ? 3 : (float) $task->storypoints;
            $priority = empty($task->priority) ? 3 : (int) $task->priority;
            $score = $effort * ($priorityFactor[$priority] ?? 1);

            $totalScore += $score;
            if (in_array((int) $task->status, $doneStatuses, true)) {
                $doneScore += $score;
            }
        }

        if ($totalScore === 0.0) {
            return 0.0;
        }

        return $doneScore / $totalScore * 100;
    }

    /**
     * Metric progress percent, mirroring the goal dashboard math in
     * {@see \Leantime\Domain\Goalcanvas\Services\Goalcanvas::getCanvasItemsById}.
     */
    private function calculateGoalProgress(object $goal): float
    {
        $total = (float) $goal->endValue - (float) $goal->startValue;
        if ($total == 0.0) {
            return 0.0;
        }

        $progress = (float) $goal->currentValue - (float) $goal->startValue;

        return min(100, max(0, round($progress / $total, 2) * 100));
    }

    /**
     * @param  array<int, object>  $tasks
     * @param  int[]  $doneStatuses
     * @return array{done: int, total: int}
     */
    private function buildTaskStats(array $tasks, array $doneStatuses): array
    {
        $done = 0;
        foreach ($tasks as $task) {
            if (in_array((int) $task->status, $doneStatuses, true)) {
                $done++;
            }
        }

        return ['done' => $done, 'total' => count($tasks)];
    }

    /**
     * Up to five tasks per milestone for the drill-down list, completed work first.
     *
     * @param  array<int, object>  $tasks
     * @param  int[]  $doneStatuses
     * @return array<int, object>
     */
    private function pickKeyTasks(array $tasks, array $doneStatuses, int $limit = 5): array
    {
        usort($tasks, function (object $a, object $b) use ($doneStatuses) {
            $aDone = in_array((int) $a->status, $doneStatuses, true) ? 0 : 1;
            $bDone = in_array((int) $b->status, $doneStatuses, true) ? 0 : 1;

            return $aDone <=> $bDone;
        });

        foreach ($tasks as $task) {
            $task->isDone = in_array((int) $task->status, $doneStatuses, true);
        }

        return array_slice($tasks, 0, $limit);
    }

    /**
     * @param  array<int, object>  $tasks
     * @return array<int, array<int, object>> milestoneId => tasks
     */
    private function groupTasksByMilestone(array $tasks): array
    {
        $grouped = [];
        foreach ($tasks as $task) {
            $grouped[(int) $task->milestoneid][] = $task;
        }

        return $grouped;
    }

    /**
     * @param  array<int, object>  $upcoming  Milestones sorted by start date
     * @return array<string, array<int, object>> "Q3 2026" => milestones
     */
    private function groupByQuarter(array $upcoming): array
    {
        $grouped = [];
        foreach ($upcoming as $milestone) {
            $grouped[$milestone->quarterLabel][] = $milestone;
        }

        return $grouped;
    }

    private function quarterLabel(CarbonImmutable $date): string
    {
        $userDate = $date->setToUserTimezone();

        return 'Q'.$userDate->quarter.' '.$userDate->year;
    }

    /**
     * Parses a db datetime string, treating null/empty/zero dates as absent.
     */
    private function parseDbDateOrNull(mixed $dbDate): ?CarbonImmutable
    {
        if (empty($dbDate) || str_starts_with((string) $dbDate, '0000-00-00')) {
            return null;
        }

        try {
            return dtHelper()->parseDbDateTime((string) $dbDate);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Plain-text one-liner from a possibly-HTML field.
     */
    private function excerpt(string $html, int $length = 140): string
    {
        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 1).'…';
    }
}
