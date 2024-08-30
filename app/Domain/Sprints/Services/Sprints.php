<?php

namespace Leantime\Domain\Sprints\Services {

    use Leantime\Core\Support\DateTimeHelper;
    use Leantime\Core\Support\FromFormat;
    use Leantime\Core\Template as TemplateCore;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Reports\Repositories\Reports as ReportRepository;
    use Leantime\Domain\Sprints\Models;
    use DatePeriod;
    use DateTime;
    use DateInterval;

    /**
     *
     *
     * @api
     */
    class Sprints
    {
        private TemplateCore $tpl;
        private LanguageCore $language;
        private ProjectRepository $projectRepository;
        private SprintRepository $sprintRepository;
        private TicketRepository $ticketRepository;
        private ReportRepository $reportRepository;

        /**
         * @param TemplateCore      $tpl
         * @param LanguageCore      $language
         * @param ProjectRepository $projectRepository
         * @param SprintRepository  $sprintRepository
         * @param TicketRepository  $ticketRepository
         * @param ReportRepository  $reportRepository
         *
     */
        public function __construct(
            TemplateCore $tpl,
            LanguageCore $language,
            ProjectRepository $projectRepository,
            SprintRepository $sprintRepository,
            TicketRepository $ticketRepository,
            ReportRepository $reportRepository
        ) {
            $this->tpl = $tpl;
            $this->language = $language;
            $this->projectRepository = $projectRepository;
            $this->sprintRepository = $sprintRepository;
            $this->reportRepository = $reportRepository;
            $this->ticketRepository = $ticketRepository;
        }

        /**
         * @param $id
         * @return array|false
         *
     * @api
     */
        public function getSprint($id): false|Models\Sprints
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
         * @param $projectId
         * @return int|bool
         *
     * @api
     */
        public function getCurrentSprintId($projectId): bool|int
        {

            if (session()->exists("currentSprint") && session("currentSprint") != "") {
                return session("currentSprint");
            }

            //$sprint = $this->sprintRepository->getCurrentSprint($projectId);

            //if ($sprint) {
            //    session(["currentSprint" => $sprint->id]);
            //    return $sprint->id;
            //}

            session(["currentSprint" => ""]);

            return false;
        }

        /**
         * @param $projectId
         * @return array|false
         *
     * @api
     */
        public function getUpcomingSprint($projectId): false|array
        {

            $sprint = $this->sprintRepository->getUpcomingSprint($projectId);

            if ($sprint) {
                return $sprint;
            }

            return false;
        }

        /**
         * @param $projectId
         * @return array
         *
     * @api
     */
        public function getAllSprints($projectId = null): array
        {

            $sprints = $this->sprintRepository->getAllSprints($projectId);

            //Caution: Empty arrays will be false
            if ($sprints) {
                return $sprints;
            }

            return [];
        }

        /**
         * @param $projectId
         * @return array|false
         *
     * @api
     */
        public function getAllFutureSprints($projectId): false|array
        {

            $sprints = $this->sprintRepository->getAllFutureSprints($projectId);

            if ($sprints) {
                return $sprints;
            }

            return false;
        }

        /**
         * @param $params
         * @return false|object
         *
     * @api
     */
        public function addSprint($params): object|false
        {

            $sprint = (object) $params;
            $sprint->startDate = format(value: $sprint->startDate, fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime();
            $sprint->endDate = format(value: $sprint->endDate, fromFormat: FromFormat::UserDateEndOfDay)->isoDateTime();

            $sprint->projectId = $params['projectId'] ?? session("currentProject");

            $result = $this->sprintRepository->addSprint($sprint);

            if ($result !== false) {
                return $sprint;
            }

            return false;
        }

        /**
         * @param $params
         * @return false|object
         *
     * @api
     */
        public function editSprint($params): object|false
        {

            $sprint = (object) $params;
            $sprint->startDate = format(value: $sprint->startDate, fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime();
            $sprint->endDate = format(value: $sprint->endDate, fromFormat: FromFormat::UserDateEndOfDay)->isoDateTime();

            $sprint->projectId = $params['projectId'] ?? session("currentProject");

            $result = $this->sprintRepository->editSprint($sprint);

            if ($sprint) {
                return $sprint;
            }

            return false;
        }

        /**
         * @param $sprint
         * @return array|false
         * @throws \Exception
         *
     * @api
     */
        public function getSprintBurndown($sprint): false|array
        {

            if (!is_object($sprint)) {
                return false;
            }

            $sprintValues = $this->reportRepository->getSprintReport($sprint->id);
            $sprintData = array();
            foreach ($sprintValues as $row) {
                if (is_object($row)) {
                    $sprintData[$row->date] = $row;
                }
            }

            $allKeys = array_keys($sprintData);

            //If the first day is set in our reports table
            if (isset($allKeys[0])) {
                $plannedHoursStart = $sprintData[$allKeys[0]]->sum_planned_hours;
                $plannedNumStart = $sprintData[$allKeys[0]]->sum_todos;
                $plannedEffortStart = $sprintData[$allKeys[0]]->sum_points;
            } else {
                //If the sprint started today and we don't have any data to report, planned is 0
                $plannedHoursStart = 0;
                $plannedNumStart = 0;
                $plannedEffortStart = 0;
            }

            $dateStart =  dtHelper()->parseDbDateTime($sprint->startDate)->startOfDay();
            $dateEnd =  dtHelper()->parseDbDateTime($sprint->endDate)->endOfDay();


            $sprintLength = $dateStart->diffInDays($dateEnd);

            $period = $dateStart->daysUntil($dateEnd);

            $sprintLength++; //Diff is 1 day less than actual sprint days (eg even if a sprint starts and ends today it should still be a 1 day sprint, but the diff would be 0)

            $dailyHoursPlanned = $plannedHoursStart / $sprintLength;
            $dailyNumPlanned = $plannedNumStart / $sprintLength;
            $dailyEffortPlanned = $plannedEffortStart / $sprintLength;

            $burnDown = [];
            $i = 0;
            foreach ($period as $key => $value) {
                $burnDown[$i]['date'] = $value->format('Y-m-d');

                if ($i == 0) {
                    $burnDown[$i]["plannedHours"] = $plannedHoursStart;
                    $burnDown[$i]["plannedNum"] = $plannedNumStart;
                    $burnDown[$i]["plannedEffort"] = $plannedEffortStart;
                } else {
                    $burnDown[$i]["plannedHours"] = $burnDown[$i - 1]["plannedHours"] - $dailyHoursPlanned;
                    $burnDown[$i]["plannedNum"] = $burnDown[$i - 1]["plannedNum"] - $dailyNumPlanned;
                    $burnDown[$i]["plannedEffort"] = $burnDown[$i - 1]["plannedEffort"] - $dailyEffortPlanned;
                }

                if (isset($sprintData[$value->format('Y-m-d') . " 00:00:00"])) {
                    $burnDown[$i]["actualHours"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_estremaining_hours;
                    $burnDown[$i]["actualNum"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_open_todos + $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_progres_todos;
                    $burnDown[$i]["actualEffort"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_points_open + $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_points_progress;
                } else {
                    if ($i == 0) {
                        $burnDown[$i]["actualHours"] = $plannedHoursStart;
                        $burnDown[$i]["actualNum"] =  $plannedNumStart;
                        $burnDown[$i]["actualEffort"] = $plannedEffortStart;
                    } else {
                        //If the date is in the future. Set to 0
                        $today = new DateTime();
                        if ($value->format('Ymd') < $today->format('Ymd')) {
                            $burnDown[$i]["actualHours"] =  $burnDown[$i - 1]["actualHours"];
                            $burnDown[$i]["actualNum"] =  $burnDown[$i - 1]["actualNum"];
                            $burnDown[$i]["actualEffort"] = $burnDown[$i - 1]["actualEffort"];
                        } else {
                            $burnDown[$i]["actualHours"] =  '';
                            $burnDown[$i]["actualNum"] = '';
                            $burnDown[$i]["actualEffort"] = '';
                        }
                    }
                }

                $i++;
            }

            return $burnDown;
        }


        /**
         * @param $project
         * @return array|false
         * @throws \Exception
         *
     * @api
     */
        public function getCummulativeReport($project): false|array
        {

            if (!($project)) {
                return false;
            }

            $sprintValues = $this->reportRepository->getFullReport($project);


            $sprintData = array();
            foreach ($sprintValues as $row) {
                $sprintData[$row->date] = $row;
            }

            $allKeys = array_keys($sprintData);

            if (count($allKeys) == 0) {
                return [];
            }

            $period = new DatePeriod(
                new DateTime($allKeys[count($allKeys) - 1]),
                new DateInterval('P1D'),
                new DateTime()
            );

            $burnDown = [];
            $i = 0;


            foreach ($period as $key => $value) {
                $burnDown[$i]['date'] = $value->format('Y-m-d');

                if (isset($sprintData[$value->format('Y-m-d') . " 00:00:00"])) {
                    $burnDown[$i]["open"]["actualHours"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_estremaining_hours;
                    $burnDown[$i]["open"]["actualNum"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_open_todos;
                    $burnDown[$i]["open"]["actualEffort"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_points_open;

                    $burnDown[$i]["progress"]["actualHours"] = 0;
                    $burnDown[$i]["progress"]["actualNum"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_progres_todos;
                    $burnDown[$i]["progress"]["actualEffort"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_points_progress;

                    $burnDown[$i]["done"]["actualHours"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_logged_hours;
                    $burnDown[$i]["done"]["actualNum"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_closed_todos;
                    $burnDown[$i]["done"]["actualEffort"] = $sprintData[$value->format('Y-m-d') . " 00:00:00"]->sum_points_done;
                } else {
                    if ($i == 0) {
                        $burnDown[$i]["open"]["actualHours"] = 0;
                        $burnDown[$i]["open"]["actualNum"] =  0;
                        $burnDown[$i]["open"]["actualEffort"] = 0;

                        $burnDown[$i]["progress"]["actualHours"] = 0;
                        $burnDown[$i]["progress"]["actualNum"] =  0;
                        $burnDown[$i]["progress"]["actualEffort"] = 0;

                        $burnDown[$i]["done"]["actualHours"] = 0;
                        $burnDown[$i]["done"]["actualNum"] =  0;
                        $burnDown[$i]["done"]["actualEffort"] = 0;
                    } else {
                        //If the date is in the future. Set to 0
                        $today = new DateTime();
                        if ($value->format('Ymd') < $today->format('Ymd')) {
                            $burnDown[$i]["open"]["actualHours"] =  $burnDown[$i - 1]["open"]["actualHours"];
                            $burnDown[$i]["open"]["actualNum"] =  $burnDown[$i - 1]["open"]["actualNum"];
                            $burnDown[$i]["open"]["actualEffort"] = $burnDown[$i - 1]["open"]["actualEffort"];

                            $burnDown[$i]["progress"]["actualHours"] =  $burnDown[$i - 1]["progress"]["actualHours"];
                            $burnDown[$i]["progress"]["actualNum"] =  $burnDown[$i - 1]["progress"]["actualNum"];
                            $burnDown[$i]["progress"]["actualEffort"] = $burnDown[$i - 1]["progress"]["actualEffort"];

                            $burnDown[$i]["done"]["actualHours"] =  $burnDown[$i - 1]["done"]["actualHours"];
                            $burnDown[$i]["done"]["actualNum"] =  $burnDown[$i - 1]["done"]["actualNum"];
                            $burnDown[$i]["done"]["actualEffort"] = $burnDown[$i - 1]["done"]["actualEffort"];
                        } else {
                            $burnDown[$i]["open"]["actualHours"] =  '';
                            $burnDown[$i]["open"]["actualNum"] = '';
                            $burnDown[$i]["open"]["actualEffort"] = '';

                            $burnDown[$i]["progress"]["actualHours"] =  '';
                            $burnDown[$i]["progress"]["actualNum"] = '';
                            $burnDown[$i]["progress"]["actualEffort"] = '';

                            $burnDown[$i]["done"]["actualHours"] =  '';
                            $burnDown[$i]["done"]["actualNum"] = '';
                            $burnDown[$i]["done"]["actualEffort"] = '';
                        }
                    }
                }

                $i++;
            }

            return $burnDown;
        }
    }

}
