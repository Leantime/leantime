<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Symfony\Component\HttpFoundation\Response;

class Notifications extends Controller
{
    public NotificationService $notificationService;

    /**
     * init - initialize private variables
     */
    public function init(NotificationService $notificationService): void
    {
        $this->notificationService = $notificationService;
    }

    /**
     * get - handle get requests
     */
    public function get(array $params): Response
    {
        $notifications = $this->notificationService->getAllNotifications($params['userId'], $params['read']);

        return $this->tpl->displayJson($notifications);
    }

    /**
     * post - handle post requests
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function post(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * put - handle put requests
     */
    public function patch(array $params): Response
    {
        if (isset($params['action']) && $params['action'] == 'read') {
            $this->notificationService->markNotificationRead($params['id'], session('userdata.id'));
        }

        return new Response;
    }

    /**
     * delete - handle delete requests
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}
