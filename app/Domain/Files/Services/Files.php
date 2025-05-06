<?php

namespace Leantime\Domain\Files\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Infrastructure\i18n\Language as LanguageCore;

/**
 * @api
 */
class Files
{
    private FileRepository $fileRepository;

    private ProjectService $projectService;

    private LanguageCore $language;

    public function __construct(
        FileRepository $fileRepository,
        ProjectService $projectService,
        LanguageCore $language
    ) {

        $this->fileRepository = $fileRepository;
        $this->projectService = $projectService;
        $this->language = $language;
    }

    /**
     * @api
     */
    public function getFilesByModule(string $module = '', $entityId = null, $userId = null): false|array
    {
        return $this->fileRepository->getFilesByModule($module, $entityId, $userId);
    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    public function uploadFile($file, $module, $entityId, $entity = null): array|bool
    {

        if (isset($file['file'])) {
            if ($return = $this->fileRepository->upload($file, $module, $entityId)) {

                switch ($module) {
                    case 'ticket':
                        $subject = sprintf($this->language->__('email_notifications.new_file_todo_subject'), $entity->id, strip_tags($entity->headline));
                        $message = sprintf($this->language->__('email_notifications.new_file_todo_subject'), session('userdata.name'), strip_tags($entity->headline));
                        $linkLabel = $this->language->__('email_notifications.new_file_todo_cta');
                        break;
                    default:
                        $subject = $this->language->__('email_notifications.new_file_general_subject');
                        $message = $this->language->__('email_notifications.new_file_general_message');
                        $linkLabel = $this->language->__('email_notifications.new_file_general_cta');
                        break;
                }

                if ($module !== 'user') {
                    $notification = app()->make(Notification::class);
                    $notification->url = [
                        'url' => CURRENT_URL,
                        'text' => $linkLabel,
                    ];

                    $notification->entity = $file;
                    $notification->module = $module;
                    $notification->projectId = session('currentProject');
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id');
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);
                }

                return $return;

            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @api
     */
    public function deleteFile($fileId): bool
    {
        return $this->fileRepository->deleteFile($fileId);
    }
}
