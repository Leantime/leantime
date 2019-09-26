<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;
    use \DatePeriod;
    use \DateTime;
    use \DateInterval;

    class sprints
    {

        private $projectRepository;
        private $sprintRepository;
        private $ticketRepository;
        private $reportRepository;

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->sprintRepository = new repositories\sprints();
            $this->reportRepository = new repositories\reports();
            $this->ticketRepository = new repositories\tickets();
        }

        public function getSprint($id)
        {

            $sprint = $this->sprintRepository->getSprint($id);

            if($sprint) {
                $sprint->startDate = date('m/d/Y', strtotime($sprint->startDate));
                $sprint->endDate = date('m/d/Y', strtotime($sprint->endDate));
                return $sprint;
            }

            return false;

        }

        public function getCurrentSprint($projectId)
        {

            $sprint = $this->sprintRepository->getCurrentSprint($projectId);

            if($sprint) {
                return $sprint;
            }

            return false;

        }

        public function getUpcomingSprint($projectId)
        {

            $sprint = $this->sprintRepository->getUpcomingSprint($projectId);

            if($sprint) {
                return $sprint;
            }

            return false;

        }

        public function getAllSprints($projectId)
        {

            $sprints = $this->sprintRepository->getAllSprints($projectId);

            //Caution: Empty arrays will be false
            if($sprints) {
                return $sprints;
            }

            return false;

        }

        public function getAllFutureSprints($projectId)
        {

            $sprints = $this->sprintRepository->getAllFutureSprints($projectId);

            if($sprints) {
                return $sprints;
            }

            return false;

        }


        public function addSprint($params)
        {

            $sprint = (object) $params;
            $sprint->startDate = date('Y-m-d 00:00:01', strtotime($sprint->startDate));
            $sprint->endDate = date('Y-m-d 23:59:59', strtotime($sprint->endDate));

            //TODO: Refactor when project selector is available
            $sprint->projectId = $_SESSION['currentProject'];

            $result = $this->sprintRepository->addSprint($sprint);

            if($result !== false) {
                return $sprint;
            }

            return false;

        }

        public function editSprint($params)
        {

            $sprint = (object) $params;
            $sprint->startDate = date('Y-m-d 00:00:01', strtotime($sprint->startDate));
            $sprint->endDate = date('Y-m-d 23:59:59', strtotime($sprint->endDate));

            //TODO: Refactor when project selector is available
            $sprint->projectId = $_SESSION['currentProject'];

            $result = $this->sprintRepository->editSprint($sprint);

            if($sprint) {
                return $sprint;
            }

            return false;

        }

        public function getSprintBurndown($sprint)
        {

            if(!is_object($sprint)) {
                return false;
            }

            $sprintValues = $this->reportRepository->getSprintReport($sprint->id);
            $sprintData = array();
            foreach($sprintValues as $row) {
                $sprintData[$row['date']] = $row;
            }

            $allKeys = array_keys($sprintData);

            //If the first day is set in our reports table
            if(isset($allKeys[0])) {
                $plannedHoursStart = $sprintData[$allKeys[0]]['sum_planned_hours'];
                $plannedNumStart = $sprintData[$allKeys[0]]['sum_todos'];
                $plannedEffortStart = $sprintData[$allKeys[0]]['sum_points'];
            }else{
                //If the sprint started today and we don't have any data to report, planned is 0
                $plannedHoursStart = 0;
                $plannedNumStart = 0;
                $plannedEffortStart = 0;
            }

            $dateStart = new DateTime($sprint->startDate);
            $dateEnd = new DateTime($sprint->endDate);
            $sprintLength = $dateEnd->diff($dateStart)->format("%a");
            $sprintLength++; //Diff is 1 day less than actual sprint days (eg even if a sprint starts and ends today it should still be a 1 day sprint, but the diff would be 0)

            $dailyHoursPlanned = $plannedHoursStart / $sprintLength;
            $dailyNumPlanned = $plannedNumStart / $sprintLength;
            $dailyEffortPlanned = $plannedEffortStart / $sprintLength;

            $period = new DatePeriod(
                new DateTime($sprint->startDate),
                new DateInterval('P1D'),
                new DateTime($sprint->endDate)
            );

            $burnDown = [];
            $i = 0;
            foreach ($period as $key => $value) {

                    $burnDown[$i]['date'] = $value->format('m/d/Y');

                if ($i == 0) {
                    $burnDown[$i]["plannedHours"] = $plannedHoursStart;
                    $burnDown[$i]["plannedNum"] = $plannedNumStart;
                    $burnDown[$i]["plannedEffort"] = $plannedEffortStart;
                } else {
                    $burnDown[$i]["plannedHours"] = $burnDown[$i - 1]["plannedHours"] - $dailyHoursPlanned;
                    $burnDown[$i]["plannedNum"] = $burnDown[$i - 1]["plannedNum"] - $dailyNumPlanned;
                    $burnDown[$i]["plannedEffort"] = $burnDown[$i - 1]["plannedEffort"] - $dailyEffortPlanned;
                }

                if (isset($sprintData[$value->format('Y-m-d')." 00:00:00"])) {
                    $burnDown[$i]["actualHours"] = $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_estremaining_hours'];
                    $burnDown[$i]["actualNum"] = $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_open_todos'] + $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_progres_todos'];
                    $burnDown[$i]["actualEffort"] = $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_points_open'] + $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_points_progress'];
                }else{
                    if ($i == 0) {

                        $burnDown[$i]["actualHours"] = $plannedHoursStart;
                        $burnDown[$i]["actualNum"] =  $plannedNumStart;
                        $burnDown[$i]["actualEffort"] = $plannedEffortStart;

                    }else{

                        //If the date is in the future. Set to 0
                        $today = new DateTime();
                        if($value->format('Ymd') < $today->format('Ymd')) {

                            $burnDown[$i]["actualHours"] =  $burnDown[$i-1]["actualHours"];
                            $burnDown[$i]["actualNum"] =  $burnDown[$i-1]["actualNum"];
                            $burnDown[$i]["actualEffort"] = $burnDown[$i-1]["actualEffort"];
                        }else{
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

        public function getBacklogBurndown($project)
        {

            if(!($project)) {
                return false;
            }

            $sprintValues = $this->reportRepository->getBacklogReport($project);
            $sprintData = array();
            foreach($sprintValues as $row) {
                $sprintData[$row['date']] = $row;
            }

            $allKeys = array_keys($sprintData);

            if(count($allKeys) === false || count($allKeys) == 0){
                return [];
            }

            $period = new DatePeriod(
                new DateTime($allKeys[0]),
                new DateInterval('P1D'),
                new DateTime($allKeys[(count($allKeys)-1)])
            );

            $burnDown = [];
            $i = 0;
            foreach ($period as $key => $value) {

                $burnDown[$i]['date'] = $value->format('m/d/Y');

                $burnDown[$i]["plannedHours"] = 0;
                $burnDown[$i]["plannedNum"] = 0;
                $burnDown[$i]["plannedEffort"] = 0;

                if (isset($sprintData[$value->format('Y-m-d')." 00:00:00"])) {
                    $burnDown[$i]["actualHours"] = $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_estremaining_hours'];
                    $burnDown[$i]["actualNum"] = $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_open_todos'] + $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_progres_todos'];
                    $burnDown[$i]["actualEffort"] = $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_points_open'] + $sprintData[$value->format('Y-m-d')." 00:00:00"]['sum_points_progress'];
                }else{
                    if ($i == 0) {

                        $burnDown[$i]["actualHours"] = 0;
                        $burnDown[$i]["actualNum"] =  0;
                        $burnDown[$i]["actualEffort"] = 0;

                    }else{

                        //If the date is in the future. Set to 0
                        $today = new DateTime();
                        if($value->format('Ymd') < $today->format('Ymd')) {

                            $burnDown[$i]["actualHours"] =  $burnDown[$i-1]["actualHours"];
                            $burnDown[$i]["actualNum"] =  $burnDown[$i-1]["actualNum"];
                            $burnDown[$i]["actualEffort"] = $burnDown[$i-1]["actualEffort"];
                        }else{
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


    }

}