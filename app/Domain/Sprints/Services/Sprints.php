<?php

namespace Leantime\Domain\Sprints\Services;

use Carbon\CarbonImmutable;
use DateInterval;
use DatePeriod;
use DateTime;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Domain\Reports\Repositories\Reports as ReportRepository;
use Leantime\Domain\Sprints\Events\SprintCreated;
use Leantime\Domain\Sprints\Models;
use Leantime\Domain\Sprints\Permissions\SprintsPermissions;
use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;

/**
 * @api
 */
class Sprints extends BaseService
{
    public function __construct(
        private SprintRepository $sprintRepository,
        private ReportRepository $reportRepository
    ) {}

    /**
     * @api
     */
    #[RequiresPermission(SprintsPermissions::VIEW, entityScoped: true)]
    public function getSprint(int $id): false|Models\Sprints
    {
        $sprint = $this->sprintRepository->getSprint($id);

        if (! $sprint) {
            return false;
        }

        // IDOR fence: the id alone names any project's sprint. Authorize VIEW against the sprint's
        // ACTUAL project — not the session project — mirroring the editSprint/deleteSprint write
        // fences and the Tickets::getTicket read precedent. entityScoped makes this fire on every
        // call (RPC, the EditSprint controller, and internal callers); the internal callers
        // (Reports burndown, EditSprint's own session) only ever pass sprints from an
        // already-accessible project, so they keep working — only the cross-project read is denied.
        $this->authorize(SprintsPermissions::VIEW, (int) $sprint->projectId);

        return $sprint;
    }

    /**
     * getNewSprint - builds a blank sprint pre-populated with the default
     * 13-day window (today through 13 days from now in the user's timezone).
     *
     * @internal Pure in-memory builder (no project, no repo) — not an RPC surface.
     */
    public function getNewSprint(): Models\Sprints
    {
        $sprint = new Models\Sprints;

        $sprint->startDate = dtHelper()->userNow();
        $sprint->endDate = dtHelper()->userNow()->addDays(13);

        return $sprint;
    }

    /**
     * getCurrentSprintId returns the ID of the current sprint in the project provided
     *
     * The session value is the task-view sprint filter, which can also hold non-id selections
     * such as "all" or "backlog"; those yield false just like an unset filter.
     *
     * @return false|int The selected sprint id, or false when no single sprint is selected
     *
     * @api
     */
    #[RequiresPermission(SprintsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getCurrentSprintId(int $projectId): false|int
    {
        $currentSprint = session('currentSprint', '');

        if ($currentSprint === '') {
            session(['currentSprint' => '']);

            return false;
        }

        // The value comes from a request filter: accept only a plain positive integer, so input such
        // as "1.5" or "1e3" cannot be cast into a different, valid sprint id.
        $sprintId = filter_var($currentSprint, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($sprintId !== false) {
            return $sprintId;
        }

        return false;
    }

    /**
     * @api
     */
    #[RequiresPermission(SprintsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getUpcomingSprint(int $projectId): false|\Leantime\Domain\Sprints\Models\Sprints
    {

        $sprint = $this->sprintRepository->getUpcomingSprint($projectId);

        if ($sprint) {
            return $sprint;
        }

        return false;
    }

    /**
     * All sprints of a project.
     *
     * A null project means the session project (never "every project"); a non-positive or
     * unresolvable project, or one the caller cannot view, yields an empty list.
     *
     * @param  int|string|null  $projectId  The project id, or null for the session project.
     * @return array<int, Models\Sprints>
     *
     * @api
     */
    #[RequiresPermission(SprintsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getAllSprints($projectId = null): array
    {
        $projectId = filter_var($projectId ?? session('currentProject'), FILTER_VALIDATE_INT);
        if ($projectId === false || $projectId <= 0) {
            return [];
        }

        if (! $this->can(SprintsPermissions::VIEW, $projectId)) {
            return [];
        }

        $sprints = $this->sprintRepository->getAllSprints($projectId);

        // Caution: Empty arrays will be false
        $sprints = $sprints ?: [];

        // Allow plugins (e.g. PgmPro) to append inherited sprints for this project — for
        // example the sprints owned by the project's parent program. Mirrors the
        // Tickets::filterTickets extension point.
        $sprints = self::dispatchFilter('afterGettingAllSprints', $sprints, ['projectId' => $projectId]);

        return $sprints;
    }

    /**
     * @api
     */
    #[RequiresPermission(SprintsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getAllFutureSprints(int $projectId): false|array
    {

        $sprints = $this->sprintRepository->getAllFutureSprints($projectId);

        if ($sprints) {
            return $sprints;
        }

        return false;
    }

    /**
     * @throws MissingParameterException When the start or end date is missing.
     *
     * @api
     */
    #[RequiresPermission(SprintsPermissions::CREATE, entityScoped: true)]
    public function addSprint($params): int|false
    {
        // Authorize before validating, so an unauthorized caller is denied first and never learns
        // about parameter requirements (matches the Tickets::addTicket authorize-then-validate order).
        $projectId = (int) ($params['projectId'] ?? session('currentProject'));
        $this->authorize(SprintsPermissions::CREATE, $projectId);

        $this->assertSprintDates($params);

        $sprint = new Models\Sprints;

        foreach ($params as $key => $value) {
            $sprint->$key = $value;
        }

        if (dtHelper()->isValidDateString($sprint->startDate ?? null)) {
            $sprint->startDate = dtHelper()->parseUserDateTime($sprint->startDate)->startOfDay()->formatDateTimeForDb();
        }

        if (dtHelper()->isValidDateString($sprint->endDate ?? null)) {
            $sprint->endDate = dtHelper()->parseUserDateTime($sprint->endDate)->endOfDay()->formatDateTimeForDb();
        }

        $sprint->projectId = $projectId;

        $result = $this->sprintRepository->addSprint($sprint);

        if ($result !== false) {
            SprintCreated::dispatch(
                sprintId: (int) $result,
                projectId: $projectId > 0 ? $projectId : null,
                lengthDays: $this->sprintLengthInDays($sprint->startDate ?? null, $sprint->endDate ?? null),
            );

            return $result;
        }

        return false;
    }

    /**
     * Calendar days from a sprint's start date to its end date, or null when either is missing.
     *
     * @param  mixed  $startDate  The stored start (UTC 'Y-m-d H:i:s').
     * @param  mixed  $endDate  The stored end (UTC 'Y-m-d H:i:s').
     */
    private function sprintLengthInDays(mixed $startDate, mixed $endDate): ?int
    {
        if (! is_string($startDate) || ! is_string($endDate) || $startDate === '' || $endDate === '') {
            return null;
        }

        try {
            $start = CarbonImmutable::parse(substr($startDate, 0, 10), 'UTC');
            $end = CarbonImmutable::parse(substr($endDate, 0, 10), 'UTC');
        } catch (\Throwable) {
            return null;
        }

        // Inclusive, like the burndown: a same-day sprint is 1 day, Oct 1–14 is 14 days.
        return (int) round(abs($end->getTimestamp() - $start->getTimestamp()) / 86400) + 1;
    }

    /**
     * @throws MissingParameterException When the start or end date is missing.
     *
     * @api
     */
    #[RequiresPermission(SprintsPermissions::EDIT, entityScoped: true)]
    public function editSprint($params): Models\Sprints|false
    {
        // IDOR fence: $params['id'] could name any project's sprint. Authorize edit against the
        // EXISTING sprint's project (and, below, the target project if it's relocated) BEFORE
        // validating params, so an unauthorized caller is denied first (Tickets authorize-first order).
        $existing = $this->sprintRepository->getSprint((int) ($params['id'] ?? 0));
        if ($existing) {
            $this->authorize(SprintsPermissions::EDIT, (int) $existing->projectId);
        }

        $this->assertSprintDates($params);

        $sprint = new Models\Sprints;

        foreach ($params as $key => $value) {
            $sprint->$key = $value;
        }

        if (dtHelper()->isValidDateString($sprint->startDate ?? null)) {
            $sprint->startDate = dtHelper()->parseUserDateTime($sprint->startDate)->startOfDay()->formatDateTimeForDb();
        }

        if (dtHelper()->isValidDateString($sprint->endDate ?? null)) {
            $sprint->endDate = dtHelper()->parseUserDateTime($sprint->endDate)->endOfDay()->formatDateTimeForDb();
        }

        $sprint->projectId = $params['projectId'] ?? session('currentProject');

        // Relocating to another project also requires edit rights there.
        if ((int) $sprint->projectId !== (int) ($existing->projectId ?? 0)) {
            $this->authorize(SprintsPermissions::EDIT, (int) $sprint->projectId);
        }

        $result = $this->sprintRepository->editSprint($sprint);

        if ($result) {
            return $sprint;
        }

        return false;
    }

    /**
     * deleteSprint - deletes a sprint and clears the current-sprint session value.
     *
     * @param  int  $id  Sprint id to delete.
     *
     * @api
     */
    #[RequiresPermission(SprintsPermissions::DELETE, entityScoped: true)]
    public function deleteSprint(int $id): void
    {
        // IDOR fence: the id alone identified the row before, so any editor could delete another
        // project's sprint (and detach its tickets). Authorize delete against the sprint's project.
        $sprint = $this->sprintRepository->getSprint($id);
        if ($sprint) {
            $this->authorize(SprintsPermissions::DELETE, (int) $sprint->projectId);
        }

        $this->sprintRepository->delSprint($id);

        session(['currentSprint' => '']);
    }

    /**
     * assertSprintDates - ensures the start and end date are both provided.
     *
     * @param  array  $params  Incoming sprint params.
     *
     * @throws MissingParameterException When the start or end date is missing.
     */
    private function assertSprintDates(array $params): void
    {
        if (($params['startDate'] ?? '') == '' || ($params['endDate'] ?? '') == '') {
            throw new MissingParameterException('First day and last day are required');
        }
    }

    /**
     * Burndown chart data for a sprint.
     *
     * The sprint is reloaded by id and authorized against its REAL project (a caller-supplied
     * model is never trusted for its project or dates).
     *
     * @param  Models\Sprints  $sprint  The sprint (only its id is used).
     * @return false|array The burndown rows, or false when the sprint does not exist.
     *
     * @throws \Exception
     *
     * @api
     */
    #[RequiresPermission(SprintsPermissions::VIEW, entityScoped: true)]
    public function getSprintBurndown(Models\Sprints $sprint): false|array
    {
        $sprintId = filter_var($sprint->id ?? null, FILTER_VALIDATE_INT);
        if ($sprintId === false || $sprintId <= 0) {
            return false;
        }

        // getSprint() authorizes VIEW on the sprint's real project (throws when denied).
        $sprint = $this->getSprint($sprintId);
        if (! $sprint) {
            return false;
        }

        $sprintValues = $this->reportRepository->getSprintReport($sprint->id);
        $sprintData = [];
        foreach ($sprintValues as $row) {
            if (is_object($row)) {
                $sprintData[$row->date] = $row;
            }
        }

        $allKeys = array_keys($sprintData);

        // If the first day is set in our reports table
        if (isset($allKeys[0])) {
            $plannedHoursStart = $sprintData[$allKeys[0]]->sum_planned_hours;
            $plannedNumStart = $sprintData[$allKeys[0]]->sum_todos;
            $plannedEffortStart = $sprintData[$allKeys[0]]->sum_points;
        } else {
            // If the sprint started today and we don't have any data to report, planned is 0
            $plannedHoursStart = 0;
            $plannedNumStart = 0;
            $plannedEffortStart = 0;
        }

        if (dtHelper()->isValidDateString($sprint->startDate)) {
            $dateStart = dtHelper()->parseDbDateTime($sprint->startDate)->startOfDay();
        } elseif (dtHelper()->isValidDateString($sprint->modified)) {
            $dateStart = dtHelper()->parseDbDateTime($sprint->modified)->startOfDay();
        } else {
            $dateStart = dtHelper()->userNow()->startOfDay();
        }

        if (dtHelper()->isValidDateString($sprint->endDate)) {
            $dateEnd = dtHelper()->parseDbDateTime($sprint->endDate)->endOfDay();
        } else {
            $dateEnd = dtHelper()->dbNow()->addDays(7)->endOfDay();
        }

        $sprintLength = $dateStart->diffInDays($dateEnd);

        $period = $dateStart->daysUntil($dateEnd);

        $sprintLength++; // Diff is 1 day less than actual sprint days (eg even if a sprint starts and ends today it should still be a 1 day sprint, but the diff would be 0)

        $dailyHoursPlanned = $plannedHoursStart / $sprintLength;
        $dailyNumPlanned = $plannedNumStart / $sprintLength;
        $dailyEffortPlanned = $plannedEffortStart / $sprintLength;

        $burnDown = [];
        $i = 0;
        foreach ($period as $key => $value) {
            $burnDown[$i]['date'] = $value->format('Y-m-d');

            if ($i === 0) {
                $burnDown[$i]['plannedHours'] = $plannedHoursStart;
                $burnDown[$i]['plannedNum'] = $plannedNumStart;
                $burnDown[$i]['plannedEffort'] = $plannedEffortStart;
            } else {
                $burnDown[$i]['plannedHours'] = $burnDown[$i - 1]['plannedHours'] - $dailyHoursPlanned;
                $burnDown[$i]['plannedNum'] = $burnDown[$i - 1]['plannedNum'] - $dailyNumPlanned;
                $burnDown[$i]['plannedEffort'] = $burnDown[$i - 1]['plannedEffort'] - $dailyEffortPlanned;
            }

            $dateKey = $value->format('Y-m-d').' 00:00:00';
            if (isset($sprintData[$dateKey])) {
                $burnDown[$i]['actualHours'] = $sprintData[$dateKey]->sum_estremaining_hours;
                $burnDown[$i]['actualNum'] = $sprintData[$dateKey]->sum_open_todos + $sprintData[$dateKey]->sum_progres_todos;
                $burnDown[$i]['actualEffort'] = $sprintData[$dateKey]->sum_points_open + $sprintData[$dateKey]->sum_points_progress;
            } elseif ($i === 0) {
                $burnDown[$i]['actualHours'] = $plannedHoursStart;
                $burnDown[$i]['actualNum'] = $plannedNumStart;
                $burnDown[$i]['actualEffort'] = $plannedEffortStart;
            } else {
                // If the date is in the future. Set to 0
                $today = dtHelper()->userNow(); // the user's today, not the process (UTC) day
                if ($value->format('Ymd') < $today->format('Ymd')) {
                    $burnDown[$i]['actualHours'] = $burnDown[$i - 1]['actualHours'];
                    $burnDown[$i]['actualNum'] = $burnDown[$i - 1]['actualNum'];
                    $burnDown[$i]['actualEffort'] = $burnDown[$i - 1]['actualEffort'];
                } else {
                    $burnDown[$i]['actualHours'] = '';
                    $burnDown[$i]['actualNum'] = '';
                    $burnDown[$i]['actualEffort'] = '';
                }
            }

            $i++;
        }

        return $burnDown;
    }

    /**
     * @throws \Exception
     *
     * @api
     */
    #[RequiresPermission(SprintsPermissions::VIEW, projectIdParam: 'project')]
    public function getCummulativeReport($project): false|array
    {

        if (! ($project)) {
            return false;
        }

        $sprintValues = $this->reportRepository->getFullReport($project);

        $sprintData = [];
        foreach ($sprintValues as $row) {
            $sprintData[$row->date] = $row;
        }

        $allKeys = array_keys($sprintData);
        $burnDown = [];

        if (count($allKeys) > 0) {
            // Both ends in the user's timezone, so the user's current day is always included.
            $userTimezone = dtHelper()->userNow()->getTimezone();
            $period = new DatePeriod(
                new DateTime($allKeys[count($allKeys) - 1], $userTimezone),
                new DateInterval('P1D'),
                dtHelper()->userNow()
            );

            // Each day is built as one complete row so every row has the same shape.
            $i = 0;
            foreach ($period as $value) {
                $dateKey = $value->format('Y-m-d').' 00:00:00';

                if (isset($sprintData[$dateKey])) {
                    $open = [
                        'actualHours' => $sprintData[$dateKey]->sum_estremaining_hours,
                        'actualNum' => $sprintData[$dateKey]->sum_open_todos,
                        'actualEffort' => $sprintData[$dateKey]->sum_points_open,
                    ];
                    $progress = [
                        'actualHours' => 0,
                        'actualNum' => $sprintData[$dateKey]->sum_progres_todos,
                        'actualEffort' => $sprintData[$dateKey]->sum_points_progress,
                    ];
                    $done = [
                        'actualHours' => $sprintData[$dateKey]->sum_logged_hours,
                        'actualNum' => $sprintData[$dateKey]->sum_closed_todos,
                        'actualEffort' => $sprintData[$dateKey]->sum_points_done,
                    ];
                } elseif ($i === 0) {
                    $zeroValues = ['actualHours' => 0, 'actualNum' => 0, 'actualEffort' => 0];
                    $open = $zeroValues;
                    $progress = $zeroValues;
                    $done = $zeroValues;
                } elseif ($value->format('Ymd') < dtHelper()->userNow()->format('Ymd')) {
                    // A past day without a report row carries the previous day forward
                    // (the user's today, not the process (UTC) day).
                    $open = $burnDown[$i - 1]['open'];
                    $progress = $burnDown[$i - 1]['progress'];
                    $done = $burnDown[$i - 1]['done'];
                } else {
                    // Today and future days stay empty
                    $emptyValues = ['actualHours' => '', 'actualNum' => '', 'actualEffort' => ''];
                    $open = $emptyValues;
                    $progress = $emptyValues;
                    $done = $emptyValues;
                }

                $burnDown[$i] = [
                    'date' => $value->format('Y-m-d'),
                    'open' => $open,
                    'progress' => $progress,
                    'done' => $done,
                ];

                $i++;
            }

        }

        return $burnDown;
    }
}
