<?php

namespace Leantime\Domain\Goalcanvas\Services {

    use Leantime\Core\Exceptions\ElementExistsException;
    use Leantime\Core\Exceptions\MissingParameterException;
    use Leantime\Core\Language;
    use Leantime\Core\Mailer;
    use Leantime\Core\Support\FromFormat;
    use Leantime\Domain\Canvas\Services\Canvas as CanvasService;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
    use Leantime\Domain\Projects\Services\Projects;
    // use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepo;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;

    /**
     * @api
     */
    class Goalcanvas
    {
        protected Mailer $mailer;

        protected QueueRepo $queueRepo;

        protected CommentRepository $commentsRepo;

        protected TicketService $ticketService;

        private GoalcanvaRepository $goalRepository;

        private ?Projects $projectService;

        protected ?Language $language;

        // protected CommentsService $commentsService;

        public array $reportingSettings = [
            'linkonly',
            'linkAndReport',
            'nolink',
        ];

        public function __construct(
            GoalcanvaRepository $goalRepository,
            CommentRepository $commentsRepo,
            Mailer $mailer,
            QueueRepo $queueRepo,
            Projects $projectService,
            ?Language $language = null,
        ) {
            $this->goalRepository = $goalRepository;
            $this->projectService = $projectService;
            $this->language = $language;
            $this->commentsRepo = $commentsRepo;

            $this->mailer = $mailer;
            $this->queueRepo = $queueRepo;
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
                    //Skip if total value is 0.
                    if ($total <= 0) {
                        continue;
                    }

                    if ($goal['setting'] == 'linkAndReport') {
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

            //Goals come back as rows for levl1 and lvl2 being columns, so
            //goal A | goalChildA
            //goal A | goalChildB
            //goal B
            //Checks if first level is also link+report or just link
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
            //Goals come back as rows for levl1 and lvl2 being columns, so
            //goal A | goalChildA
            //goal A | goalChildB
            //goal B
            //Checks if first level is also link+report or just link
            $children = $this->goalRepository->getCanvasItemsByKPI($parentId);

            foreach ($children as $child) {
                //Added Child already? Look for child of child
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

            //Checks if first level is also link+report or just link
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

        // Retrieves goals associated with a specific milestone
        /**
         * Retrieves goals by milestone ID.
         *
         * @param  int  $milestoneId  The ID of the milestone.
         * @return array The array of goals.
         */
        public function getGoalsByMilestone($milestoneId): array
        {

            $goals = $this->goalRepository->getGoalsByMilestone($milestoneId);

            return $goals;
        }

        /**
         * Updates an existing goalboard with provided values.
         *
         * @param  array  $values  The values to update the goalboard with.
         * @return mixed The result of the update operation.
         */

        /**
         * Updates the goalboard with the given values.
         *
         * @param  array  $values  The values to update the goalboard with.
         * @return mixed The result of the update operation.
         */
        public function updateGoalboard($values)
        {
            return $this->goalRepository->updateCanvas($values);
        }

        /**
         * Creates a goal board.
         *
         * @param  array  $values  The values to be used for creating the goal board.
         * @return mixed The result of adding the goal board to the repository.
         */
        public function createGoalboard($values)
        {
            return $this->goalRepository->addCanvas($values);
        }

        /**
         * Retrieves a single canvas by its ID
         *
         * @param  int  $id  The ID of the canvas
         * @return mixed The canvas object
         */
        // Retrieves a single canvas by its ID

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
         * Retrieves all goals for the current account.
         *
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
         * Retrieves all updated goals for the current account, with modified IDs
         *
         * @return array|false An array of updated goals with modified IDs, or false if there was an error
         */

        /**
         * @return array
         *
         * @api
         */
        public function pollForUpdatedGoals(?int $projectId = null, ?int $board = null): array|false
        {
            $goals = $this->goalRepository->getAllAccountGoals();

            foreach ($goals as $key => $goal) {
                $goals[$key]['id'] = $goal['id'].'-'.$goal['modified'];
            }

            return $goals;
        }

        /**
         * Retrieves the related labels for the goal canvas.
         *
         * @return array The related labels for the goal canvas.
         */
        public function getRelatesLabels()
        {
            return $this->goalRepository->getRelatesLabels();
        }

        /**
         * Retrieves the disclaimer from the goal repository.
         *
         * @return string The disclaimer text.
         */
        public function getDisclaimer()
        {
            return $this->goalRepository->getDisclaimer();
        }

        /**
         * Handles the GET request for the goal dashboard.
         *
         * @param  array  $params  The parameters for the request.
         * @return array The response data for the request.
         */
        public function handleDashboardGetRequest($params): array
        {
            $allCanvas = $this->goalRepository->getAllCanvasId(session('currentProject'));

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
                'users' => $this->projectService->getUsersAssignedToProject(session('currentProject')),
            ];
        }


        public function getGoalStatusLabels()
        {
            return $this->goalRepository->getStatusLabels();
        }

        public function getGoalDataLabels()
        {
            return $this->goalRepository->getDataLabels();
        }

        public function getGoalRelatesLabels()
        {
            return $this->goalRepository->getRelatesLabels();
        }
        /**
         * Creates a default canvas when no canvas exists.
         *
         * @return array The array of all canvases after creating the default canvas.
         */
        // Creates a default canvas when no canvas exists
        private function createDefaultCanvas(): array
        {
            $values = [
                'title' => $this->language->__('label.board'),
                'author' => session('userdata.id'),
                'projectId' => session('currentProject'),
            ];
            $this->goalRepository->addCanvas($values);

            return $this->goalRepository->getAllCanvas(session('currentProject'));
        }

        // Calculates analytics for all goals across all canvases
        private function calculateGoalAnalytics(array $allCanvas): array
        {
            $goalAnalytics = [
                'numCanvases' => count($allCanvas),
                'numGoals' => 0,
                'goalsOnTrack' => 0,
                'goalsAtRisk' => 0,
                'goalsMiss' => 0,
                'avgPercentComplete' => 0,
            ];

            $totalPercent = 0;
            foreach ($allCanvas as $canvas) {
                $canvasItems = $this->goalRepository->getCanvasItemsById($canvas['id']);
                foreach ($canvasItems as $item) {
                    $goalAnalytics['numGoals']++;
                    $this->updateGoalStatus($goalAnalytics, $item);
                    $totalPercent += $this->calculateItemPercentage($item);
                }
            }

            if ($goalAnalytics['numGoals'] > 0) {
                $goalAnalytics['avgPercentComplete'] = $totalPercent / $goalAnalytics['numGoals'];
            }

            return $goalAnalytics;
        }

        /**
         * Updates the goal status counters in the analytics array.
         *
         * This method is responsible for updating the goal status counters in the provided goalAnalytics array based on the status of the given item.
         *
         * @param  array  &$goalAnalytics  The goal analytics array to update.
         * @param  array  $item  The item containing the status information.
         */
        // Updates the goal status counters in the analytics array
        private function updateGoalStatus(array &$goalAnalytics, array $item): void
        {
            switch ($item['status']) {
                case 'status_ontrack':
                    $goalAnalytics['goalsOnTrack']++;
                    break;
                case 'status_atrisk':
                    $goalAnalytics['goalsAtRisk']++;
                    break;
                case 'status_miss':
                    $goalAnalytics['goalsMiss']++;
                    break;
            }
        }

        /**
         * Calculates the percentage completion of a goal item.
         *
         * @param  array  $item  The goal item containing 'startValue', 'endValue', and 'currentValue'.
         * @return float The percentage completion of the goal item.
         */
        // Calculates the percentage completion of a goal item

        private function calculateItemPercentage(array $item): float
        {
            $total = $item['endValue'] - $item['startValue'];
            $progressValue = $item['currentValue'] - $item['startValue'];

            return $total > 0 ? round($progressValue / $total * 100, 2) : 0;
        }

        /**
         * Determines the current canvas ID based on various parameters.
         *
         * @param  array  $allCanvas  An array of all available canvas.
         * @param  array  $params  An array of parameters.
         * @return int The current canvas ID.
         */
        // Determines the current canvas ID based on various parameters

        private function determineCurrentCanvasId(array $allCanvas, array $params): int
        {
            $sessionKey = 'current'.strtoupper('goal').'Canvas';

            if (isset($params['id'])) {
                $currentCanvasId = (int) $params['id'];
            } elseif (isset($_REQUEST['searchCanvas'])) {
                $currentCanvasId = (int) $_REQUEST['searchCanvas'];
            } elseif (session()->exists($sessionKey)) {
                $currentCanvasId = session($sessionKey);
                $currentCanvasId = $this->validateCanvasId($currentCanvasId, $allCanvas);
            } else {
                $currentCanvasId = $allCanvas[0]['id'] ?? -1;
            }

            session([$sessionKey => $currentCanvasId]);

            return $currentCanvasId;
        }

        /**
         * Validates the canvas ID against an array of all canvases.
         *
         * @param  int  $canvasId  The ID of the canvas to validate.
         * @param  array  $allCanvas  An array of all canvases.
         * @return int Returns the validated canvas ID if found, otherwise returns -1.
         */
        private function validateCanvasId(int $canvasId, array $allCanvas): int
        {
            foreach ($allCanvas as $canvas) {
                if ($canvasId == $canvas['id']) {
                    return $canvasId;
                }
            }

            return -1;
        }

        /**
         * Retrieves the current canvas ID, creating a default if necessary.
         *
         * @param  array  $params  The parameters for retrieving the current canvas ID.
         * @return int The current canvas ID.
         */
        // Retrieves the current canvas ID, creating a default if necessary

        public function getCurrentCanvasId($params)
        {
            $allCanvas = $this->getAllCanvas();
            $currentCanvasId = $this->getStoredCanvasId();

            if (empty($allCanvas)) {
                $currentCanvasId = $this->createDefaultCanvas();
                $allCanvas = $this->getAllCanvas();
            } elseif ($currentCanvasId === -1 && ! empty($allCanvas)) {
                $currentCanvasId = $allCanvas[0]['id'];
            }

            if (isset($params['id'])) {
                $currentCanvasId = (int) $params['id'];
            }

            if (isset($_REQUEST['searchCanvas'])) {
                $currentCanvasId = (int) $_REQUEST['searchCanvas'];
                // You might want to handle the redirect in the controller
            }

            $this->storeCurrentCanvasId($currentCanvasId);

            return $currentCanvasId;
        }

        /**
         * Retrieves all canvases for the current project.
         *
         * @return array The array of canvases.
         */
        // Retrieves all canvases for the current project

        /**
         * Retrieves all canvas from the goal repository.
         *
         * @return array The array of canvas.
         */
        public function getAllCanvas()
        {
            return $this->goalRepository->getAllCanvas(session('currentProject'));
        }

        /**
         * Retrieves the stored canvas ID from the session.
         *
         * @return int The stored canvas ID, or -1 if it doesn't exist.
         */
        // Retrieves the stored canvas ID from the session
        private function getStoredCanvasId()
        {
            $sessionKey = 'current'.strtoupper('goal').'Canvas';

            return session()->exists($sessionKey) ? session($sessionKey) : -1;
        }

        /**
         * Stores the current canvas ID in the session.
         *
         * @param  int  $canvasId  The ID of the canvas to be stored.
         * @return void
         */
        // Stores the current canvas ID in the session

        private function storeCurrentCanvasId($canvasId)
        {
            $sessionKey = 'current'.strtoupper('goal').'Canvas';
            session([$sessionKey => $canvasId]);
        }

        /**
         * Creates a new canvas with the given title.
         *
         * @param  string  $title  The title of the canvas.
         * @return array An array with the success status and a message.
         *
         * @throws MissingParameterException If the title is empty.
         */
        // Creates a new canvas with the given title

        public function createNewCanvas(string $title): array
        {
            if (empty($title)) {
                throw new MissingParameterException($this->language->__('notification.please_enter_title'));
            }

            if ($this->goalRepository->existCanvas(session('currentProject'), $title)) {
                return ['success' => false, 'message' => $this->language->__('notification.board_exists')];
            }

            $values = [
                'title' => $title,
                'author' => session('userdata.id'),
                'projectId' => session('currentProject'),
            ];
            $canvasId = $this->goalRepository->addCanvas($values);

            $this->notifyUsers('canvas_created', $title);

            session(['current'.strtoupper('goal').'Canvas' => $canvasId]);

            return ['success' => true, 'message' => $this->language->__('notification.board_created')];
        }

        /**
         * Edits an existing canvas with the given title and ID.
         *
         * @param  string  $title  The new title for the canvas.
         * @param  int  $canvasId  The ID of the canvas to be edited.
         * @return array An array containing the success status and a message.
         *
         * @throws MissingParameterException If the title parameter is empty.
         * @throws ElementExistsException If a canvas with the same title already exists.
         */

        // Edits an existing canvas with the given title and ID

        public function editCanvas(string $title, int $canvasId): array
        {
            if (empty($title)) {
                throw new MissingParameterException($this->language->__('notification.please_enter_title'));
            }

            if ($this->goalRepository->existCanvas(session('currentProject'), $title)) {
                throw new ElementExistsException($this->language->__('notification.board_exists'));
            }

            $values = ['title' => $title, 'id' => $canvasId];
            $this->goalRepository->updateCanvas($values);

            return ['success' => true, 'message' => $this->language->__('notification.board_edited')];
        }

        /**
         * Clones an existing canvas with a new title.
         *
         * @param  string  $title  The new title for the cloned canvas.
         * @param  int  $canvasId  The ID of the canvas to be cloned.
         * @return array An array containing the success status and a message.
         *
         * @throws MissingParameterException If the title parameter is empty.
         * @throws ElementExistsException If a canvas with the same title already exists.
         */
        // Clones an existing canvas with a new title
        public function cloneCanvas(string $title, int $canvasId): array
        {
            if (empty($title)) {
                throw new MissingParameterException($this->language->__('notification.please_enter_title'));
            }

            if ($this->goalRepository->existCanvas(session('currentProject'), $title)) {
                throw new ElementExistsException($this->language->__('notification.board_exists'));
            }

            $newCanvasId = $this->goalRepository->copyCanvas(
                session('currentProject'),
                $canvasId,
                session('userdata.id'),
                $title
            );

            session(['current'.strtoupper('goal').'Canvas' => $newCanvasId]);

            return ['success' => true, 'message' => $this->language->__('notification.board_copied')];
        }

        /**
         * Merges two canvases.
         *
         * @param  int  $canvasId  The ID of the canvas to merge.
         * @param  int  $mergeCanvasId  The ID of the canvas to merge with.
         * @return array An array containing the success status and a message.
         *
         * @throws \Exception If either $canvasId or $mergeCanvasId is less than or equal to 0.
         * @throws \Exception If the merge operation fails.
         */
        // Merges two canvases

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

        /**
         * Imports a canvas from an uploaded file.
         *
         * @param  array|null  $file  The uploaded file.
         * @return array The result of the import operation.
         *
         * @throws \Exception If the file is not provided or there is an error with the file upload.
         */
        // Imports a canvas from an uploaded file

        public function importCanvas(?array $file): array
        {
            if (! $file || $file['error'] !== 0) {
                throw new \Exception($this->language->__('notification.board_import_failed'));
            }

            $uploadfile = tempnam(sys_get_temp_dir(), 'leantime.').'.xml';

            if (! move_uploaded_file($file['tmp_name'], $uploadfile)) {
                throw new \Exception($this->language->__('notification.board_import_failed'));
            }

            $services = app()->make(CanvasService::class);
            $importCanvasId = $services->import(
                $uploadfile,
                'goal'.'canvas',
                projectId: session('currentProject'),
                authorId: session('userdata.id')
            );
            unlink($uploadfile);

            if ($importCanvasId === false) {
                throw new \Exception($this->language->__('notification.board_import_failed'));
            }

            session(['current'.strtoupper('goal').'Canvas' => $importCanvasId]);
            $canvas = $this->goalRepository->getSingleCanvas($importCanvasId);
            $this->notifyUsers('canvas_imported', $canvas[0]['title']);

            return ['success' => true, 'message' => $this->language->__('notification.board_imported')];
        }

        /**
         * Notifies users about canvas-related actions.
         *
         * @param  string  $action  The action being performed on the canvas.
         * @param  string  $canvasTitle  The title of the canvas.
         */
        // Notifies users about canvas-related actions

        private function notifyUsers(string $action, string $canvasTitle): void
        {
            $mailer = app()->make(Mailer::class);
            $users = $this->projectService->getUsersToNotify(session('currentProject'));

            $subject = $this->language->__("notification.board_{$action}");
            $mailer->setSubject($subject);

            $actual_link = CURRENT_URL;
            $message = sprintf(
                $this->language->__("email_notifications.canvas_{$action}_message"),
                session('userdata.name'),
                "<a href='".$actual_link."'>".$canvasTitle.'</a>'
            );
            $mailer->setHtml($message);

            $queue = app()->make(QueueRepo::class);
            $queue->queueMessageToUsers(
                $users,
                $message,
                $subject,
                session('currentProject')
            );
        }

        /**
         * Deletes a goal canvas item.
         *
         * @param  int  $id  The ID of the goal canvas item to be deleted.
         */
        // Deletes a goal canvas item

        public function deleteGoalCanvasItem(int $id): void
        {
            $this->goalRepository->delCanvasItem($id);
        }

        /**
         * Retrieves canvas data including comments.
         *
         * @param  array  $params  The parameters for retrieving the canvas data.
         * @return array|false The canvas data including comments, or false if the canvas item is not found.
         */
        // Retrieves canvas data including comments

        public function getCanvasData(array $params): array|false
        {
            if (isset($params['id'])) {
                if (isset($params['delComment'])) {
                    $this->deleteComment((int) $params['delComment']);
                }

                if (isset($params['removeMilestone'])) {
                    $this->removeMilestone($params['id']);
                }

                $canvasItem = $this->goalRepository->getSingleCanvasItem($params['id']);

                if ($canvasItem) {
                    $comments = $this->commentsRepo->getComments('goalcanvasitem', $canvasItem['id']);
                    $numComments = $this->commentsRepo->countComments('goalcanvascanvasitem', $canvasItem['id']);
                } else {
                    return false;
                }
            } else {
                $canvasItem = $this->getNewCanvasItem();
                $comments = [];
                $numComments = 0;
            }

            return [
                'canvasItem' => $canvasItem,
                'comments' => $comments,
                'numComments' => $numComments,
                'canvasIcon' => $this->goalRepository->getIcon(),
                'canvasTypes' => $this->goalRepository->getCanvasTypes(),
                'statusLabels' => $this->goalRepository->getStatusLabels(),
                'dataLabels' => $this->goalRepository->getDataLabels(),
            ];
        }

        /**
         * Deletes a comment.
         *
         * @param  int  $commentId  The ID of the comment to be deleted.
         */
        // Deletes a comment

        private function deleteComment(int $commentId): void
        {
            $this->commentsRepo->deleteComment($commentId);
        }

        // Removes a milestone from a canvas item

        /**
         * Removes the milestone from a canvas item.
         *
         * @param  int  $canvasItemId  The ID of the canvas item.
         */
        private function removeMilestone(int $canvasItemId): void
        {
            $this->goalRepository->patchCanvasItem($canvasItemId, ['milestoneId' => '']);
        }

        /**
         * Creates a new canvas item with default values
         *
         * @return array The newly created canvas item with default values
         */
        private function getNewCanvasItem(): array
        {
            return [
                'id' => '',
                'box' => 'goal',
                'title' => '',
                'description' => '',
                'status' => array_key_first($this->goalRepository->getStatusLabels()),
                'relates' => '',
                'startValue' => '',
                'currentValue' => '',
                'canvasId' => $_GET['canvasId'] ?? (int) session('currentGOALCanvas'),
                'endValue' => '',
                'kpi' => '',
                'startDate' => '',
                'endDate' => '',
                'setting' => '',
                'metricType' => '',
                'assignedTo' => '',
                'parent' => '',
            ];
        }

        /**
         * Adds a new comment to a canvas item.
         *
         * @param  array  $params  The parameters for adding a comment.
         *                         - text: The text of the comment.
         *                         - father: The parent comment ID.
         * @return int|false The ID of the newly added comment, or false if the comment text is empty.
         */
        public function addComment($params): int|false
        {
            $values = [
                'text' => $params['text'],
                'date' => date('Y-m-d H:i:s'),
                'userId' => session('userdata.id'),
                'moduleId' => $_GET['id'],
                'commentParent' => $params['father'],
            ];

            if ($params['text'] != '') {
                return $this->commentsRepo->addComment($values, 'goalcanvasitem');
            }

            return false;
        }

        /**
         * Updates or creates a canvas item.
         *
         * @param  array  $params  The parameters for updating or creating the canvas item.
         * @return array|false Returns an array with the canvas item, its ID, and a flag indicating if it is a new item. Returns false if the update or creation fails.
         */
        // Updates or creates a canvas item

        public function updateCanvasItem($params): array|false
        {
            $canvasItem = $this->prepareCanvasItemData($params);

            if (isset($params['itemId']) && ! empty($params['itemId'])) {
                $this->goalRepository->editCanvasItem($canvasItem);

                return ['canvasItem' => $canvasItem, 'id' => $params['itemId'], 'isNew' => false];
            } elseif (isset($params['title']) && ! empty($params['title'])) {
                $id = $this->goalRepository->addCanvasItem($canvasItem);

                return ['canvasItem' => $canvasItem, 'id' => $id, 'isNew' => true];
            }

            return false;
        }

        /**
         * Prepares canvas item data for insertion or update.
         *
         * @param  array  $params  The parameters for preparing the canvas item data.
         * @return array The prepared canvas item data.
         */

        // Prepares canvas item data for insertion or update

        private function prepareCanvasItemData($params): array
        {
            $canvasItem = [
                'box' => $params['box'],
                'author' => session('userdata.id'),
                'title' => $params['title'],
                'description' => $params['description'] ?? '',
                'status' => $params['status'] ?? '',
                'relates' => '',
                'startValue' => $params['startValue'],
                'currentValue' => $params['currentValue'],
                'endValue' => $params['endValue'],
                'canvasId' => $params['canvasId'],
                'parent' => $params['parent'] ?? null,
                'kpi' => $params['kpi'] ?? '',
                'startDate' => format(value: $params['startDate'] ?? '', fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime(),
                'endDate' => format(value: $params['endDate'] ?? '', fromFormat: FromFormat::UserDateEndOfDay)->isoDateTime(),
                'setting' => $params['setting'] ?? '',
                'metricType' => $params['metricType'],
                'assignedTo' => $params['assignedTo'] ?? '',
            ];

            if (isset($params['itemId'])) {
                $canvasItem['id'] = $params['itemId'];
                $canvasItem['itemId'] = $params['itemId'];
            }

            $this->handleMilestone($params, $canvasItem);

            return $canvasItem;
        }

        /**
         * Handles milestone creation or association for a canvas item.
         *
         * @param  array  $params  The parameters for milestone creation or association.
         * @param  array  $canvasItem  The canvas item to handle the milestone for.
         */
        // Handles milestone creation or association for a canvas item
        private function handleMilestone($params, &$canvasItem): void
        {
            if (isset($params['newMilestone']) && $params['newMilestone'] != '') {
                $milestoneParams = [
                    'headline' => $params['newMilestone'],
                    'tags' => '#ccc',
                    'editFrom' => dtHelper()->userNow()->formatDateForUser(),
                    'editTo' => dtHelper()->userNow()->addDays(7)->formatDateForUser(),
                    'dependentMilestone' => '',
                ];
                $id = $this->ticketService->quickAddMilestone($milestoneParams);
                if ($id !== false) {
                    $canvasItem['milestoneId'] = $id;
                }
            } elseif (isset($params['existingMilestone']) && $params['existingMilestone'] != '') {
                $canvasItem['milestoneId'] = $params['existingMilestone'];
            }
        }

        /**
         * Retrieves all canvas types.
         *
         * @return array An array containing all the canvas types.
         */
        // Retrieves all canvas types

        public function getCanvasTypes(): array
        {
            return $this->goalRepository->getCanvasTypes();
        }

        /**
         * Retrieves all status labels.
         *
         * @return array The array of status labels.
         */
        // Retrieves all status labels
        public function getStatusLabels(): array
        {
            return $this->goalRepository->getStatusLabels();
        }

        /**
         * Retrieves all data labels.
         *
         * @return array The array of data labels.
         */
        // Retrieves all data labels
        public function getDataLabels(): array
        {
            return $this->goalRepository->getDataLabels();
        }

        /**
         * Retrieves data for a specific canvas item including comments.
         *
         * @param  int  $id  The ID of the canvas item.
         * @return array An array containing the canvas item and its comments.
         */

        // Retrieves data for a specific canvas item including comments

        public function getCanvasItemData($id): array
        {
            return [
                'canvasItem' => $this->goalRepository->getSingleCanvasItem($id),
                'comments' => $this->commentsRepo->getComments('goalcanvasitem', $id),
            ];
        }

        /**
         * Returns a new canvas item template.
         *
         * @param  array  $params  The parameters for the canvas item.
         * @return array The new canvas item template.
         */
        public function getNewCanvasItemTemplate($params): array
        {
            return [
                'id' => '',
                'box' => $params['box'],
                'author' => session('userdata.id'),
                'title' => '',
                'description' => '',
                'status' => array_key_first($this->getStatusLabels()),
                'relates' => array_key_first($this->goalRepository->getRelatesLabels()),
                'startValue' => '',
                'currentValue' => '',
                'endValue' => '',
                'kpi' => '',
                'startDate' => '',
                'endDate' => '',
                'setting' => '',
                'metricType' => '',
                'assignedTo' => session('userdata.id'),
            ];
        }

        /**
         * Prepares the dates of a goal for API response
         *
         * @param  array  $goal  The goal array containing the dates
         * @return array The goal array with the dates converted to ISO 8601 Zulu format or null if invalid
         */
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
