<?php

namespace Leantime\Domain\Notifications\Listeners;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Notifications\Services\Notifications;

/**
 *
 */
class NotifyProjectUsers
{

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
                'authorId' => session("userdata.id"),
            );
        }

        $notificationService->addNotifications($notifications);
    }
}
