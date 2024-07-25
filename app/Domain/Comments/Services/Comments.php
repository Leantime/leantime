<?php

namespace Leantime\Domain\Comments\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Notifications\Models\Notification;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;


    /**
     *
     */
    class Comments
    {
        private CommentRepository $commentRepository;
        private ProjectService $projectService;
        private LanguageCore $language;

        /**
         * @param CommentRepository $commentRepository
         * @param ProjectService    $projectService
         * @param LanguageCore      $language
         */
        public function __construct(
            CommentRepository $commentRepository,
            ProjectService $projectService,
            LanguageCore $language
        ) {
            $this->commentRepository = $commentRepository;
            $this->projectService = $projectService;
            $this->language = $language;
        }

        /**
         * @param $module
         * @param $entityId
         * @param $commentOrder
         * @return array|false
         */
        /**
         * @param $module
         * @param $entityId
         * @param int      $commentOrder
         * @return array|false
         */
        public function getComments($module, $entityId, int $commentOrder = 0): false|array
        {
            return $this->commentRepository->getComments($module, $entityId, 0, $commentOrder);
        }

        /**
         * @param $values
         * @param $module
         * @param $entityId
         * @param $entity
         * @return bool
         * @throws BindingResolutionException
         */
        public function addComment($values, $module, $entityId, $entity): bool
        {
            if (isset($values['text']) && $values['text'] != '' && isset($values['father']) && isset($module) &&  isset($entityId) &&  isset($entity)) {
                $mapper = array(
                    'text' => $values['text'],
                    'date' => $values["date"] ?? dtHelper()->dbNow()->formatDateTimeForDb(),
                    'userId' => (session("userdata.id")),
                    'moduleId' => $entityId,
                    'commentParent' => ($values['father']),
                    'status' => $values['status'] ?? '',
                );

                $comment = $this->commentRepository->addComment($mapper, $module);

                if ($comment) {
                    $mapper['id'] = $comment;

                    $currentUrl = CURRENT_URL;

                    switch ($module) {
                        case "ticket":
                            $subject = sprintf($this->language->__("email_notifications.new_comment_todo_with_type_subject"), $this->language->__("label." . strtolower($entity->type)), $entity->id, $entity->headline);
                            $message = sprintf($this->language->__("email_notifications.new_comment_todo_with_type_message"), session("userdata.name"), $this->language->__("label." . strtolower($entity->type)), $entity->headline, $values['text']);
                            $linkLabel = $this->language->__("email_notifications.new_comment_todo_cta");
                            $currentUrl = BASE_URL . "#/tickets/showTicket/" . $entity->id;
                            break;
                        case "project":
                            $subject = sprintf($this->language->__("email_notifications.new_comment_project_subject"), $entityId, $entity['name']);
                            $message = sprintf($this->language->__("email_notifications.new_comment_project_message"), session("userdata.name"), $entity['name']);
                            $linkLabel = $this->language->__("email_notifications.new_comment_project_cta");
                            break;
                        default:
                            $subject = $this->language->__("email_notifications.new_comment_general_subject");
                            $message = sprintf($this->language->__("email_notifications.new_comment_general_message"), session("userdata.name"));
                            $linkLabel = $this->language->__("email_notifications.new_comment_general_cta");
                            break;
                    }

                    $notification = app()->make(Notification::class);
                    $notification->url = array(
                        "url" => $currentUrl . "&projectId=" . session("currentProject"),
                        "text" => $linkLabel,
                    );

                    $notification->entity = $mapper;
                    $notification->module = "comments";
                    $notification->projectId = session("currentProject");
                    $notification->subject = $subject;
                    $notification->authorId = session("userdata.id");
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    return true;
                }
            }

            return false;
        }

        /**
         * @param $values
         * @param $id
         * @return bool
         * @throws BindingResolutionException
         */
        public function editComment($values, $id): bool
        {
            return $this->commentRepository->editComment($values['text'], $id);
        }

        /**
         * @param $commentId
         * @return bool
         */
        public function deleteComment($commentId): bool
        {

            return $this->commentRepository->deleteComment($commentId);
        }

        public function pollComments(): array | false
        {
            return $this->commentRepository->getAllAccountComments();
        }
    }
}
