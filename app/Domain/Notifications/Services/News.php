<?php

namespace Leantime\Domain\Notifications\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Infrastructure\Database\Db as DbCore;
use Leantime\Infrastructure\i18n\Language as LanguageCore;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationRepository;
use Leantime\Domain\Setting\Services\Setting;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * @api
 */
class News
{
    private DbCore $db;

    private NotificationRepository $notificationsRepo;

    private UserRepository $userRepository;

    private LanguageCore $language;

    private Setting $settingService;

    /**
     * __construct - get database connection
     *
     *
     * @api
     */
    public function __construct(
        DbCore $db,
        NotificationRepository $notificationsRepo,
        UserRepository $userRepository,
        LanguageCore $language,
        Setting $settingService
    ) {
        $this->db = $db;
        $this->notificationsRepo = $notificationsRepo;
        $this->userRepository = $userRepository;
        $this->language = $language;
        $this->settingService = $settingService;
    }

    public function getLatest(int $userId): false|\SimpleXMLElement
    {

        try {
            $rss = $this->getFeed();
        } catch (\Exception $e) {
            Log::warning('Could not connect to news server.');
            Log::warning($e);

            return false;
        }

        $latestGuid = (string) $rss?->channel?->item[0]?->guid;
        $this->settingService->saveSetting('usersettings.'.$userId.'.lastNewsGuid', strval($latestGuid));

        // Todo: check last article the user read
        // Only load rss feed once a day
        return $rss;

    }

    public function hasNews(int $userId): bool
    {

        try {
            $rss = $this->getFeed();
        } catch (\Exception $e) {
            Log::warning('Could not connect to news server.');
            Log::warning($e);

            return false;
        }

        $latestGuid = (string) $rss?->channel?->item[0]?->guid;

        $lastNewsGuid = $this->settingService->getSetting('usersettings.'.$userId.'.lastNewsGuid');

        if ($lastNewsGuid === false) {
            return true;
        }

        if ($lastNewsGuid !== $latestGuid) {
            return true;
        }

        return false;

    }

    /**
     * getFeed - Fetches the feed from a remote URL and returns the contents as a SimpleXMLElement object
     *
     * @return \SimpleXMLElement - The parsed XML content as a SimpleXMLElement object
     *
     * @throws \Exception - If the simplexml_load_string function doesn't exist
     *
     * @api
     */
    public function getFeed()
    {

        $client = new \GuzzleHttp\Client;
        $response = $client->request('GET', 'https://leantime.io/category/leantime-updates/feature-updates/feed/', [
            'headers' => ['Accept' => 'application/xml'],
            'timeout' => 20,
        ])->getBody()->getContents();

        if (function_exists('simplexml_load_string')) {
            $responseXml = simplexml_load_string($response);
        } else {
            throw new \Exception('Simple XML extension is not installed');
        }

        return $responseXml;
    }
}
