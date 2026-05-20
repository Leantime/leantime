<?php

namespace Leantime\Domain\Mcp\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;

class CommentWriter
{
    public function __construct(
        private CommentRepository $commentRepository,
        private ProjectService $projectsService,
        private LanguageCore $language,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function addComment(int $authorId, string $authorName, string $module, int $entityId, int $projectId, string $text, mixed $entity, int $parentId = 0, string $status = ''): string|false
    {
        $commentId = $this->commentRepository->addComment([
            'text' => $text,
            'date' => dtHelper()->dbNow()->formatDateTimeForDb(),
            'userId' => $authorId,
            'moduleId' => $entityId,
            'commentParent' => $parentId,
            'status' => $status,
        ], $module);

        if ($commentId === false) {
            return false;
        }

        $notification = app()->make(Notification::class);
        $notification->entity = [
            'id' => $commentId,
            'text' => $text,
            'moduleId' => $entityId,
        ];
        $notification->module = 'comments';
        $notification->action = 'commented';
        $notification->projectId = $projectId;
        $notification->authorId = $authorId;

        switch ($module) {
            case 'ticket':
                /** @var TicketModel $entity */
                $notification->subject = sprintf(
                    $this->language->__('email_notifications.new_comment_todo_with_type_subject'),
                    $this->language->__('label.'.strtolower($entity->type)),
                    $entity->id,
                    strip_tags((string) $entity->headline)
                );
                $notification->message = sprintf(
                    $this->language->__('email_notifications.new_comment_todo_with_type_message'),
                    $authorName,
                    $this->language->__('label.'.strtolower($entity->type)),
                    strip_tags((string) $entity->headline),
                    strip_tags($text)
                );
                $notification->url = [
                    'url' => BASE_URL.'#/tickets/showTicket/'.$entity->id,
                    'text' => $this->language->__('email_notifications.new_comment_todo_cta'),
                ];
                break;

            case 'project':
                $notification->subject = sprintf(
                    $this->language->__('email_notifications.new_comment_project_subject'),
                    $entityId,
                    strip_tags((string) ($entity['name'] ?? ''))
                );
                $notification->message = sprintf(
                    $this->language->__('email_notifications.new_comment_project_message'),
                    $authorName,
                    strip_tags((string) ($entity['name'] ?? ''))
                );
                $notification->url = [
                    'url' => BASE_URL.'/projects/showProject/'.$entityId,
                    'text' => $this->language->__('email_notifications.new_comment_project_cta'),
                ];
                break;

            default:
                $notification->subject = $this->language->__('email_notifications.new_comment_general_subject');
                $notification->message = sprintf($this->language->__('email_notifications.new_comment_general_message'), $authorName);
                $notification->url = false;
                break;
        }

        $this->projectsService->notifyProjectUsers($notification);

        return $commentId;
    }
}
