<?php

namespace Leantime\Domain\Goalcanvas\Services {

    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;

    /**
     * @api
     */
    class Goalcanvas
    {
        private GoalcanvaRepository $goalRepository;

        public array $reportingSettings = [
            'linkonly',
            'linkAndReport',
            'nolink',
        ];

        public function __construct(GoalcanvaRepository $goalRepository)
        {
            $this->goalRepository = $goalRepository;
        }

        /**
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
                    // Skip if total value is 0.
                    if ($total <= 0) {
                        continue;
                    }

                    if ($goal['setting'] == 'linkAndReport') {
                        // GetAll Child elements
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
         * @return int|mixed
         *
         * @api
         */
        /**
         * @return int|mixed
         *
         * @api
         */
        public function getChildGoalsForReporting($parentId): mixed
        {

            // Goals come back as rows for levl1 and lvl2 being columns, so
            // goal A | goalChildA
            // goal A | goalChildB
            // goal B
            // Checks if first level is also link+report or just link
            $goals = $this->goalRepository->getCanvasItemsByKPI($parentId);
            $currentValueSum = 0;
            foreach ($goals as $child) {
                if ($child['setting'] == 'linkAndReport') {
                    $currentValueSum = $currentValueSum + $child['childCurrentValue'];
                } else {
                    $currentValueSum = $currentValueSum + $child['currentValue'];
                }
            }

            return $currentValueSum;
        }

        /**
         * @api
         */
        public function getChildrenbyKPI($parentId): array
        {

            $goals = [];
            // Goals come back as rows for levl1 and lvl2 being columns, so
            // goal A | goalChildA
            // goal A | goalChildB
            // goal B
            // Checks if first level is also link+report or just link
            $children = $this->goalRepository->getCanvasItemsByKPI($parentId);

            foreach ($children as $child) {
                // Added Child already? Look for child of child
                if (! isset($goals[$child['id']])) {
                    $goals[$child['id']] = [
                        'id' => $child['id'],
                        'title' => $child['title'],
                        'startValue' => $child['startValue'],
                        'endValue' => $child['endValue'],
                        'currentValue' => $child['currentValue'],
                        'metricType' => $child['metricType'],
                        'boardTitle' => $child['boardTitle'],
                        'canvasId' => $child['canvasId'],
                        'projectName' => $child['projectName'],
                    ];
                }

                if ($child['childId'] != '') {
                    if (isset($goals[$child['childId']]) === false) {
                        $goals[$child['childId']] = [
                            'id' => $child['childId'],
                            'title' => $child['childTitle'],
                            'startValue' => $child['childStartValue'],
                            'endValue' => $child['childEndValue'],
                            'currentValue' => $child['childCurrentValue'],
                            'metricType' => $child['childMetricType'],
                            'boardTitle' => $child['childBoardTitle'],
                            'canvasId' => $child['childCanvasId'],
                            'projectName' => $child['childProjectName'],
                        ];
                    }
                }
            }

            return $goals;
        }

        /**
         * @api
         */
        public function getParentKPIs($projectId): array
        {

            $kpis = $this->goalRepository->getAllAvailableKPIs($projectId);

            $goals = [];

            // Checks if first level is also link+report or just link
            foreach ($kpis as $kpi) {
                $goals[$kpi['id']] = [
                    'id' => $kpi['id'],
                    'description' => $kpi['description'],
                    'project' => $kpi['projectName'],
                    'board' => $kpi['boardTitle'],
                ];
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
         * @param  array  $values
         * @return int
         *
         * @api
         */
        public function createGoal($values)
        {
            return $this->goalRepository->createGoal($values);
        }

        /**
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
         * @return array
         *
         * @api
         */
        public function pollForUpdatedGoals(?int $projectId = null, ?int $board = null): array|false
        {

            $goals = $this->goalRepository->getAllAccountGoals($projectId, $board);

            foreach ($goals as $key => $goal) {
                $goals[$key] = $this->prepareDatesForApiResponse($goal);
                $goals[$key]['id'] = $goal['id'].'-'.$goal['modified'];
            }

            return $goals;
        }

        private function prepareDatesForApiResponse($goal)
        {

            if (dtHelper()->isValidDateString($goal['created'])) {
                $goal['created'] = dtHelper()->parseDbDateTime($goal['created'])->toIso8601ZuluString();
            } else {
                $goal['created'] = null;
            }

            if (dtHelper()->isValidDateString($goal['modified'])) {
                $goal['modified'] = dtHelper()->parseDbDateTime($goal['modified'])->toIso8601ZuluString();
            } else {
                $goal['modified'] = null;
            }

            if (dtHelper()->isValidDateString($goal['startDate'])) {
                $goal['startDate'] = dtHelper()->parseDbDateTime($goal['startDate'])->toIso8601ZuluString();
            } else {
                $goal['startDate'] = null;
            }

            if (dtHelper()->isValidDateString($goal['endDate'])) {
                $goal['endDate'] = dtHelper()->parseDbDateTime($goal['endDate'])->toIso8601ZuluString();
            } else {
                $goal['endDate'] = null;
            }

            return $goal;

        }
    }
}
