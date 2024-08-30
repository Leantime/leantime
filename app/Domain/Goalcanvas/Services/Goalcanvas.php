<?php

namespace Leantime\Domain\Goalcanvas\Services {

    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;

    /**
     *
     *
     * @api
     */
    class Goalcanvas
    {
        private GoalcanvaRepository $goalRepository;
        public array $reportingSettings = [
            "linkonly",
            "linkAndReport",
            "nolink",
        ];

        /**
         * @param GoalcanvaRepository $goalRepository
         *
     */
        public function __construct(GoalcanvaRepository $goalRepository)
        {
            $this->goalRepository = $goalRepository;
        }

        /**
         * @param int $id
         * @return array
         *
     * @api
     */
        public function getCanvasItemsById(int $id): array
        {

            $goals = $this->goalRepository->getCanvasItemsById($id);

            if ($goals) {
                foreach ($goals as &$goal) {
                    $progressValue = 0;
                    $goal['goalProgress'] = 0;
                    $total = $goal['endValue'] - $goal['startValue'];
                    //Skip if total value is 0.
                    if ($total <= 0) {
                        continue;
                    }

                    if ($goal['setting'] == "linkAndReport") {
                        //GetAll Child elements
                        $currentValueSum = $this->getChildGoalsForReporting($goal['id']);

                        $goal['currentValue'] = $currentValueSum;
                        $progressValue = $currentValueSum - $goal['startValue'];
                    } else {
                        $progressValue = $goal['currentValue'] - $goal['startValue'];
                    }

                    $goal['goalProgress'] = round($progressValue / $total, 2) * 100;
                }
            }

            return $goals;
        }

        /**
         * @param $parentId
         * @return int|mixed
         *
     * @api
     */
        /**
         * @param $parentId
         * @return int|mixed
         *
     * @api
     */
        public function getChildGoalsForReporting($parentId): mixed
        {

            //Goals come back as rows for levl1 and lvl2 being columns, so
            //goal A | goalChildA
            //goal A | goalChildB
            //goal B
            //Checks if first level is also link+report or just link
            $goals = $this->goalRepository->getCanvasItemsByKPI($parentId);
            $currentValueSum = 0;
            foreach ($goals as $child) {
                if ($child['setting'] == "linkAndReport") {
                    $currentValueSum = $currentValueSum + $child['childCurrentValue'];
                } else {
                    $currentValueSum = $currentValueSum + $child["currentValue"];
                }
            }

            return $currentValueSum;
        }

        /**
         * @param $parentId
         * @return array
         *
     * @api
     */
        public function getChildrenbyKPI($parentId): array
        {

            $goals = array();
            //Goals come back as rows for levl1 and lvl2 being columns, so
            //goal A | goalChildA
            //goal A | goalChildB
            //goal B
            //Checks if first level is also link+report or just link
            $children = $this->goalRepository->getCanvasItemsByKPI($parentId);

            foreach ($children as $child) {
                //Added Child already? Look for child of child
                if (!isset($goals[$child['id']])) {
                    $goals[$child['id']] = array(
                        "id" => $child['id'],
                        "title" => $child['title'],
                        "startValue" => $child['startValue'],
                        "endValue" => $child['endValue'],
                        "currentValue" => $child['currentValue'],
                        "metricType" => $child['metricType'],
                        "boardTitle" => $child['boardTitle'],
                        "canvasId" => $child['canvasId'],
                        "projectName" => $child['projectName'],
                    );
                }

                if ($child['childId'] != '') {
                    if (isset($goals[$child['childId']]) === false) {
                        $goals[$child['childId']] = array(
                            "id" => $child['childId'],
                            "title" => $child['childTitle'],
                            "startValue" => $child['childStartValue'],
                            "endValue" => $child['childEndValue'],
                            "currentValue" => $child['childCurrentValue'],
                            "metricType" => $child['childMetricType'],
                            "boardTitle" => $child['childBoardTitle'],
                            "canvasId" => $child['childCanvasId'],
                            "projectName" => $child['childProjectName'],
                        );
                    }
                }
            }

            return $goals;
        }

        /**
         * @param $projectId
         * @return array
         *
     * @api
     */
        public function getParentKPIs($projectId): array
        {

            $kpis = $this->goalRepository->getAllAvailableKPIs($projectId);

            $goals = array();

            //Checks if first level is also link+report or just link
            foreach ($kpis as $kpi) {
                $goals[$kpi['id']] = array(
                    "id" => $kpi['id'],
                    "description" => $kpi['description'],
                    "project" => $kpi['projectName'],
                    "board" => $kpi['boardTitle'],
                );
            }

            return $goals;
        }

        public function getGoalsByMilestone($milestoneId): array
        {

            $goals = $this->goalRepository->getGoalsByMilestone($milestoneId);

            return $goals;
        }

        public function updateGoalboard($values)
        {
            return $this->goalRepository->updateCanvas($values);
        }

        public function createGoalboard($values)
        {
            return $this->goalRepository->addCanvas($values);
        }

        public function getSingleCanvas($id)
        {
            return $this->goalRepository->getSingleCanvas($id);
        }

        /**
         * @param array $values
         * @return int
         *
         * @api
         */
        public function createGoal($values)
        {
            return $this->goalRepository->createGoal($values);
        }

        /**
         * @param ?int $projectId
         * @param ?int $board
         * @return array
         *
         * @api
         */
        public function pollGoals(?int $projectId = null, ?int $board = null)
        {
            $goals = $this->goalRepository->getAllAccountGoals($projectId, $board);

            foreach ($goals as $key => $goal) {
                $goals[$key] = $this->prepareDatesForApiResponse($goal);
            }

            return $goals;
        }

        /**
         * @param ?int $projectId
         * @param ?int $board
         * @return array
         *
         * @api
         */
        public function pollForUpdatedGoals(?int $projectId = null, ?int $board = null): array|false
        {

            $goals = $this->goalRepository->getAllAccountGoals($projectId, $board);

            foreach ($goals as $key => $goal) {
                $goals[$key] = $this->prepareDatesForApiResponse($goal);
                $goals[$key]['id'] = $goal['id'] . '-' . $goal['modified'];
            }

            return $goals;
        }

        private function prepareDatesForApiResponse($goal) {

            if(dtHelper()->isValidDateString($goal['created'])) {
                $goal['created'] = dtHelper()->parseDbDateTime($goal['created'])->toIso8601ZuluString();
            }else{
                $goal['created'] = null;
            }

            if(dtHelper()->isValidDateString($goal['modified'])) {
                $goal['modified'] = dtHelper()->parseDbDateTime($goal['modified'])->toIso8601ZuluString();
            }else{
                $goal['modified'] = null;
            }

            if(dtHelper()->isValidDateString($goal['startDate'])) {
                $goal['startDate'] = dtHelper()->parseDbDateTime($goal['startDate'])->toIso8601ZuluString();
            }else{
                $goal['startDate'] = null;
            }

            if(dtHelper()->isValidDateString($goal['endDate'])) {
                $goal['endDate'] = dtHelper()->parseDbDateTime($goal['endDate'])->toIso8601ZuluString();
            }else{
                $goal['endDate'] = null;
            }

            return $goal;

        }
    }
}
