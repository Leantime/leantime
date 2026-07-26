<?php

declare(strict_types=1);

namespace Leantime\Domain\Reports\Services;

use Leantime\Core\Resources\Models\ResourceSummary;
use Leantime\Domain\Reports\Models\ReportPeriod;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketsRepo;

/**
 * Three-way capacity analysis for the stakeholder report's "Resource gaps &
 * risks" section.
 *
 * For each project in a plan, joins three independent estimates of the work
 * ahead and compares them against the capacity ResourceSummary already
 * exposes:
 *
 *   1. Budgeted hours   — sum(planHours) on open tickets. Explicit but often blank.
 *   2. Effort hours     — sum(storypoints) × hoursPerPoint on open tickets. Always
 *                         populated when sizing is used; a t-shirt/fibonacci proxy.
 *   3. Available hours  — sum(person allocation to this project) × weeks in the
 *                         project's active window (or the report period as fallback).
 *
 * The output tells the board *how much* things are off, not just *that* they
 * are — sensitivity, not a flag. Recommendation triples ("extend by X weeks",
 * "add Y people", "cut Z points of scope") land alongside so the discussion
 * has levers, not just a colored dot.
 *
 * Trust signal: when budgeted and effort disagree materially (or coverage is
 * low), the analyzer says so. A resourcing decision shouldn't hinge on
 * numbers the team hasn't kept clean.
 */
final class CapacityAnalyzer
{
    /**
     * Rough hours per fibonacci story point. A half-day-per-point default:
     * Effort labels are t-shirt sizes (XS=1, S=2, M=3, L=5, XL=8, XXL=13);
     * treating a point as ~4h puts M (3pts) at 12h — about a day and a
     * half — which matches how most Leantime teams size in practice.
     */
    public const DEFAULT_HOURS_PER_POINT = 4.0;

    /**
     * Trust bands for coverage (share of tickets that have planHours filled).
     */
    private const COVERAGE_HIGH = 0.75;

    private const COVERAGE_LOW = 0.30;

    /**
     * Divergence threshold: when budgeted and effort disagree by more than
     * this ratio, we call the estimate unreliable.
     */
    private const DIVERGENCE_THRESHOLD = 0.4;

    public function __construct(
        private readonly TicketsRepo $ticketsRepo,
    ) {}

    /**
     * Analyzes capacity for each project in the plan.
     *
     * @param  int[]  $projectIds
     * @param  array<int, string>  $projectNames  projectId => display name (from ReportEngine summaries)
     * @return array<int, array{
     *     projectId: int, name: string,
     *     openTicketCount: int, coverage: float, budgetedHours: float,
     *     effortPoints: float, effortHours: float, hoursPerPoint: float,
     *     divergence: float, trustSignal: string,
     *     peopleCount: int, weeklyHoursToProject: float,
     *     weeksInWindow: int, availableHours: float,
     *     gapVsBudgeted: float, gapVsEffort: float, gap: float,
     *     verdict: string,
     *     recommendations: array{extendWeeks: int, addPeople: int, cutHours: float, cutPoints: float}
     * }>
     */
    public function analyzeProjects(
        array $projectIds,
        ReportPeriod $period,
        ResourceSummary $resources,
        array $projectNames = [],
        float $hoursPerPoint = self::DEFAULT_HOURS_PER_POINT,
    ): array {
        $out = [];
        $weeksInPeriod = $this->weeksBetween($period->from, $period->to);

        foreach ($projectIds as $pid) {
            $pid = (int) $pid;

            // Skip projects the report doesn't consider "reportable" — those
            // that fell out of the resource walk but aren't in ReportEngine's
            // summaries (typically program containers themselves, not real
            // work projects). Only analyze what the report tracks.
            if (! empty($projectNames) && ! isset($projectNames[$pid])) {
                continue;
            }

            $rawTickets = $this->ticketsRepo->getAllByProjectId($pid) ?: [];

            // Normalize — the repo hydrates rows into Tickets model objects; we
            // want plain array rows for uniform key access downstream.
            $tickets = array_map(
                fn ($t) => is_object($t) ? (array) $t : (array) $t,
                $rawTickets,
            );

            // Filter to open tickets only — DONE work doesn't need capacity. DONE is
            // resolved per project from the status labels (custom statuses can mark
            // any id as done), not from the default 0/-1 convention.
            $doneStatuses = $this->doneStatusesForProject($pid);
            $openTickets = array_values(array_filter($tickets, fn ($t) => ! $this->isDone($t, $doneStatuses)));

            [$budgetedHours, $ticketsWithBudget] = $this->sumBudgetedHours($openTickets);
            [$effortPoints, $ticketsWithEffort] = $this->sumEffort($openTickets);
            $effortHours = $effortPoints * $hoursPerPoint;

            $openCount = count($openTickets);
            $coverage = $openCount > 0 ? $ticketsWithBudget / $openCount : 0.0;

            // Divergence between the two estimates. Only meaningful when both are non-trivial.
            $divergence = ($budgetedHours > 0 && $effortHours > 0)
                ? abs($budgetedHours - $effortHours) / max($budgetedHours, $effortHours)
                : 0.0;

            $trustSignal = $this->trustSignal($coverage, $divergence, $budgetedHours, $effortHours);

            // Capacity side — from ResourceSummary. Sum weekly allocation to THIS project only.
            $peopleCount = 0;
            $weeklyHoursToProject = 0.0;
            foreach ($resources->people as $person) {
                $hrs = (float) ($person->allocations[$pid] ?? 0.0);
                if ($hrs > 0) {
                    $peopleCount++;
                    $weeklyHoursToProject += $hrs;
                }
            }
            $availableHours = $weeklyHoursToProject * $weeksInPeriod;

            // The "reference demand" for a gap: prefer whichever estimate we trust more.
            // When trust is 'effort' (coverage is low), use the effort projection;
            // when it's 'budgeted', use the explicit hours; when mixed, use the
            // higher of the two (conservative — surface risk, don't hide it).
            $referenceDemand = match ($trustSignal) {
                'budgeted' => $budgetedHours,
                'effort' => $effortHours,
                default => max($budgetedHours, $effortHours),
            };

            $gap = $referenceDemand - $availableHours;

            $verdict = $this->verdict($gap, $availableHours, $referenceDemand);

            $recommendations = $gap > 0 && $weeklyHoursToProject > 0
                ? $this->recommend($gap, $weeklyHoursToProject, $peopleCount, $weeksInPeriod, $hoursPerPoint)
                : ['extendWeeks' => 0, 'addPeople' => 0, 'cutHours' => 0.0, 'cutPoints' => 0.0];

            // Skip noise rows: projects walked by the resource gateway but with
            // nothing meaningful to say (typically program containers themselves,
            // which have no direct tickets or allocations). Reporting these as
            // "no capacity / no work" is just clutter.
            if ($openCount === 0 && $peopleCount === 0 && $budgetedHours === 0.0 && $effortHours === 0.0) {
                continue;
            }

            $out[$pid] = [
                'projectId' => $pid,
                'name' => $projectNames[$pid] ?? ('#'.$pid),
                'openTicketCount' => $openCount,
                'coverage' => $coverage,
                'budgetedHours' => $budgetedHours,
                'ticketsWithBudget' => $ticketsWithBudget,
                'effortPoints' => $effortPoints,
                'ticketsWithEffort' => $ticketsWithEffort,
                'effortHours' => $effortHours,
                'hoursPerPoint' => $hoursPerPoint,
                'divergence' => $divergence,
                'trustSignal' => $trustSignal,
                'peopleCount' => $peopleCount,
                'weeklyHoursToProject' => $weeklyHoursToProject,
                'weeksInWindow' => $weeksInPeriod,
                'availableHours' => $availableHours,
                'referenceDemand' => $referenceDemand,
                'gapVsBudgeted' => $budgetedHours - $availableHours,
                'gapVsEffort' => $effortHours - $availableHours,
                'gap' => $gap,
                'verdict' => $verdict,
                'recommendations' => $recommendations,
            ];
        }

        return $out;
    }

    /**
     * Rolls per-project capacity rows into per-program aggregates for the
     * strategy report. Every derived field (coverage, divergence, verdict,
     * recommendations) is recomputed against the aggregate — a program that
     * has three tight projects reads as tight overall, not "critical because
     * one child was critical".
     *
     * @param  array<int, array<string, mixed>>  $projectRows  Output of analyzeProjects()
     * @param  array<int, int[]>  $programChildMap  programId => [childProjectIds]
     * @param  array<int, array{id:int, name:string}>  $programMeta
     * @return array<int, array<string, mixed>> One row per program with an added 'children' key
     */
    public function aggregateByProgram(
        array $projectRows,
        array $programChildMap,
        array $programMeta,
        \Leantime\Core\Resources\Models\ResourceSummary $resources,
        ReportPeriod $period,
        float $hoursPerPoint = self::DEFAULT_HOURS_PER_POINT,
    ): array {
        $out = [];
        $weeksInPeriod = $this->weeksBetween($period->from, $period->to);

        foreach ($programMeta as $progId => $progInfo) {
            $childIds = $programChildMap[$progId] ?? [];
            if ($childIds === []) {
                continue;
            }
            $children = array_values(array_filter(
                array_map(fn ($cid) => $projectRows[$cid] ?? null, $childIds),
                fn ($r) => $r !== null,
            ));

            $openTicketCount = 0;
            $ticketsWithBudget = 0;
            $ticketsWithEffort = 0;
            $budgetedHours = 0.0;
            $effortPoints = 0.0;
            foreach ($children as $c) {
                $openTicketCount += (int) $c['openTicketCount'];
                $ticketsWithBudget += (int) $c['ticketsWithBudget'];
                $ticketsWithEffort += (int) $c['ticketsWithEffort'];
                $budgetedHours += (float) $c['budgetedHours'];
                $effortPoints += (float) $c['effortPoints'];
            }
            $effortHours = $effortPoints * $hoursPerPoint;
            $coverage = $openTicketCount > 0 ? $ticketsWithBudget / $openTicketCount : 0.0;
            $divergence = ($budgetedHours > 0 && $effortHours > 0)
                ? abs($budgetedHours - $effortHours) / max($budgetedHours, $effortHours)
                : 0.0;
            $trustSignal = $this->trustSignal($coverage, $divergence, $budgetedHours, $effortHours);

            // Capacity aggregation: unique people across the program (a person
            // on two child projects still counts once).
            //
            // Supply is each person's CAPACITY, not the hours already
            // allocated here. Allocation answers "what have we committed",
            // capacity answers "what could we actually do" — and only the
            // second one can say whether there is room. Summing allocations
            // made the two halves of the report disagree: the headline tile
            // reads allocated/capacity while this block read demand against
            // allocated, so a program with real headroom and nothing booked
            // yet reported no_capacity.
            //
            // A person's capacity is not dedicated to this program, so
            // commitments to projects OUTSIDE it are deducted. Only projects
            // inside this ResourceSummary are visible, so for a strategy
            // report that means siblings within the same strategy; work on
            // other strategies is not visible here and this therefore reads
            // as an upper bound.
            $childIdSet = array_flip(array_map('intval', $childIds));
            $peopleSet = [];
            $weeklyCapacityToProgram = 0.0;
            foreach ($resources->people as $person) {
                $touched = false;
                $committedElsewhere = 0.0;
                foreach ($person->allocations as $allocPid => $allocHrs) {
                    $allocHrs = (float) $allocHrs;
                    if ($allocHrs <= 0) {
                        continue;
                    }
                    if (isset($childIdSet[(int) $allocPid])) {
                        $touched = true;
                    } else {
                        $committedElsewhere += $allocHrs;
                    }
                }

                if ($touched) {
                    $peopleSet[$person->itemId] = 1;
                    $weeklyCapacityToProgram += max(0.0, $person->capacity - $committedElsewhere);
                }
            }
            $peopleCount = count($peopleSet);
            $availableHours = $weeklyCapacityToProgram * $weeksInPeriod;
            // recommend() reasons in weekly hours; keep its input consistent
            // with the supply figure the verdict was derived from.
            $weeklyHoursToProgram = $weeklyCapacityToProgram;

            $referenceDemand = match ($trustSignal) {
                'budgeted' => $budgetedHours,
                'effort' => $effortHours,
                default => max($budgetedHours, $effortHours),
            };
            $gap = $referenceDemand - $availableHours;
            $verdict = $this->verdict($gap, $availableHours, $referenceDemand);

            $recommendations = $gap > 0 && $weeklyHoursToProgram > 0
                ? $this->recommend($gap, $weeklyHoursToProgram, $peopleCount, $weeksInPeriod, $hoursPerPoint)
                : ['extendWeeks' => 0, 'addPeople' => 0, 'cutHours' => 0.0, 'cutPoints' => 0.0];

            // Order child project rows worst-first for the expand view.
            $verdictRank = ['critical' => 0, 'tight' => 1, 'balanced' => 2, 'buffer' => 3, 'no_capacity' => 4, 'no_work' => 5];
            usort($children, fn ($a, $b) => ($verdictRank[$a['verdict']] ?? 9) <=> ($verdictRank[$b['verdict']] ?? 9));

            $out[$progId] = [
                'projectId' => $progId,
                'name' => $progInfo['name'],
                'isProgram' => true,
                'childCount' => count($children),
                'children' => $children,
                'openTicketCount' => $openTicketCount,
                'coverage' => $coverage,
                'budgetedHours' => $budgetedHours,
                'ticketsWithBudget' => $ticketsWithBudget,
                'effortPoints' => $effortPoints,
                'ticketsWithEffort' => $ticketsWithEffort,
                'effortHours' => $effortHours,
                'hoursPerPoint' => $hoursPerPoint,
                'divergence' => $divergence,
                'trustSignal' => $trustSignal,
                'peopleCount' => $peopleCount,
                'weeklyHoursToProject' => $weeklyHoursToProgram,
                'weeksInWindow' => $weeksInPeriod,
                'availableHours' => $availableHours,
                'referenceDemand' => $referenceDemand,
                'gapVsBudgeted' => $budgetedHours - $availableHours,
                'gapVsEffort' => $effortHours - $availableHours,
                'gap' => $gap,
                'verdict' => $verdict,
                'recommendations' => $recommendations,
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tickets
     * @return array{0: float, 1: int} [totalHours, ticketsWithNonZeroPlanHours]
     */
    private function sumBudgetedHours(array $tickets): array
    {
        $sum = 0.0;
        $withBudget = 0;
        foreach ($tickets as $t) {
            $ph = (float) ($t['planHours'] ?? 0);
            if ($ph > 0) {
                $sum += $ph;
                $withBudget++;
            }
        }

        return [$sum, $withBudget];
    }

    /**
     * @param  array<int, array<string, mixed>>  $tickets
     * @return array{0: float, 1: int} [totalPoints, ticketsWithNonZeroPoints]
     */
    private function sumEffort(array $tickets): array
    {
        $sum = 0.0;
        $withEffort = 0;
        foreach ($tickets as $t) {
            $sp = (float) ($t['storypoints'] ?? 0);
            if ($sp > 0) {
                $sum += $sp;
                $withEffort++;
            }
        }

        return [$sum, $withEffort];
    }

    /**
     * @param  int[]  $doneStatuses  Status ids whose statusType is DONE in the ticket's project
     */
    private function isDone(array $t, array $doneStatuses): bool
    {
        return in_array((int) ($t['status'] ?? 0), $doneStatuses, true);
    }

    /**
     * DONE-type status ids for a project from its (cached) status label settings —
     * covers custom statuses with positive ids, plus the default 0/-1 pair.
     *
     * @return int[]
     */
    private function doneStatusesForProject(int $projectId): array
    {
        $doneStatuses = [];
        foreach ($this->ticketsRepo->getStateLabels($projectId) as $statusId => $label) {
            if (($label['statusType'] ?? '') === 'DONE') {
                $doneStatuses[] = (int) $statusId;
            }
        }

        return $doneStatuses;
    }

    private function weeksBetween(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        $seconds = $to->getTimestamp() - $from->getTimestamp();

        return max(1, (int) ceil($seconds / (60 * 60 * 24 * 7)));
    }

    /**
     * 'budgeted' → coverage is high AND estimates agree. Trust the explicit hours.
     * 'effort'   → coverage is low. Fall back on story-point projection.
     * 'mixed'    → coverage okay but estimates disagree meaningfully. Flag the ambiguity.
     */
    private function trustSignal(float $coverage, float $divergence, float $budgeted, float $effort): string
    {
        if ($budgeted === 0.0 && $effort === 0.0) {
            return 'none';
        }
        if ($coverage < self::COVERAGE_LOW) {
            return $effort > 0 ? 'effort' : 'budgeted';
        }
        if ($coverage >= self::COVERAGE_HIGH && $divergence < self::DIVERGENCE_THRESHOLD) {
            return 'budgeted';
        }

        return 'mixed';
    }

    private function verdict(float $gap, float $available, float $demand): string
    {
        if ($demand === 0.0) {
            return 'no_work';
        }
        if ($available === 0.0) {
            return 'no_capacity';
        }
        $ratio = $gap / $available;
        if ($ratio > 0.25) {
            return 'critical';
        }
        if ($ratio > 0) {
            return 'tight';
        }
        if ($ratio < -0.5) {
            return 'buffer';
        }

        return 'balanced';
    }

    /**
     * Compute the three levers: extend timeline / add people / cut scope.
     *
     * @return array{extendWeeks: int, addPeople: int, cutHours: float, cutPoints: float}
     */
    private function recommend(
        float $gapHours,
        float $weeklyHoursToProject,
        int $peopleCount,
        int $weeksInWindow,
        float $hoursPerPoint,
    ): array {
        $avgWeeklyPerPerson = $peopleCount > 0 ? $weeklyHoursToProject / $peopleCount : 20.0;

        return [
            'extendWeeks' => (int) ceil($gapHours / max(1.0, $weeklyHoursToProject)),
            'addPeople' => (int) ceil($gapHours / max(1.0, $weeksInWindow * $avgWeeklyPerPerson)),
            'cutHours' => $gapHours,
            'cutPoints' => $hoursPerPoint > 0 ? $gapHours / $hoursPerPoint : 0.0,
        ];
    }
}
