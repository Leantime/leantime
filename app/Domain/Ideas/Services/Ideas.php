<?php

namespace Leantime\Domain\Ideas\Services {

    use LDAP\Result;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeasRepository;
    use Leantime\Core\Language;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Ideas\Models\Ideas as ModelsIdeas;
    use Leantime\Domain\Notifications\Models\Notification as NotificationModel;


    class Ideas
    {
        protected const CANVAS_NAME = '??';

        private IdeasRepository $ideasRepository;
        private TicketService $ticketService;

        private ?Language $language;

        private ?ProjectService $projectService;

        private ?CommentRepository $commentRepository;



        public function __construct(IdeasRepository $ideasRepository, CommentRepository $commentRepository = null, Language $language = null)
        {
            $this->ideasRepository = $ideasRepository;
            $this->language = $language;
            $this->commentRepository = $commentRepository;
            $this->projectService = app()->make(ProjectService::class);
            $this->ticketService = app()->make(TicketService::class);
        }


        public function pollForNewIdeas(?int $projectId = null, ?int $board = null): array
        {
            $ideas = $this->ideasRepository->getAllIdeas($projectId, $board);

            foreach ($ideas as $key => $idea) {
                $ideas[$key] = $this->prepareDatesForApiResponse($idea);
            }

            return $ideas;
        }

        public function pollForUpdatedIdeas(?int $projectId = null, ?int $board = null): array
        {
            $ideas = $this->ideasRepository->getAllIdeas($projectId, $board);

            foreach ($ideas as $key => $idea) {
                $ideas[$key] = $this->prepareDatesForApiResponse($idea);
                $ideas[$key]['id'] = $idea['id'] . '-' . $idea['modified'];
            }

            return $ideas;
        }

        public function getCurrentCanvasId($allCanvas = null, $params = null): int
        {
            if (!empty($params) && isset($params["id"])) {
                $currentCanvasId = (int)$params["id"];
                session(["currentIdeaCanvas" => $currentCanvasId]);
                return $currentCanvasId;
            }

            if (session()->exists("currentIdeaCanvas")) {
                return session("currentIdeaCanvas");
            }

            if (!empty($allCanvas)) {
                $allCanvas = $this->ideasRepository->getAllCanvas(session("currentProject"));
            }

            if (count($allCanvas) > 0 && session("currentIdeaCanvas") == '') {
                $currentCanvasId = $allCanvas[0]['id'];
                session(["currentIdeaCanvas" => $currentCanvasId]);
                return $currentCanvasId;
            }

            return -1;
        }

        public function createNewCanvas($values)
        {
            // if (isset($_POST["newCanvas"]) === true) {
            if (isset($_POST['canvastitle']) === true) {
                $values = array("title" => $_POST['canvastitle'], "author" => session("userdata.id"), "projectId" => session("currentProject"));
                $currentCanvasId = $this->ideasRepository->addCanvas($values);

                // $this->tpl->setNotification($this->language->__('notification.idea_board_created'), 'success', "ideaboard_created");

                $mailer = app()->make(MailerCore::class);
                $mailer->setContext('idea_board_created');
                $users = $this->projectService->getUsersToNotify(session("currentProject"));

                $mailer->setSubject($this->language->__('email_notifications.idea_board_created_subject'));
                $message = sprintf($this->language->__('email_notifications.idea_board_created_message'), session("userdata.name"), "<a href='" . CURRENT_URL . "'>" . $values['title'] . "</a>.<br />");

                $mailer->setHtml($message);
                $mailer->sendMail($users, session("userdata.name"));

                // NEW Queuing messaging system
                $queue = app()->make(QueueRepository::class);
                $queue->queueMessageToUsers($users, $message, $this->language->__('email_notifications.idea_board_created_subject'), session("currentProject"));

                session(["currentIdeaCanvas" => $currentCanvasId]);
                // return Frontcontroller::redirect(BASE_URL . "/ideas/advancedBoards/");
                return [
                    'notification' => [
                        'message' => $this->language->__('notification.idea_board_created'),
                        'type' => 'success'
                    ],
                    'success' => true,
                    'canvasId' => $currentCanvasId,
                    'redirect_url' => BASE_URL . "/ideas/advancedBoards/"
                ];
            } else {
                return [
                    'notification' => [
                        'message' => $this->language->__('notification.please_enter_title'),
                        'type' => 'error'
                    ],
                    'success' => false

                ];
                // }
            }
        }

        public function editCanvas($values, $currentCanvasId)
        {
            if (isset($_POST['canvastitle']) === true) {
                $values = array("title" => $_POST['canvastitle'], "id" => $currentCanvasId);
                $currentCanvasId = $this->ideasRepository->updateCanvas($values);

                return [
                    'notification' => [
                        'message' => $this->language->__("notification.board_edited"),
                        "success",
                        "ideaboard_edited",
                        'type' => 'success'
                    ],
                    'success' => true,
                    'redirect_url' => BASE_URL . "/ideas/advancedBoards/"
                ];
            } else {
                return [
                    'notification' => [
                        'message' => $this->language->__('notification.please_enter_title'),
                        'type' => 'error'
                    ],
                    'success' => false

                ];
            }
        }

        // boardDialog

        public function prepareCanvasData($canvasId = null)
        {
            $allCanvas = $this->ideasRepository->getAllCanvas(session("currentProject"));
            $currentCanvasId = '';
            $canvasTitle = "";

            if ($canvasId !== null) {
                $currentCanvasId = (int)$canvasId;
                $singleCanvas = $this->ideasRepository->getSingleCanvas($currentCanvasId);
                $canvasTitle = $singleCanvas->title ?? "";
                session(["current" . strtoupper(static::CANVAS_NAME) . "Canvas" => $currentCanvasId]);
            }

            $users = $this->projectService->getUsersAssignedToProject(session("currentProject"));

            return [
                'allCanvas' => $allCanvas,
                'currentCanvasId' => $currentCanvasId,
                'canvasTitle' => $canvasTitle,
                'users' => $users,
            ];
        }


        // idea dialog get request

        public function processIdeaDialogGetRequest($params)
        {
            $result = [
                'canvasTypes' => $this->ideasRepository->canvasTypes,
                'milestones' => $this->ticketService->getAllMilestones([
                    "sprint" => '',
                    "type" => "milestone",
                    "currentProject" => session("currentProject")
                ])
            ];

            if (isset($params['id'])) {
                $result = array_merge($result, $this->processExistingIdea($params));
            } else {
                $result['canvasItem'] = $this->createNewCanvasItem($params['type'] ?? "idea");
                $result['comments'] = [];
            }

            return $result;
        }

        private function processExistingIdea($params)
        {
            $result = [];

            if (isset($params['delComment'])) {
                $this->deleteComment((int)$params['delComment']);
                $result['notification'] = [
                    'message' => $this->language->__('notifications.comment_deleted'),
                    'type' => 'success',
                    'key' => 'ideacomment_deleted'
                ];
            }

            if (isset($params['removeMilestone'])) {
                $this->removeMilestone($params['id']);
                $result['notification'] = [
                    'message' => $this->language->__('notifications.milestone_detached'),
                    'type' => 'success'
                ];
            }

            $canvasItem = $this->ideasRepository->getSingleCanvasItem($params['id']);
            $canvasItem->box = $canvasItem->box === "0" ? "idea" : $canvasItem->box;

            $result['canvasItem'] = $canvasItem;
            $result['comments'] = $this->commentRepository->getComments('idea', $canvasItem->id);
            $result['numComments'] = $this->commentRepository->countComments('ideas', $canvasItem->id);

            return $result;
        }

        private function createNewCanvasItem($type)
        {
            $new_idea = new ModelsIdeas();
            $new_idea->box = $type;
            $new_idea->status = 'idea';
            return $new_idea;

        }

        private function deleteComment($commentId)
        {
            $this->commentRepository->deleteComment($commentId);
        }

        private function removeMilestone($ideaId)
        {
            $this->ideasRepository->patchCanvasItem($ideaId, ["milestoneId" => '']);
        }

        // idea dialog post request

        public function processPostRequest($params)
        {

            if (isset($params['comment']) && !empty($params['text'])) {
                $result = $this->handleCommentSubmission($params);
                return $result;
            }

            if (isset($params['changeItem'])) {

                return $this->handleItemChange($params);
            }

            return [
                'canvasTypes' => $this->ideasRepository->canvasTypes,
                'canvasItem' => $this->ideasRepository->getSingleCanvasItem($_GET['id'])
            ];
        }

        private function handleCommentSubmission($params)
        {
            if (empty($params['text'])) {
                return ['notification' => ['message' => $this->language->__("notification.please_enter_text"), 'type' => 'error']];
            }

            $values = [
                'text' => $params['text'],
                'date' => date("Y-m-d H:i:s"),
                'userId' => session("userdata.id"),
                'moduleId' => (int)$_GET['id'],
                'commentParent' => $params['father'],
            ];

            $commentId = $this->commentRepository->addComment($values, 'idea');
            $this->notifyAboutNewComment($values, $commentId);

            return [
                'notification' => ['message' => $this->language->__('notifications.comment_create_success'), 'type' => 'success'],
                'redirect' => BASE_URL . "/ideas/ideaDialog/" . (int)$_GET['id']
            ];
        }

        private function handleItemChange($params)
        {
            if (isset($params['itemId']) && $params['itemId'] != '') {
                return $this->editExistingItem($params);
            } else {
                return $this->createNewItem($params);
            }
        }

        private function editExistingItem($params)
        {
            if (!isset($params['description'])) {
                return ['notification' => ['message' => $this->language->__("notification.please_enter_title"), 'type' => 'error']];
            }

            $canvasItem = $this->prepareCanvasItem($params);
            $this->handleMilestone($params, $canvasItem);
            $this->ideasRepository->editCanvasItem($canvasItem);

            $this->notifyAboutEditedIdea($canvasItem);

            return [
                'notification' => ['message' => $this->language->__('notification.idea_edited'), 'type' => 'success'],
                'redirect' => BASE_URL . "/ideas/ideaDialog/" . (int)$params['itemId']
            ];
        }

        private function createNewItem($params)
        {
            if (!isset($params['description'])) {
                return ['notification' => ['message' => $this->language->__("notification.please_enter_title"), 'type' => 'error']];
            }

            $canvasItem = $this->prepareCanvasItem($params);
            $id = $this->ideasRepository->addCanvasItem($canvasItem);
            $canvasItem["id"] = $id;

            $this->notifyAboutNewIdea($canvasItem);

            return [
                'notification' => ['message' => $this->language->__('notification.idea_created'), 'type' => 'success', 'key' => 'idea_created'],
                'redirect' => BASE_URL . "/ideas/ideaDialog/" . (int)$id
            ];
        }

        private function prepareCanvasItem($params)
        {
            return [
                "box" => $params['box'],
                "author" => session("userdata.id"),
                "description" => $params['description'],
                "status" => $params['status'],
                "assumptions" => "",
                "data" => $params['data'],
                "conclusion" => "",
                "tags" => $params['tags'] ?? "",
                "itemId" => $params['itemId'] ?? "",
                "canvasId" => (int)session("currentIdeaCanvas"),
                "milestoneId" => $params['milestoneId'] ?? "",
                "id" => $params['itemId'] ?? "",
            ];
        }

        private function handleMilestone($params, &$canvasItem)
        {
            if (isset($params['newMilestone']) && $params['newMilestone'] != '') {
                $milestone = [
                    'headline' => $params['newMilestone'],
                    'tags' => "#ccc",
                    'editFrom' => date("Y-m-d"),
                    'editTo' => date("Y-m-d", strtotime("+1 week"))
                ];
                $id = $this->ticketService->quickAddMilestone($milestone);
                if ($id !== false) {
                    $canvasItem['milestoneId'] = $id;
                }
            } elseif (isset($params['existingMilestone']) && $params['existingMilestone'] != '') {
                $canvasItem['milestoneId'] = $params['existingMilestone'];
            }
        }

        private function notifyAboutNewComment($values, $commentId)
        {
            $notification = $this->createNotification(
                $this->language->__('email_notifications.new_comment_idea_subject'),
                sprintf($this->language->__('email_notifications.new_comment_idea_message'), session("userdata.name")),
                BASE_URL . "/ideas/ideaDialog/" . (int)$_GET['id'],
                $this->language->__('email_notifications.new_comment_idea_cta'),
                $values,
                "comments"
            );
            $this->projectService->notifyProjectUsers($notification);
        }

        private function notifyAboutEditedIdea($canvasItem)
        {
            $notification = $this->createNotification(
                $this->language->__('email_notifications.idea_edited_subject'),
                sprintf($this->language->__('notification.idea_edited'), session("userdata.name"), $canvasItem['description']),
                BASE_URL . "/ideas/ideaDialog/" . (int)$canvasItem['id'],
                $this->language->__('email_notifications.idea_edited_cta'),
                $canvasItem,
                "ideas"
            );
            $this->projectService->notifyProjectUsers($notification);
        }

        private function notifyAboutNewIdea($canvasItem)
        {
            $notification = $this->createNotification(
                $this->language->__('email_notifications.idea_created_subject'),
                sprintf($this->language->__('email_notifications.idea_created_message'), session("userdata.name"), $canvasItem['description']),
                BASE_URL . "/ideas/ideaDialog/" . $canvasItem['id'],
                $this->language->__('email_notifications.idea_created_subject'),
                $canvasItem,
                "ideas"
            );
            $this->projectService->notifyProjectUsers($notification);
        }

        private function createNotification($subject, $message, $url, $cta, $entity, $module)
        {
            $notification = app()->make(NotificationModel::class);
            $notification->url = ["url" => $url, "text" => $cta];
            $notification->entity = $entity;
            $notification->module = $module;
            $notification->projectId = session("currentProject");
            $notification->subject = $subject;
            $notification->authorId = session("userdata.id");
            $notification->message = $message;
            return $notification;
        }


        // controller showboards



        public function handleShowBoardGetRequest($getParams)
        {
            $allCanvas = $this->ensureCanvasExists();
            $currentCanvasId = $this->getCurrentCanvasId($allCanvas, $getParams);

            return $this->prepareResponseData($currentCanvasId, $allCanvas);
        }

        public function handleShowBoardPostRequest($postParams)
        {
            if (isset($postParams["newCanvas"])) {
                return $this->createNewCanvas($postParams);
            }

            if (isset($postParams["editCanvas"])) {
                return $this->handleEditCanvas($postParams);
            }

            if (isset($postParams["searchCanvas"])) {
                return $this->handleSearchCanvas($postParams);
            }

            return $this->handleShowBoardGetRequest([]);
        }

        private function handleSearchCanvas($postParams)
        {
            $currentCanvasId = (int)$postParams["searchCanvas"];
            session(["currentIdeaCanvas" => $currentCanvasId]);
            return $this->prepareResponseData($currentCanvasId);
        }


        private function handleEditCanvas($postParams)
        {
            $currentCanvasId = session("currentIdeaCanvas");
            if (!isset($postParams['canvastitle']) || empty($postParams['canvastitle']) || $currentCanvasId <= 0) {
                return [
                    'notification' => [
                        'message' => $this->language->__('notification.please_enter_title'),
                        'type' => 'error'
                    ]
                ];
            }

            $values = ["title" => $postParams['canvastitle'], "id" => $currentCanvasId];
            $this->ideasRepository->updateCanvas($values);

            return [
                'notification' => [
                    'message' => $this->language->__("notification.board_edited"),
                    'type' => "success",
                    'key' => "idea_board_edited"
                ],
                'template' => 'canvas.boardDialog'
            ];
        }

        private function ensureCanvasExists()
        {
            $allCanvas = $this->ideasRepository->getAllCanvas(session("currentProject"));
            if (!$allCanvas || count($allCanvas) == 0) {
                $values = [
                    'title' => $this->language->__("label.board"),
                    'author' => session("userdata.id"),
                    'projectId' => session("currentProject"),
                ];
                $this->ideasRepository->addCanvas($values);
                $allCanvas = $this->ideasRepository->getAllCanvas(session("currentProject"));
            }
            return $allCanvas;
        }

        private function prepareResponseData($currentCanvasId, $allCanvas = null)
        {
            if ($allCanvas === null) {
                $allCanvas = $this->ideasRepository->getAllCanvas(session("currentProject"));
            }

            return [
                'currentCanvasId' => $currentCanvasId,
                'canvasLabels' => $this->ideasRepository->getCanvasLabels(),
                'allCanvas' => $allCanvas,
                'canvasItems' => $this->ideasRepository->getCanvasItemsById($currentCanvasId),
                'users' => $this->projectService->getUsersAssignedToProject(session("currentProject"))
            ];
        }

        private function prepareDatesForApiResponse($idea) {

            if(dtHelper()->isValidDateString($idea['created'])) {
                $idea['created'] = dtHelper()->parseDbDateTime($idea['created'])->toIso8601ZuluString();
            }else{
                $idea['created'] = null;
            }

            if(dtHelper()->isValidDateString($idea['modified'])) {
                $idea['modified'] = dtHelper()->parseDbDateTime($idea['modified'])->toIso8601ZuluString();
            }else{
                $idea['modified'] = null;
            }

            return $idea;

        }
    }
}
