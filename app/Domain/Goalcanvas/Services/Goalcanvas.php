<?php

namespace Leantime\Domain\Goalcanvas\Services {

    use Leantime\Core\Exceptions\ElementExistsException;
    use Leantime\Core\Exceptions\MissingParameterException;
    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
    use Leantime\Domain\Projects\Services\Projects;
    use Leantime\Core\Language;
    use Leantime\Core\Mailer;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepo;
    use Leantime\Domain\Canvas\Services\Canvas as CanvasService;


    /**
     *
     */
    class Goalcanvas
    {
        private GoalcanvaRepository $goalRepository;
        private ?Projects $projectService;

        protected ?Language $language;
        protected Mailer $mailer;

        protected const CANVAS_NAME = 'goal';
        protected QueueRepo $queueRepo;

        public array $reportingSettings = [
            "linkonly",
            "linkAndReport",
            "nolink",
        ];

        /**
         * @param GoalcanvaRepository $goalRepository
         */
        public function __construct(
            GoalcanvaRepository $goalRepository,
            ?Language $language = null
        ) {
            $this->goalRepository = $goalRepository;
            $this->projectService = app()->make(Projects::class);
            $this->language = $language;

            $this->mailer = app()->make(Mailer::class);
            $this->queueRepo = app()->make(QueueRepo::class);
        }

        /**
         * @param int $id
         * @return array
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
         */
        /**
         * @param $parentId
         * @return int|mixed
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

        public function pollGoals()
        {
            return $this->goalRepository->getAllAccountGoals();
        }

        public function pollForUpdatedGoals(): array|false
        {
            $goals = $this->goalRepository->getAllAccountGoals();

            foreach ($goals as $key => $goal) {
                $goals[$key]['id'] = $goal['id'] . '-' . $goal['modified'];
            }

            return $goals;
        }



        // Goal Dashboard Get
        public function handleDashboardGetRequest($params): array
        {
            $allCanvas = $this->goalRepository->getAllCanvas(session("currentProject"));

            if (empty($allCanvas)) {
                $allCanvas = $this->createDefaultCanvas();
            }

            $goalAnalytics = $this->calculateGoalAnalytics($allCanvas);
            $currentCanvasId = $this->determineCurrentCanvasId($allCanvas, $params);

            return [
                'currentCanvasId' => $currentCanvasId,
                'goalAnalytics' => $goalAnalytics,
                'canvasIcon' => $this->goalRepository->getIcon(),
                'canvasTypes' => $this->goalRepository->getCanvasTypes(),
                'statusLabels' => $this->goalRepository->getStatusLabels(),
                'relatesLabels' => $this->goalRepository->getRelatesLabels(),
                'dataLabels' => $this->goalRepository->getDataLabels(),
                'disclaimer' => $this->goalRepository->getDisclaimer(),
                'allCanvas' => $allCanvas,
                'canvasItems' => $this->goalRepository->getCanvasItemsById($currentCanvasId),
                'users' => $this->projectService->getUsersAssignedToProject(session("currentProject")),
            ];
        }

        private function createDefaultCanvas(): array
        {
            $values = [
                'title' => $this->language->__("label.board"),
                'author' => session("userdata.id"),
                'projectId' => session("currentProject"),
            ];
            $this->goalRepository->addCanvas($values);
            return $this->goalRepository->getAllCanvas(session("currentProject"));
        }

        private function calculateGoalAnalytics(array $allCanvas): array
        {
            $goalAnalytics = [
                "numCanvases" => count($allCanvas),
                "numGoals" => 0,
                "goalsOnTrack" => 0,
                "goalsAtRisk" => 0,
                "goalsMiss" => 0,
                "avgPercentComplete" => 0,
            ];

            $totalPercent = 0;
            foreach ($allCanvas as $canvas) {
                $canvasItems = $this->goalRepository->getCanvasItemsById($canvas["id"]);
                foreach ($canvasItems as $item) {
                    $goalAnalytics["numGoals"]++;
                    $this->updateGoalStatus($goalAnalytics, $item);
                    $totalPercent += $this->calculateItemPercentage($item);
                }
            }

            if ($goalAnalytics["numGoals"] > 0) {
                $goalAnalytics["avgPercentComplete"] = $totalPercent / $goalAnalytics["numGoals"];
            }

            return $goalAnalytics;
        }

        private function updateGoalStatus(array &$goalAnalytics, array $item): void
        {
            switch ($item["status"]) {
                case 'status_ontrack':
                    $goalAnalytics["goalsOnTrack"]++;
                    break;
                case 'status_atrisk':
                    $goalAnalytics["goalsAtRisk"]++;
                    break;
                case 'status_miss':
                    $goalAnalytics["goalsMiss"]++;
                    break;
            }
        }

        private function calculateItemPercentage(array $item): float
        {
            $total = $item['endValue'] - $item['startValue'];
            $progressValue = $item['currentValue'] - $item['startValue'];

            return $total > 0 ? round($progressValue / $total * 100, 2) : 0;
        }

        private function determineCurrentCanvasId(array $allCanvas, array $params): int
        {
            $sessionKey = "current" . strtoupper(static::CANVAS_NAME) . "Canvas";

            if (isset($params['id'])) {
                $currentCanvasId = (int)$params['id'];
            } elseif (isset($_REQUEST['searchCanvas'])) {
                $currentCanvasId = (int)$_REQUEST['searchCanvas'];
            } elseif (session()->exists($sessionKey)) {
                $currentCanvasId = session($sessionKey);
                $currentCanvasId = $this->validateCanvasId($currentCanvasId, $allCanvas);
            } else {
                $currentCanvasId = $allCanvas[0]['id'] ?? -1;
            }

            session([$sessionKey => $currentCanvasId]);

            return $currentCanvasId;
        }

        private function validateCanvasId(int $canvasId, array $allCanvas): int
        {
            foreach ($allCanvas as $canvas) {
                if ($canvasId == $canvas['id']) {
                    return $canvasId;
                }
            }
            return -1;
        }

        // Goal Dashboard Post

        public function handleDashboardPostRequest($params): array
        {
            $result = [
                'redirect' => false,
                'redirectUrl' => '',
                'notification' => null,
            ];

            $currentCanvasId = session("current" . strtoupper(static::CANVAS_NAME) . "Canvas") ?? -1;

            if (isset($params['newCanvas'])) {
                $result = $this->handleNewCanvas($params, $result);
            } elseif (isset($params['editCanvas']) && $currentCanvasId > 0) {
                $result = $this->handleEditCanvas($params, $currentCanvasId, $result);
            } elseif (isset($params['cloneCanvas']) && $currentCanvasId > 0) {
                $result = $this->handleCloneCanvas($params, $currentCanvasId, $result);
            } elseif (isset($params['mergeCanvas']) && $currentCanvasId > 0) {
                $result = $this->handleMergeCanvas($params, $currentCanvasId, $result);
            } elseif (isset($params['importCanvas'])) {
                $result = $this->handleImportCanvas($params, $result);
            }

            return $result;
        }

        private function handleNewCanvas($params, $result): array
        {
            if (!isset($params['canvastitle']) || empty($params['canvastitle'])) {
                return $this->setNotification($result, 'notification.please_enter_title', 'error');
            }

            if ($this->goalRepository->existCanvas(session("currentProject"), $params['canvastitle'])) {
                return $this->setNotification($result, 'notification.board_exists', 'error');
            }

            $values = [
                'title' => $params['canvastitle'],
                'author' => session("userdata.id"),
                'projectId' => session("currentProject"),
            ];
            $currentCanvasId = $this->goalRepository->addCanvas($values);

            $this->sendCanvasNotification('notification.board_created', $values['title']);

            session(["current" . strtoupper(static::CANVAS_NAME) . "Canvas" => $currentCanvasId]);
            return $this->setRedirect($result, BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/');
        }

        private function handleEditCanvas($params, $currentCanvasId, $result): array
        {
            if (!isset($params['canvastitle']) || empty($params['canvastitle'])) {
                return $this->setNotification($result, 'notification.please_enter_title', 'error');
            }

            if ($this->goalRepository->existCanvas(session("currentProject"), $params['canvastitle'])) {
                return $this->setNotification($result, 'notification.board_exists', 'error');
            }

            $values = ['title' => $params['canvastitle'], 'id' => $currentCanvasId];
            $this->goalRepository->updateCanvas($values);

            return $this->setRedirect($result, BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/', 'notification.board_edited');
        }

        private function handleCloneCanvas($params, $currentCanvasId, $result): array
        {
            if (!isset($params['canvastitle']) || empty($params['canvastitle'])) {
                return $this->setNotification($result, 'notification.please_enter_title', 'error');
            }

            if ($this->goalRepository->existCanvas(session("currentProject"), $params['canvastitle'])) {
                return $this->setNotification($result, 'notification.board_exists', 'error');
            }

            $newCanvasId = $this->goalRepository->copyCanvas(
                session("currentProject"),
                $currentCanvasId,
                session("userdata.id"),
                $params['canvastitle']
            );

            session(["current" . strtoupper(static::CANVAS_NAME) . "Canvas" => $newCanvasId]);
            return $this->setRedirect($result, BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/', 'notification.board_copied');
        }

        private function handleMergeCanvas($params, $currentCanvasId, $result): array
        {
            if (!isset($params['canvasid']) || $params['canvasid'] <= 0) {
                return $this->setNotification($result, 'notification.internal_error', 'error');
            }

            $status = $this->goalRepository->mergeCanvas($currentCanvasId, $params['canvasid']);

            if ($status) {
                return $this->setRedirect($result, BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/', 'notification.board_merged');
            } else {
                return $this->setNotification($result, 'notification.merge_error', 'error');
            }
        }

        private function handleImportCanvas($params, $result): array
        {
            if (!isset($_FILES['canvasfile']) || $_FILES['canvasfile']['error'] !== 0) {
                return $this->setNotification($result, 'notification.board_import_failed', 'error');
            }

            $uploadfile = tempnam(sys_get_temp_dir(), 'leantime.') . '.xml';

            if (!move_uploaded_file($_FILES['canvasfile']['tmp_name'], $uploadfile)) {
                return $this->setNotification($result, 'notification.board_import_failed', 'error');
            }

            $services = app()->make(static::class);
            $importCanvasId = $services->import(
                $uploadfile,
                static::CANVAS_NAME . 'canvas',
                projectId: session("currentProject"),
                authorId: session("userdata.id")
            );
            unlink($uploadfile);

            if ($importCanvasId === false) {
                return $this->setNotification($result, 'notification.board_import_failed', 'error');
            }

            $canvas = $this->goalRepository->getSingleCanvas($importCanvasId);
            $this->sendCanvasNotification('notification.board_imported', $canvas[0]['title']);

            session(["current" . strtoupper(static::CANVAS_NAME) . "Canvas" => $importCanvasId]);
            return $this->setRedirect($result, BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas/', 'notification.board_imported');
        }

        private function setNotification($result, $message, $type): array
        {
            $result['notification'] = [
                'message' => $this->language ? $this->language->__($message) : $message,
                'type' => $type
            ];
            return $result;
        }

        private function setRedirect($result, $url, $notificationMessage = null): array
        {
            $result['redirect'] = true;
            $result['redirectUrl'] = $url;
            if ($notificationMessage) {
                $result['notification'] = [
                    'message' => $this->language ? $this->language->__($notificationMessage) : $notificationMessage,
                    'type' => 'success'
                ];
            }
            return $result;
        }

        private function sendCanvasNotification($subject, $title): void
        {
            if (!$this->mailer || !$this->projectService || !$this->queueRepo) {
                return;
            }

            $users = $this->projectService->getUsersToNotify(session("currentProject"));
            $this->mailer->setSubject($this->language->__($subject));

            $actual_link = CURRENT_URL;
            $message = sprintf(
                $this->language->__('email_notifications.canvas_created_message'),
                session("userdata.name"),
                "<a href='" . $actual_link . "'>" . $title . '</a>'
            );
            $this->mailer->setHtml($message);

            $this->queueRepo->queueMessageToUsers(
                $users,
                $message,
                $this->language->__($subject),
                session("currentProject")
            );
        }


        // Show canvas get data

        public function getCurrentCanvasId($params)
        {
            $allCanvas = $this->getAllCanvas();
            $currentCanvasId = $this->getStoredCanvasId();

            if (empty($allCanvas)) {
                $currentCanvasId = $this->createDefaultCanvas();
                $allCanvas = $this->getAllCanvas();
            } elseif ($currentCanvasId === -1 && !empty($allCanvas)) {
                $currentCanvasId = $allCanvas[0]['id'];
            }

            if (isset($params['id'])) {
                $currentCanvasId = (int)$params['id'];
            }

            if (isset($_REQUEST['searchCanvas'])) {
                $currentCanvasId = (int)$_REQUEST['searchCanvas'];
                // You might want to handle the redirect in the controller
            }

            $this->storeCurrentCanvasId($currentCanvasId);

            return $currentCanvasId;
        }

        public function getAllCanvas()
        {
            return $this->goalRepository->getAllCanvas(session("currentProject"));
        }


        private function getStoredCanvasId()
        {
            $sessionKey = "current" . strtoupper(static::CANVAS_NAME) . "Canvas";
            return session()->exists($sessionKey) ? session($sessionKey) : -1;
        }

        private function storeCurrentCanvasId($canvasId)
        {
            $sessionKey = "current" . strtoupper(static::CANVAS_NAME) . "Canvas";
            session([$sessionKey => $canvasId]);
        }

        // Show canvas post data

        public function createNewCanvas(string $title): array
        {
            if (empty($title)) {
                throw new MissingParameterException($this->language->__('notification.please_enter_title'));
            }

            if ($this->goalRepository->existCanvas(session("currentProject"), $title)) {
                return ['success' => false, 'message' => $this->language->__('notification.board_exists')];
            }

            $values = [
                'title' => $title,
                'author' => session("userdata.id"),
                'projectId' => session("currentProject"),
            ];
            $canvasId = $this->goalRepository->addCanvas($values);

            $this->notifyUsers('canvas_created', $title);

            session(["current" . strtoupper(static::CANVAS_NAME) . "Canvas" => $canvasId]);
            return ['success' => true, 'message' => $this->language->__('notification.board_created')];
        }

        public function editCanvas(string $title, int $canvasId): array
        {
            if (empty($title)) {
                throw new MissingParameterException($this->language->__('notification.please_enter_title'));
            }

            if ($this->goalRepository->existCanvas(session("currentProject"), $title)) {               
                throw new ElementExistsException($this->language->__('notification.board_exists'));
            }

            $values = ['title' => $title, 'id' => $canvasId];
            $this->goalRepository->updateCanvas($values);

            return ['success' => true, 'message' => $this->language->__('notification.board_edited')];
        }

        public function cloneCanvas(string $title, int $canvasId): array
        {
            if (empty($title)) {
                throw new MissingParameterException($this->language->__('notification.please_enter_title'));
            }

            if ($this->goalRepository->existCanvas(session("currentProject"), $title)) {               
                throw new ElementExistsException($this->language->__('notification.board_exists'));
            }

            $newCanvasId = $this->goalRepository->copyCanvas(
                session("currentProject"),
                $canvasId,
                session("userdata.id"),
                $title
            );

            session(["current" . strtoupper(static::CANVAS_NAME) . "Canvas" => $newCanvasId]);
            return ['success' => true, 'message' => $this->language->__('notification.board_copied')];
        }

        public function mergeCanvas(int $canvasId, int $mergeCanvasId): array
        {
            if ($canvasId <= 0 || $mergeCanvasId <= 0) {
                throw new \Exception($this->language->__('notification.internal_error'));
            }

            $status = $this->goalRepository->mergeCanvas($canvasId, $mergeCanvasId);

            if ($status) {
                return ['success' => true, 'message' => $this->language->__('notification.board_merged')];
            } else {
                throw new \Exception($this->language->__('notification.internal_error'));

            }
        }

        public function importCanvas(?array $file): array
        {
            if (!$file || $file['error'] !== 0) {
                throw new \Exception($this->language->__('notification.board_import_failed'));
            }

            $uploadfile = tempnam(sys_get_temp_dir(), 'leantime.') . '.xml';

            if (!move_uploaded_file($file['tmp_name'], $uploadfile)) {
                throw new \Exception($this->language->__('notification.board_import_failed'));
            }

            $services = app()->make(CanvasService::class);
            $importCanvasId = $services->import(
                $uploadfile,
                static::CANVAS_NAME . 'canvas',
                projectId: session("currentProject"),
                authorId: session("userdata.id")
            );
            unlink($uploadfile);

            if ($importCanvasId === false) {
                throw new \Exception($this->language->__('notification.board_import_failed'));
            }

            session(["current" . strtoupper(static::CANVAS_NAME) . "Canvas" => $importCanvasId]);
            $canvas = $this->goalRepository->getSingleCanvas($importCanvasId);
            $this->notifyUsers('canvas_imported', $canvas[0]['title']);

            return ['success' => true, 'message' => $this->language->__('notification.board_imported')];
        }

        private function notifyUsers(string $action, string $canvasTitle): void
        {
            $mailer = app()->make(Mailer::class);
            $users = $this->projectService->getUsersToNotify(session("currentProject"));

            $subject = $this->language->__("notification.board_{$action}");
            $mailer->setSubject($subject);

            $actual_link = CURRENT_URL;
            $message = sprintf(
                $this->language->__("email_notifications.canvas_{$action}_message"),
                session("userdata.name"),
                "<a href='" . $actual_link . "'>" . $canvasTitle . '</a>'
            );
            $mailer->setHtml($message);

            $queue = app()->make(QueueRepo::class);
            $queue->queueMessageToUsers(
                $users,
                $message,
                $subject,
                session("currentProject")
            );
        }
    }
}
