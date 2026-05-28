<?php

namespace Leantime\Domain\Calendar\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Symfony\Component\HttpFoundation\Response;

class ExternalCal extends Controller
{
    private CalendarService $calendarService;

    private int $cacheTime = 60 * 30; // 30min

    /**
     * Initializes dependencies.
     */
    public function init(CalendarService $calendarService): void
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Serves an external calendar's iCal content, with session-based caching.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        $calId = $params['id'] ?? '';

        if (! session()->exists('calendarCache')) {
            session(['calendarCache' => []]);
        }

        $content = '';
        if (session()->exists('calendarCache.'.$calId) && session()->exists('calendarCache.'.$calId.'.lastUpdate') && session('calendarCache.'.$calId.'.lastUpdate') > time() - $this->cacheTime) {
            $content = session('calendarCache.'.$calId.'.content');
        } else {
            $cal = $this->calendarService->getExternalCalendar((int) $calId, session('userdata.id'));

            if (isset($cal['url'])) {
                try {
                    $content = $this->loadIcalUrl($cal['url']);
                    session(['calendarCache.'.$calId.'.lastUpdate' => time()]);
                    session(['calendarCache.'.$calId.'.content' => $content]);
                } catch (\Exception $e) {
                    $content = '';
                }
            }
        }

        return new Response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }

    /**
     * Loads an iCal URL via HTTP.
     *
     * @param  string  $url  The URL of the iCal to load
     * @return string The contents of the iCal
     */
    private function loadIcalUrl(string $url): string
    {
        $guzzle = app()->make(Client::class);
        $appSettings = app()->make(AppSettings::class);

        if (str_contains($url, 'webcal://')) {
            $url = str_replace('webcal://', 'https://', $url);
        }

        try {
            $response = $guzzle->request('GET', $url, [
                'headers' => [
                    'Accept' => 'text/calendar',
                    'User-Agent' => 'Leantime Calendar Integration v'.$appSettings->appVersion,
                ],
            ]);
        } catch (ClientException $e) {
            throw new \Exception('Guzzle problem: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() == '200') {
            return (string) $response->getBody();
        }

        throw new \Exception('Guzzle bad response code');
    }
}
