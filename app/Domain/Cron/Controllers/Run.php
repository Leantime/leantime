<?php

namespace Leantime\Domain\Cron\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Console\ConsoleKernel;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Events\EventDispatcher;
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\HttpFoundation\Response;

class Run extends Controller
{
    private Environment $config;

    /**
     * Initializes dependencies.
     */
    public function init(Environment $config): void
    {
        $this->config = $config;
    }

    /**
     * The Poor Man's Cron Endpoint.
     *
     * @param  array  $params  Request parameters
     *
     * @throws Exception
     */
    public function get(array $params): Response
    {
        EventDispatcher::add_event_listener('leantime.core.http.httpkernel.terminate.request_terminated', function () {
            ignore_user_abort(true);

            set_time_limit(0);

            $output = new \Symfony\Component\Console\Output\BufferedOutput;
            $consoleKernel = app()->make(ConsoleKernel::class);
            $result = $consoleKernel->call('schedule:run', [], $output);

            register_shutdown_function(function () use ($output) {
                if ($this->config->debug) {
                    Log::info('Cron Schedule Output: '.$output->fetch());
                }
            });

            return $result;
        });

        return tap(new Response, function ($response) {
            $response->headers->set('Content-Length', '0');
            $response->headers->set('Connection', 'close');
        });
    }
}
