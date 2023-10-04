<?php

namespace Leantime\Domain\Notifications\Events;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Events;
use Leantime\Domain\Notifications\Services\Notifications;

/**
 *
 */
class AddNotification
{
    /**
     * @param $payload
     * @return void
     * @throws BindingResolutionException
     */
    /**
     * @param $payload
     * @return void
     * @throws BindingResolutionException
     */
    public function handle($payload): void
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

Events::add_event_listener("domain.services.projects.notifyProjectUsers", new addNotification());
