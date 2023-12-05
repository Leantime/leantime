<?php

namespace Leantime\Domain\Cron\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Cron\Services\Cron;
    use PHPMailer\PHPMailer\Exception;
    use Symfony\Component\HttpFoundation\Response;
    use Illuminate\Console\Scheduling\Schedule;
    use Leantime\Core\Events;

    /**
     *
     */
    class Run extends Controller
    {
        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {
            //
        }

        /**
         * The Poor Man's Cron Endpoint
         *
         * @return Response
         * @throws Exception
         */
        public function run(): Response
        {
            error_log("Cron run started");

            Events::add_event_listener('leantime.core.httpkernel.terminate.request_terminated', function () {
                ignore_user_abort(true);

                // Removes script execution limit
                set_time_limit(0);

                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } else {
                    flush();
                }

                $schedule = tap(new Schedule, function ($schedule) {
                    \Leantime\Core\Events::dispatch_event('cron', ['schedule' => $schedule], 'leantime.core.consolekernel.schedule');
                });

                $schedule->dueEvents(app())
                    ->each(function (\Illuminate\Console\Scheduling\Event $event) {
                        error_log(sprintf(
                            'Running scheduled command: %s' . PHP_EOL,
                            $event->mutexName()
                        ));

                        $completion = 'Completed %s with status: %s' . PHP_EOL;

                        try {
                            $event->run(app());
                        } catch (\Throwable $e) {
                            error_log(sprintf($completion, $event->mutexName(), 1));
                            error_log($e);
                            return;
                        }

                        error_log(sprintf($completion, $event->mutexName(), $event->exitCode));
                    });
            });

            return tap(new Response, function ($response) {
                $response->headers->set('Content-Length', '0');
                $response->headers->set('Connection', 'close');
            });
        }
    }
}
