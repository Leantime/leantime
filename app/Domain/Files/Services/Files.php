<?php

namespace Leantime\Domain\Files\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Notifications\Models\Notification;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;

    /**
     *
     *
     * @api
     */
    class Files
    {
        private FileRepository $fileRepository;
        private ProjectService $projectService;
        private LanguageCore $language;

        /**
         * @param FileRepository $fileRepository
         * @param ProjectService $projectService
         * @param LanguageCore   $language
         *
     */
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
         * @param string   $module
         * @param $entityId
         * @param $userId
         * @return array|false
         *
     * @api
     */
        public function getFilesByModule(string $module = '', $entityId = null, $userId = null): false|array
        {
            return $this->fileRepository->getFilesByModule($module, $entityId, $userId);
        }

        /**
         * @param $file
         * @param $module
         * @param $entityId
         * @param $entity
         * @return bool
         * @throws BindingResolutionException
         *
     * @api
     */
        public function uploadFile($file, $module, $entityId, $entity): bool
        {

            if (isset($file['file'])) {
                if ($this->fileRepository->upload($file, $module, $entityId)) {
                    switch ($module) {
                        case "ticket":
                            $subject = sprintf($this->language->__("email_notifications.new_file_todo_subject"), $entity->id, $entity->headline);
                            $message = sprintf($this->language->__("email_notifications.new_file_todo_subject"), session("userdata.name"), $entity->headline);
                            $linkLabel = $this->language->__("email_notifications.new_file_todo_cta");
                            break;
                        default:
                            $subject = $this->language->__("email_notifications.new_file_general_subject");
                            $message = $this->language->__("email_notifications.new_file_general_message");
                            $linkLabel = $this->language->__("email_notifications.new_file_general_cta");
                            break;
                    }

                    $notification = app()->make(Notification::class);
                    $notification->url = array(
                        "url" => CURRENT_URL,
                        "text" => $linkLabel,
                    );

                    $notification->entity = $file;
                    $notification->module = $module;
                    $notification->projectId = session("currentProject");
                    $notification->subject = $subject;
                    $notification->authorId = session("userdata.id");
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    return true;
                } else {
                    return false;
                }
            }

            return false;
        }

        /**
         * @param $fileId
         * @return bool
         *
     * @api
     */
        public function deleteFile($fileId): bool
        {
            return $this->fileRepository->deleteFile($fileId);
        }

    }

}
