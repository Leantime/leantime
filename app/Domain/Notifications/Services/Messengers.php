<?php

namespace Leantime\Domain\Notifications\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\OutboundUrlGuard;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Services\Tickets;

class Messengers
{
    private Client $httpClient;

    private SettingRepository $settingsRepo;

    private LanguageCore $language;

    private array $supportedMessengers = ['slack', 'discord', 'mattermost', 'zulip'];

    private string $projectName = '';

    /**
     * __construct - get database connection
     *
     *
     * @api
     */
    public function __construct(
        Client $httpClient,
        SettingRepository $settingsRepo,
        LanguageCore $language
    ) {
        $this->httpClient = $httpClient;
        $this->settingsRepo = $settingsRepo;
        $this->language = $language;
    }

    /**
     * @api
     */
    /**
     * @api
     */
    public function sendNotificationToMessengers(NotificationModel $notification, $projectName, array|string $messengers = 'all'): void
    {
        $this->projectName = $projectName ?? 'a Leantime project';

        $messengersToSend = [];
        if (is_string($messengers) && $messengers == 'all') {
            $messengersToSend = $this->supportedMessengers;
        } elseif (is_array($messengers)) {
            foreach ($messengers as $messenger) {
                if (in_array($messenger, $this->supportedMessengers)) {
                    $messengersToSend[] = $messenger;
                }
            }
        }

        foreach ($messengersToSend as $messenger) {
            $this->{''.$messenger.'Webhook'}($notification);
        }
    }

    /**
     * slackWebhook
     *
     *
     * @api
     */
    private function slackWebhook(NotificationModel $notification): bool
    {
        $slackWebhookURL = $this->settingsRepo->getSetting("projectsettings.{$notification->projectId}.slackWebhookURL");

        if ($slackWebhookURL !== '' && $slackWebhookURL !== false) {
            $message = $this->prepareMessage($notification);

            $data = [
                'text' => '',
                'attachments' => $message,
            ];

            $data_string = json_encode($data);

            try {
                if (! OutboundUrlGuard::isAllowedUrl($slackWebhookURL)) {
                    Log::warning('Blocked Slack webhook to disallowed URL (SSRF guard)', ['host' => parse_url($slackWebhookURL, PHP_URL_HOST)]);

                    return false;
                }

                $this->httpClient->post($slackWebhookURL, [
                    'allow_redirects' => OutboundUrlGuard::redirectOptions(),
                    'body' => $data_string,
                    'headers' => ['Content-Type' => 'application/json'],
                ]);

                return true;
            } catch (\Throwable $e) {
                report($e);

                return false;
            }
        }

        return false;
    }

    /**
     * mattermostWebhook
     *
     *
     * @api
     */
    private function mattermostWebhook(NotificationModel $notification): bool
    {

        $mattermostWebhookURL = $this->settingsRepo->getSetting("projectsettings.{$notification->projectId}.mattermostWebhookURL");

        if ($mattermostWebhookURL !== '' && $mattermostWebhookURL !== false) {
            $message = $this->prepareMessage($notification);

            $data = [
                'username' => 'Leantime',
                'icon_url' => '',
                'text' => '',
                'attachments' => $message,
            ];

            $data_string = json_encode($data);

            try {
                if (! OutboundUrlGuard::isAllowedUrl($mattermostWebhookURL)) {
                    Log::warning('Blocked Mattermost webhook to disallowed URL (SSRF guard)', ['host' => parse_url($mattermostWebhookURL, PHP_URL_HOST)]);

                    return false;
                }

                $this->httpClient->post($mattermostWebhookURL, [
                    'allow_redirects' => OutboundUrlGuard::redirectOptions(),
                    'body' => $data_string,
                ]);

                return true;
            } catch (Exception $e) {
                report($e);

                return false;
            }
        }

        return false;
    }

    /**
     *  zulipWebhook
     *
     *
     * @api
     */
    private function zulipWebhook(NotificationModel $notification): bool
    {
        $zulipWebhookSerialized = $this->settingsRepo->getSetting("projectsettings.{$notification->projectId}.zulipHook");

        if ($zulipWebhookSerialized !== false && $zulipWebhookSerialized !== '') {
            $zulipWebhook = safe_unserialize($zulipWebhookSerialized, []);

            $botEmail = $zulipWebhook['zulipEmail'];
            $botKey = $zulipWebhook['zulipBotKey'];
            $botURL = $zulipWebhook['zulipURL'].'/api/v1/messages';

            $prepareChatMessage = '**Project: '.$this->projectName."** \n\r".$notification->message;
            if ($notification->url !== false) {
                $prepareChatMessage .= ' '.$notification->url['url'].'';
            }

            $data = [
                'type' => 'stream',
                'to' => $zulipWebhook['zulipStream'],
                'topic' => $zulipWebhook['zulipTopic'],
                'content' => $prepareChatMessage,
            ];

            $curlUrl = $botURL.'?'.http_build_query($data);

            $data_string = json_encode($data);

            try {
                if (! OutboundUrlGuard::isAllowedUrl($curlUrl)) {
                    Log::warning('Blocked Zulip webhook to disallowed URL (SSRF guard)', ['host' => parse_url($curlUrl, PHP_URL_HOST)]);

                    return false;
                }

                $this->httpClient->post($curlUrl, [
                    'allow_redirects' => OutboundUrlGuard::redirectOptions(),
                    'body' => $data_string,
                    'headers' => ['Content-Type' => 'application/json'],
                    'auth' => [
                        $botEmail,
                        $botKey,
                    ],
                ]);

                return true;
            } catch (\Throwable $e) {
                report($e);

                return false;
            }
        }

        return false;
    }

    /**
     * mattermostWebhook
     *
     *
     * @api
     */
    public function discordWebhook(NotificationModel $notification): bool
    {
        $ticketService = app()->make(Tickets::class);

        for ($i = 1; $i <= 3; $i++) {
            $discordWebhookURL = $this->settingsRepo->getSetting("projectsettings.{$notification->projectId}.discordWebhookURL{$i}");
            if ($discordWebhookURL !== '' && $discordWebhookURL !== false) {
                $fields = [
                    [
                        'name' => $this->language->__('label.project'),
                        'value' => $this->projectName,
                        'inline' => true,
                    ],
                ];

                $statusLabelsArray = $ticketService->getStatusLabels($notification->projectId);
                if (! empty($notification->entity->status) && ! empty($statusLabelsArray[$notification->entity->status])) {
                    $fields[] = [
                        'name' => $this->language->__('label.todo_status'),
                        'value' => $statusLabelsArray[$notification->entity->status]['name'],
                        'inline' => true,
                    ];
                }

                $url_link = (
                    empty($notification->url['url'])
                    ? ''
                    : $notification->url['url']
                );

                // For details on the JSON layout: https://birdie0.github.io/discord-webhooks-guide/index.html
                $data_string = json_encode([
                    'avatar_url' => 'https://s3-us-west-2.amazonaws.com/leantime-website/wp-content/uploads/2019/03/22224016/logoIcon.png',
                    'tts' => false,
                    'embeds' => [
                        [
                            'color' => hexdec('1b75bb'),
                            'title' => $notification->message,
                            'url' => $url_link,
                            'timestamp' => date('c', strtotime('now')),
                            'fields' => $fields,
                        ],
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                try {
                    if (! OutboundUrlGuard::isAllowedUrl($discordWebhookURL)) {
                        Log::warning('Blocked Discord webhook to disallowed URL (SSRF guard)', ['host' => parse_url($discordWebhookURL, PHP_URL_HOST)]);

                        return false;
                    }

                    $this->httpClient->post($discordWebhookURL, [
                        'allow_redirects' => OutboundUrlGuard::redirectOptions(),
                        'body' => $data_string,
                        'headers' => ['Content-Type' => 'application/json'],
                    ]);
                } catch (\Throwable $e) {
                    report($e);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array[]
     *
     * @api
     */
    public function prepareMessage(NotificationModel $notification): array
    {
        $ticketService = app()->make(Tickets::class);
        if (is_array($notification->entity)) {
            $headline = $notification->entity['headline'] ?? '';
            $status = $notification->entity['status'] ?? '';
        } else {
            $headline = $notification->entity->headline;
            $status = $notification->entity->status;
        }

        $fields = [
            'title' => $this->language->__('headlines.project_with_name').' '.$this->projectName,
            'short' => false,
        ];

        $statusLabelsArray = $ticketService->getStatusLabels($notification->projectId);
        if (! empty($statusLabelsArray[$status])) {
            $fields['value'] = $this->language->__('label.todo_status').': '.$statusLabelsArray[$status]['name'];
        }

        $message = [
            [
                'color' => '#006d9f',
                'fallback' => $notification->message,
                'pretext' => $notification->message,
                'title' => $headline,
                'title_link' => $notification->url['url'],
                'fields' => $fields,
            ],
        ];

        return $message;
    }
}
