<?php

namespace Leantime\Domain\Sprints\Services;

use DateInterval;
use DatePeriod;
use DateTime;
use Leantime\Domain\Reports\Repositories\Reports as ReportRepository;
use Leantime\Domain\Sprints\Models;
use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;

/**
 * @api
 */
class Sprints
{
    public function __construct(
        private SprintRepository $sprintRepository,
        private ReportRepository $reportRepository
    ) {}

    /**
     * @api
     */
    public function getSprint(int $id): false|Models\Sprints
    {

        $sprint = $this->sprintRepository->getSprint($id);

        if ($sprint) {
            return $sprint;
        }

        return false;
    }

    /**
     * getCurrentSprintId returns the ID of the current sprint in the project provided
     *
     * @api
     */
    public function getCurrentSprintId(int $projectId): bool|int
    {

        if (session('currentSprint', '') !== '') {
            return session('currentSprint');
        }

        session(['currentSprint' => '']);

        return false;
    }

    /**
     * @api
     */
    public function getUpcomingSprint(int $projectId): false|array
    {

        $sprint = $this->sprintRepository->getUpcomingSprint($projectId);

        if ($sprint) {
            return $sprint;
        }

        return false;
    }

    /**
     * @api
     */
    public function getAllSprints($projectId = null): array
    {

        $sprints = $this->sprintRepository->getAllSprints($projectId);

        // Caution: Empty arrays will be false
        if ($sprints) {
            return $sprints;
        }

        return [];
    }

    /**
     * @api
     */
    public function getAllFutureSprints(int $projectId): false|array
    {

        $sprints = $this->sprintRepository->getAllFutureSprints($projectId);

        if ($sprints) {
            return $sprints;
        }

        return false;
    }

    /**
     * @api
     */
    public function addSprint($params): int|false
    {

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

        $result = $this->sprintRepository->addSprint($sprint);

        if ($result !== false) {
            return $result;
        }

        return false;
    }

    /**
     * @api
     */
    public function editSprint($params): Models\Sprints|false
    {

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

        $result = $this->sprintRepository->editSprint($sprint);

        if ($result) {
            return $sprint;
        }

        return false;
    }

    /**
     * @throws \Exception
     *
     * @api
     */
    public function getSprintBurndown(Models\Sprints $sprint): false|array
    {

        if (! is_object($sprint)) {
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
                $today = new DateTime;
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
            $period = new DatePeriod(
                new DateTime($allKeys[count($allKeys) - 1]),
                new DateInterval('P1D'),
                new DateTime
            );

            $i = 0;
            foreach ($period as $key => $value) {
                $burnDown[$i]['date'] = $value->format('Y-m-d');

                $dateKey = $value->format('Y-m-d').' 00:00:00';
                if (isset($sprintData[$dateKey])) {
                    $burnDown[$i]['open']['actualHours'] = $sprintData[$dateKey]->sum_estremaining_hours;
                    $burnDown[$i]['open']['actualNum'] = $sprintData[$dateKey]->sum_open_todos;
                    $burnDown[$i]['open']['actualEffort'] = $sprintData[$dateKey]->sum_points_open;

                    $burnDown[$i]['progress']['actualHours'] = 0;
                    $burnDown[$i]['progress']['actualNum'] = $sprintData[$dateKey]->sum_progres_todos;
                    $burnDown[$i]['progress']['actualEffort'] = $sprintData[$dateKey]->sum_points_progress;

                    $burnDown[$i]['done']['actualHours'] = $sprintData[$dateKey]->sum_logged_hours;
                    $burnDown[$i]['done']['actualNum'] = $sprintData[$dateKey]->sum_closed_todos;
                    $burnDown[$i]['done']['actualEffort'] = $sprintData[$dateKey]->sum_points_done;
                } elseif ($i === 0) {
                    $burnDown[$i]['open']['actualHours'] = 0;
                    $burnDown[$i]['open']['actualNum'] = 0;
                    $burnDown[$i]['open']['actualEffort'] = 0;

                    $burnDown[$i]['progress']['actualHours'] = 0;
                    $burnDown[$i]['progress']['actualNum'] = 0;
                    $burnDown[$i]['progress']['actualEffort'] = 0;

                    $burnDown[$i]['done']['actualHours'] = 0;
                    $burnDown[$i]['done']['actualNum'] = 0;
                    $burnDown[$i]['done']['actualEffort'] = 0;
                } else {
                    // If the date is in the future. Set to 0
                    $today = new DateTime;
                    if ($value->format('Ymd') < $today->format('Ymd')) {
                        $burnDown[$i]['open']['actualHours'] = $burnDown[$i - 1]['open']['actualHours'];
                        $burnDown[$i]['open']['actualNum'] = $burnDown[$i - 1]['open']['actualNum'];
                        $burnDown[$i]['open']['actualEffort'] = $burnDown[$i - 1]['open']['actualEffort'];

                        $burnDown[$i]['progress']['actualHours'] = $burnDown[$i - 1]['progress']['actualHours'];
                        $burnDown[$i]['progress']['actualNum'] = $burnDown[$i - 1]['progress']['actualNum'];
                        $burnDown[$i]['progress']['actualEffort'] = $burnDown[$i - 1]['progress']['actualEffort'];

                        $burnDown[$i]['done']['actualHours'] = $burnDown[$i - 1]['done']['actualHours'];
                        $burnDown[$i]['done']['actualNum'] = $burnDown[$i - 1]['done']['actualNum'];
                        $burnDown[$i]['done']['actualEffort'] = $burnDown[$i - 1]['done']['actualEffort'];
                    } else {
                        $burnDown[$i]['open']['actualHours'] = '';
                        $burnDown[$i]['open']['actualNum'] = '';
                        $burnDown[$i]['open']['actualEffort'] = '';

                        $burnDown[$i]['progress']['actualHours'] = '';
                        $burnDown[$i]['progress']['actualNum'] = '';
                        $burnDown[$i]['progress']['actualEffort'] = '';

                        $burnDown[$i]['done']['actualHours'] = '';
                        $burnDown[$i]['done']['actualNum'] = '';
                        $burnDown[$i]['done']['actualEffort'] = '';
                    }
                }

                $i++;
            }

        }

        return $burnDown;
    }
}
