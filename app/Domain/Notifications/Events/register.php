<?php

namespace Leantime\Domain\Notifications\Events;

use Leantime\Domain\Notifications\Services\Notifications;

class AddNotification
{
    public function handle($payload)
    {

        $notificationService = app()->make(Notifications::class);

        $notifications = array();

        foreach ($payload['users'] as $user) {
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

\Leantime\Core\Events::add_event_listener("domain.services.projects.notifyProjectUsers", new addNotification());
