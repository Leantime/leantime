<?php

namespace leantime\domain\services {

    use GuzzleHttp\Client;
    use GuzzleHttp\Promise\PromiseInterface;
    use leantime\core;
    use leantime\domain\repositories;
    use Mpdf\Tag\P;
    use Ramsey\Uuid\Uuid;

    class goalcanvas
    {
        private repositories\goalcanvas $goalRepository;
        public $reportingSettings = [
            "linkonly",
            "linkAndReport",
            "nolink",
        ];

        public function __construct(repositories\goalcanvas $goalRepository)
        {
            $this->goalRepository = $goalRepository;
        }

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

        public function getChildGoalsForReporting($parentId)
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

        public function getChildrenbyKPI($parentId)
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

        public function getParentKPIs($parentProject)
        {

            $kpis = $this->goalRepository->getAllAvailableKPIs($parentProject);


            $goals = array();
            //Goals come back as rows for levl1 and lvl2 being columns, so
            //goal A | goalChildA
            //goal A | goalChildB
            //goal B
            //Checks if first level is also link+report or just link
            foreach ($kpis as $kpi) {
                //Added Child already? Look for child of child
                if (!isset($goals[$kpi['id']])) {
                    $goals[$kpi['id']] = array(
                        "id" => $kpi['id'],
                        "description" => $kpi['description'],
                        "project" => $kpi['projectName'],
                        "board" => $kpi['boardTitle'],

                    );
                }

                if ($kpi['parentKPIId'] != '') {
                    if (isset($goals[$kpi['parentKPIId']]) === false) {
                        $goals[$kpi['parentKPIId']] = array(
                            "id" => $kpi['parentKPIId'],
                            "description" => $kpi['parentKPIDescription'],
                            "project" => $kpi['parentProjectName'],
                            "board" => $kpi['parentBoardTitle'],
                        );
                    }
                }
            }

            return $goals;
        }
    }

}
