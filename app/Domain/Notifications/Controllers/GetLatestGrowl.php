<?php

/**
 * GetLatestGrowl Controller - returns the latest pending growl notification.
 */

namespace Leantime\Domain\Notifications\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\UI\Template;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GetLatestGrowl extends Controller
{
    private NotificationService $notificationService;

    /**
     * init - inject dependencies
     */
    public function init(NotificationService $notificationService): void
    {
        $this->notificationService = $notificationService;
    }

    /**
     * get - returns the latest pending growl notification as JSON, or false when none.
     */
    public function get(array $params = []): Response
    {
        $payload = $this->notificationService->consumeFlashNotification();

        // The page shows this as HTML: clean it like rich text (see Template::growlScript()).
        if ($payload !== null) {
            $payload['notification'] = $this->tpl->escapeMinimal((string) $payload['notification']);
            $payload['type'] = Template::growlStyle((string) $payload['type']);
        }

        return new JsonResponse($payload !== null ? json_encode($payload) : false);
    }
}
