<?php

namespace Leantime\Domain\Cron\Controllers {

    use Illuminate\Support\Facades\Log;
    use Leantime\Core\Configuration\Environment;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Events\EventDispatcher;
    use PHPMailer\PHPMailer\Exception;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Run extends Controller
    {
        private Environment $config;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(Environment $config)
        {
            $this->config = $config;
        }

        /**
         * The Poor Man's Cron Endpoint
         *
         * @return Response
         * @throws Exception
         */
        public function run(): Response
        {
            if (! $this->config->poorMansCron) {
                Log::info("Poor Mans Cron is turned off");
                return new Response();
            }

            EventDispatcher::add_event_listener('leantime.core.http.httpkernel.terminate.request_terminated', function () {
                ignore_user_abort(true);

                // Removes script execution limit
                set_time_limit(0);

                $output = new \Symfony\Component\Console\Output\BufferedOutput();

                    register_shutdown_function(function () use ($output) {
                        if ($this->config->debug) {

                            Log::info("Command Output: " . $output->fetch());
                            Log::info("Cron run finished");

                        }
                    });

                /** @return never **/
                (new \Leantime\Core\Console\ConsoleKernel())->call('schedule:run', [], $output);


            });

            return tap(new Response(), function ($response) {
                $response->headers->set('Content-Length', '0');
                $response->headers->set('Connection', 'close');
            });
        }
    }
}
