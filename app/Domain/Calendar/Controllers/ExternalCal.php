<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace Leantime\Domain\Calendar\Controllers {

    use GuzzleHttp\Client;
    use Leantime\Core\AppSettings;
    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class ExternalCal extends Controller
    {
        private CalendarRepository $calendarRepo;

        private $cacheTime = 60 * 30; // 30min

        /**
         * init - initialize private variables
         */
        public function init(CalendarRepository $calendarRepo)
        {
            $this->calendarRepo = $calendarRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $calId = $_GET['id'];

            if (!isset($_SESSION['calendarCache'])) {
                $_SESSION['calendarCache'] = [];
            }

            if (isset($_SESSION['calendarCache'][$calId]) && $_SESSION['calendarCache'][$calId]['lastUpdate'] > time() - $this->cacheTime) {
                $content = $_SESSION['calendarCache'][$calId]["content"];
            } else {
                $cal = $this->calendarRepo->getExternalCalendar($calId, $_SESSION['userdata']['id']);

                if (isset($cal["url"])) {
                    $content = $this->loadIcalUrl($cal["url"]);
                    $_SESSION['calendarCache'][$calId]["lastUpdate"] = time();
                    $_SESSION['calendarCache'][$calId]["content"] = $content;
                }
            }

            header('Content-type: text/calendar; charset=utf-8');
            //header('Content-disposition: attachment;filename="external.ics"');

            echo $content;

            exit();
        }

        private function loadIcalUrl($url) {

            $guzzle = app()->make(Client::class);

            $appSettings = app()->make(AppSettings::class);
            try {
                $response = $guzzle->request('GET', $url, [
                    'headers' => [
                        'Accept' => 'text/calendar',
                        'User-Agent' => 'Leantime Calendar Integration v'.$appSettings->appVersion, //<-- Github wants a user agent.
                    ]
                ]);
            } catch (ClientException $e) {
                throw new \Exception('Guzzle problem: ', Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));
            }


            if ($response->getStatusCode() == '200') {
                return (string) $response->getBody();
            } else {
                throw new \Exception('Guzzle bad response code');
            }
        }

    }

}
