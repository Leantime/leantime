<?php

namespace leantime\domain\events {

    use leantime\domain\services\notifications;

    class addNotification
    {
        public function handle($payload)
        {

            $notificationService = new notifications();

            $notifications = array();
            foreach($payload['users'] as $user){
                $notifications[] = array(
                    'userId' => $user['id'],
                    'type' => $payload['type'],
                    'module' => $payload['module'],
                    'moduleId' => $payload['moduleId'],
                    'message' => $payload['message'],
                    'datetime' => date("Y-m-d H:i:s"),
                    'url' => $payload['url'],
                    'authorId' => $_SESSION['userdata']['id'],
                );
            }


            $notificationService->addNotifications($notifications);
        }
    }

    \leantime\core\events::add_event_listener("domain.services.projects.notifyProjectUsers", new addNotification());

}
