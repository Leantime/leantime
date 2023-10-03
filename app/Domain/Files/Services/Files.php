<?php

namespace Leantime\Domain\Files\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Notifications\Models\Notification;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;

    /**
     *
     */

    /**
     *
     */
    class Files
    {
        private $fileRepository;
        private $projectService;
        private LanguageCore $language;

        /**
         * @param FileRepository $fileRepository
         * @param ProjectService $projectService
         * @param LanguageCore   $language
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
         * @param $module
         * @param $entityId
         * @param $userId
         * @return array|false
         */
        /**
         * @param $module
         * @param $entityId
         * @param $userId
         * @return array|false
         */
        public function getFilesByModule($module = '', $entityId = null, $userId = null)
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
         */
        /**
         * @param $file
         * @param $module
         * @param $entityId
         * @param $entity
         * @return boolean
         * @throws BindingResolutionException
         */
        public function uploadFile($file, $module, $entityId, $entity)
        {

            if (isset($file['file'])) {
                if ($this->fileRepository->upload($file, $module, $entityId)) {
                    switch ($module) {
                        case "ticket":
                            $subject = sprintf($this->language->__("email_notifications.new_file_todo_subject"), $entity->id, $entity->headline);
                            $message = sprintf($this->language->__("email_notifications.new_file_todo_subject"), $_SESSION["userdata"]["name"], $entity->headline);
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
                    $notification->projectId = $_SESSION['currentProject'];
                    $notification->subject = $subject;
                    $notification->authorId = $_SESSION['userdata']['id'];
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
         */
        /**
         * @param $fileId
         * @return boolean
         */
        public function deleteFile($fileId)
        {
            return $this->fileRepository->deleteFile($fileId);
        }
    }

}
