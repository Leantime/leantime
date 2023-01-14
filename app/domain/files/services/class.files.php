<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\models\notifications\notification;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class files
    {

        private $fileRepository;
        private $projectService;
        private $language;


        public function __construct()
        {

            $this->fileRepository = new repositories\files();
            $this->projectService = new services\projects();
            $this->language = core\language::getInstance();

        }

        public function getFilesByModule($module = '', $entityId = null, $userId = null)
        {
            return $this->fileRepository->getFilesByModule($module, $entityId, $userId);
        }


        public function uploadFile($file, $module, $entityId, $entity) {

            if (isset($file['file'])) {

                if($this->fileRepository->upload($file, $module, $entityId)){

                    switch($module) {
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

                    $notification = new models\notifications\notification();
                    $notification->url = array(
                        "url" => CURRENT_URL,
                        "text" => $linkLabel
                    );

                    $notification->entity = $file;
                    $notification->module = $module;
                    $notification->projectId = $_SESSION['currentProject'];
                    $notification->subject = $subject;
                    $notification->authorId = $_SESSION['userdata']['id'];
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    return true;

                }else{

                    return false;

                }
            }

            return false;
        }

        public function deleteFile($fileId) {

           return $this->fileRepository->deleteFile($fileId);

        }

    }

}
