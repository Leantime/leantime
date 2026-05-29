<?php

namespace Leantime\Domain\Cron\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Cron\Services\Cron as CronService;
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\HttpFoundation\Response;

class Run extends Controller
{
    private CronService $cronService;

    /**
     * Initializes dependencies.
     */
    public function init(CronService $cronService): void
    {
        $this->cronService = $cronService;
    }

    /**
     * The Poor Man's Cron Endpoint.
     *
     * Registers a terminate listener that runs the scheduled tasks after the response is sent,
     * then immediately returns an empty response so the client connection can close.
     *
     * @param  array  $params  Request parameters
     *
     * @throws Exception
     */
    public function get(array $params): Response
    {
        EventDispatcher::add_event_listener(
            'leantime.core.http.httpkernel.terminate.request_terminated',
            fn () => $this->cronService->runScheduledTasks()
        );

        return tap(new Response, function ($response) {
            $response->headers->set('Content-Length', '0');
            $response->headers->set('Connection', 'close');
        });
    }
}
