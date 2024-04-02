<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace Leantime\Domain\Calendar\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Container\BindingResolutionException;
use JetBrains\PhpStorm\NoReturn;
use Leantime\Core\AppSettings;
use Leantime\Core\Controller;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class ExternalCal extends Controller
{
    private CalendarRepository $calendarRepo;

    private int $cacheTime = 60 * 30; // 30min

    /**
     * init - initialize private variables
     *
     * @param CalendarRepository $calendarRepo
     *
     * @return void
     */
    public function init(CalendarRepository $calendarRepo): void
    {
        $this->calendarRepo = $calendarRepo;
    }

    /**
     * run - display template and edit data
     *
     * @access public
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function run(): void
    {

        $calId = $_GET['id'];

        if (!isset($_SESSION['calendarCache'])) {
            $_SESSION['calendarCache'] = [];
        }

        $content = '';
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

    /**
     * Load an iCal URL.
     *
     * @param string $url The URL of the iCal to load.
     *
     * @return string The contents of the iCal.
     *
     * @throws BindingResolutionException
     */
    private function loadIcalUrl(string $url): string
    {
        $guzzle = app()->make(Client::class);

        $appSettings = app()->make(AppSettings::class);

        if(str_contains($url, "webcal://")) {
            $url = str_replace("webcal://", "https://", $url);
        }

        try {
            $response = $guzzle->request('GET', $url, [
                'headers' => [
                    'Accept' => 'text/calendar',
                    // GitHub needs a user agent.
                    'User-Agent' => 'Leantime Calendar Integration v' . $appSettings->appVersion,
                ],
            ]);
        } catch (ClientException $e) {
            throw new \Exception('Guzzle problem: ' . $e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() == '200') {
            return (string) $response->getBody();
        } else {
            throw new \Exception('Guzzle bad response code');
        }
    }
}
