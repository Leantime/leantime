<?php

namespace Leantime\Domain\Comments\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Infrastructure\i18n\Language as LanguageCore;

/**
 * @api
 */
class Comments
{
    private CommentRepository $commentRepository;

    private ProjectService $projectService;

    private LanguageCore $language;

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
     * @api
     */
    /**
     * @api
     */
    public function getComments($module, $entityId, int $commentOrder = 0): false|array
    {
        return $this->commentRepository->getComments($module, $entityId, 0, $commentOrder);
    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    public function addComment($values, $module, $entityId, $entity): bool
    {
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
                        $subject = sprintf($this->language->__('email_notifications.new_comment_todo_with_type_subject'), $this->language->__('label.'.strtolower($entity->type)), $entity->id, strip_tags($entity->headline));
                        $message = sprintf($this->language->__('email_notifications.new_comment_todo_with_type_message'), session('userdata.name'), $this->language->__('label.'.strtolower($entity->type)), strip_tags($entity->headline), strip_tags($values['text']));
                        $linkLabel = $this->language->__('email_notifications.new_comment_todo_cta');
                        $currentUrl = BASE_URL.'#/tickets/showTicket/'.$entity->id;
                        break;
                    case 'project':
                        $subject = sprintf($this->language->__('email_notifications.new_comment_project_subject'), $entityId, strip_tags($entity['name']));
                        $message = sprintf($this->language->__('email_notifications.new_comment_project_message'), session('userdata.name'), strip_tags($entity['name']));
                        $linkLabel = $this->language->__('email_notifications.new_comment_project_cta');
                        break;
                    default:
                        $subject = $this->language->__('email_notifications.new_comment_general_subject');
                        $message = sprintf($this->language->__('email_notifications.new_comment_general_message'), session('userdata.name'));
                        $linkLabel = $this->language->__('email_notifications.new_comment_general_cta');
                        break;
                }

                $notification = app()->make(Notification::class);

                $urlQueryParameter = str_contains($currentUrl, '?') ? '&' : '?';
                $notification->url = [
                    'url' => $currentUrl.$urlQueryParameter.'projectId='.session('currentProject'),
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
        return $this->commentRepository->editComment($values['text'], $id);
    }

    /**
     * @api
     */
    public function deleteComment($commentId): bool
    {

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
}
