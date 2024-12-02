<?php

namespace Leantime\Domain\Comments\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Support\Collection;
    use Leantime\Core\Exceptions\AuthException;
    use Leantime\Core\Exceptions\MissingParameterException;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Comments\Models\Comment;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Notifications\Models\Notification;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets;

    /**
     * @api
     */
    class Comments
    {
        private CommentRepository $commentRepository;

        private ProjectService $projectService;

        private LanguageCore $language;

        private Tickets $ticketService;

        public function __construct(
            CommentRepository $commentRepository,
            ProjectService $projectService,
            Tickets $ticketService,
            LanguageCore $language
        ) {
            $this->commentRepository = $commentRepository;
            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->language = $language;
        }

        /**
         * @api
         */
        public function getComments(string $module, int $moduleId, int $commentOrder = 0, int $parent = 0): false|Collection
        {

            if ($module == '') {
                throw new MissingParameterException('Module is required parameter');
            }

            if ($moduleId == '' || $moduleId == 0) {
                throw new MissingParameterException('Module Id is required parameter and greater than 0');
            }

            //Todo check that user is allowed to load data from this entity

            //Comes back as flat list
            $comments = $this->commentRepository->getComments($module, $moduleId, $parent, $commentOrder);

            /* @var array<comments> */
            $commentsArray = [];

            //Create an array of comment parents
            foreach ($comments as $comment) {
                $commentObject = app()->make(Comment::class);
                $commentObject->mapRootDbArray($comment);

                if ($comment['commentParent'] == $parent && ! isset($commentsArray[$commentObject->id])) {
                    $commentsArray[$commentObject->id] = $commentObject;
                }
            }

            //Now add replies
            foreach ($comments as $comment) {
                $commentObject = app()->make(Comment::class);
                $commentObject->mapRepliesDbArray($comment);
                if (
                    $commentObject->mapRepliesDbArray($comment) !== false
                    && isset($commentsArray[$commentObject->commentParent])
                ) {
                    $commentsArray[$commentObject->commentParent]->replies[] = $commentObject;
                }
            }

            return collect($commentsArray);
        }

        /**
         * @param  $entity
         *
         * @throws BindingResolutionException
         */
        public function addComment($values, $module, $entityId): bool
        {

            if (! Auth::userIsAtLeast(Roles::$commenter)) {
                throw new AuthException('User is not authorized to add comments');
            }

            if (isset($values['text']) && $values['text'] != '' && isset($values['father']) && isset($module) && isset($entityId) && isset($entity)) {
                $mapper = [
                    'text' => $values['text'],
                    'date' => dtHelper()->dbNow()->formatDateTimeForDb(),
                    'userId' => (session('userdata.id')),
                    'moduleId' => $entityId,
                    'commentParent' => ($values['father']),
                    'status' => $values['status'] ?? '',
                ];

                $comment = $this->commentRepository->addComment($mapper, $module);

                if ($comment) {
                    $mapper['id'] = $comment;

                    $currentUrl = CURRENT_URL;

                    switch ($module) {
                        case 'ticket':
                            $entity = $this->ticketService->getTicket($entityId);
                            $subject = sprintf($this->language->__('email_notifications.new_comment_todo_with_type_subject'), $this->language->__('label.'.strtolower($entity->type)), $entity->id, $entity->headline);
                            $message = sprintf($this->language->__('email_notifications.new_comment_todo_with_type_message'), session('userdata.name'), $this->language->__('label.'.strtolower($entity->type)), $entity->headline, $values['text']);
                            $linkLabel = $this->language->__('email_notifications.new_comment_todo_cta');
                            $currentUrl = BASE_URL.'#/tickets/showTicket/'.$entity->id;
                            break;
                        case 'project':
                            $entity = $this->projectService->getProject($entityId);
                            $subject = sprintf($this->language->__('email_notifications.new_comment_project_subject'), $entityId, $entity['name']);
                            $message = sprintf($this->language->__('email_notifications.new_comment_project_message'), session('userdata.name'), $entity['name']);
                            $linkLabel = $this->language->__('email_notifications.new_comment_project_cta');
                            break;
                        default:
                            $subject = $this->language->__('email_notifications.new_comment_general_subject');
                            $message = sprintf($this->language->__('email_notifications.new_comment_general_message'), session('userdata.name'));
                            $linkLabel = $this->language->__('email_notifications.new_comment_general_cta');
                            break;
                    }

                    $notification = app()->make(Notification::class);
                    $notification->url = [
                        'url' => $currentUrl.'&projectId='.session('currentProject'),
                        'text' => $linkLabel,
                    ];

                    $notification->entity = $mapper;
                    $notification->module = 'comments';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id');
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    return true;
                }
            }

            return false;
        }

        /**
         * @throws BindingResolutionException
         *
         * @api
         */
        public function editComment($values, $id): bool
        {

            if ($id == 0) {
                throw new MissingParameterException('Comment Id is required');
            }

            $comment = (object) $this->getComment($id);

            if ($comment->userId !== session('userdata.id') && ! Auth::userIsAtLeast(Roles::$manager)) {
                throw new AuthException('User is not authorized to edit comment');
            }

            return $this->commentRepository->editComment($values['text'], $id);
        }

        /**
         * @api
         */
        public function deleteComment(int $commentId): bool
        {

            if ($commentId == 0) {
                throw new MissingParameterException('Comment Id is required');
            }

            $comment = (object) $this->getComment($commentId);

            if ($comment->userId !== session('userdata.id') && ! Auth::userIsAtLeast(Roles::$manager)) {
                throw new AuthException('User is not authorized to delete comment');
            }

            return $this->commentRepository->deleteComment($commentId);
        }

        /**
         * @param  ?int  $projectId  Project ID
         * @param  ?int  $moduleId  Id of the entity to pull comments from
         * @return array
         *
         * @api
         */
        public function pollComments(?int $projectId = null, ?int $moduleId = null): array|false
        {

            $comments = $this->commentRepository->getAllAccountComments($projectId, $moduleId);

            foreach ($comments as $key => $comment) {
                if (dtHelper()->isValidDateString($comment['date'])) {
                    $comments[$key]['date'] = dtHelper()->parseDbDateTime($comment['date'])->toIso8601ZuluString();
                } else {
                    $comments[$key]['date'] = null;
                }
            }

            return $comments;
        }

        public function getComment($id): array
        {

            return $this->commentRepository->getComment($id);
        }
    }
}
