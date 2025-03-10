<?php

namespace Leantime\Domain\Cron\Controllers {

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
         * init - initialize private variables
         */
        public function init(Environment $config)
        {
            $this->config = $config;
        }

        /**
         * The Poor Man's Cron Endpoint
         *
         * @throws Exception
         */
        public function run(): Response
        {

            EventDispatcher::addEventListener('leantime.core.http.httpkernel.terminate.request_terminated', function () {
                ignore_user_abort(true);

                // Removes script execution limit
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
}
