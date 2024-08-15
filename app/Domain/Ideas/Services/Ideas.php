<?php

namespace Leantime\Domain\Ideas\Services {

    use LDAP\Result;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeasRepository;
    use Leantime\core\Language;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;


    class Ideas
    {
        protected const CANVAS_NAME = '??';

        private IdeasRepository $ideasRepository;
        private ?Language $language;

        private ProjectService $projectService;

        public function __construct(IdeasRepository $ideasRepository, Language $language = null)
        {
            $this->ideasRepository = $ideasRepository;
            $this->language = $language;
            $this->projectService = app()->make(ProjectService::class);
        }


        public function pollForNewIdeas(): array
        {
            return $this->ideasRepository->getAllIdeas();
        }

        public function pollForUpdatedIdeas(): array
        {
            $ideas = $this->ideasRepository->getAllIdeas();

            foreach ($ideas as $key => $idea) {
                $ideas[$key]['id'] = $idea['id'] . '-' . $idea['modified'];
            }

            return $ideas;
        }

        public function getCurrentCanvasId(): int
        {
            $allCanvas = $this->ideasRepository->getAllCanvas(session("currentProject"));

            if (session()->exists("currentIdeaCanvas")) {
                $currentCanvasId = session("currentIdeaCanvas");
            } else {
                $currentCanvasId = -1;
                session(["currentIdeaCanvas" => ""]);
            }

            if (count($allCanvas) > 0 && session("currentIdeaCanvas") == '') {
                $currentCanvasId = $allCanvas[0]['id'];
                session(["currentIdeaCanvas" => $currentCanvasId]);
            }

            return $currentCanvasId;
        }

        public function createNewCanvas($values)
        {
            if (isset($_POST["newCanvas"]) === true) {
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
                $canvasTitle = $singleCanvas[0]["title"] ?? "";
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
    }
}
