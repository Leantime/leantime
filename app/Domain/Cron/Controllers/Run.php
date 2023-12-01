<?php

namespace Leantime\Domain\Cron\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Cron\Services\Cron;
    use PHPMailer\PHPMailer\Exception;
    use Illuminate\Support\Facades\Artisan;
    use Fiber;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Run extends Controller
    {
        private Cron $cronSvc;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(Cron $cronSvc)
        {
            $this->cronSvc = $cronSvc;
        }

        /**
         * The Poor Man's Cron Endpoint
         *
         * @return Response
         * @throws Exception
         */
        public function run(): Response
        {
            (new Fiber(function () {
                ignore_user_abort(true);

                // Removes script execution limit
                set_time_limit(0);

                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } else {
                    flush();
                }

                // Run the scheduler
                try {
                    Artisan::call('schedule:run');
                    exit(0);
                } catch (\Throwable $e) {
                    error_log($e);
                    exit(1);
                }
            }))->start();

            $response = new Response();

            // Close the connection with the client
            $response->headers->set('Content-Length', '0');
            $response->headers->set('Connection', 'close');

            return $response;
        }
    }
}
