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

    private array $supportedMessengers = ['slack', 'discord', 'mattermost', 'zulip', 'telegram'];

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
     * telegramWebhook
     *
     *
     * @api
     */
    private function telegramWebhook(NotificationModel $notification): bool
    {
        $telegramHookSerialized = $this->settingsRepo->getSetting("projectsettings.{$notification->projectId}.telegramHook");

        if ($telegramHookSerialized !== false && $telegramHookSerialized !== '') {
            $telegramHook = safe_unserialize($telegramHookSerialized, []);

            if (empty($telegramHook['telegramBotToken']) || empty($telegramHook['telegramChatId'])) {
                return false;
            }

            $text = $this->prepareTelegramMessage($notification);

            $data = [
                'chat_id' => $telegramHook['telegramChatId'],
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if (! empty($telegramHook['telegramTopicId']) && is_numeric($telegramHook['telegramTopicId']) && (int) $telegramHook['telegramTopicId'] > 0) {
                $data['message_thread_id'] = (int) $telegramHook['telegramTopicId'];
            }

            try {
                $response = $this->httpClient->post(
                    "https://api.telegram.org/bot{$telegramHook['telegramBotToken']}/sendMessage",
                    [
                        'allow_redirects' => OutboundUrlGuard::redirectOptions(),
                        'json' => $data,
                    ]
                );

                $resBody = json_decode((string) $response->getBody(), true);

                return is_array($resBody) && ! empty($resBody['ok']);
            } catch (\Throwable $e) {
                Log::warning('Telegram sendMessage failed', ['exception' => get_class($e)]);

                return false;
            }
        }

        return false;
    }

    /**
     * prepareTelegramMessage
     */
    private function prepareTelegramMessage(NotificationModel $notification): string
    {
        $headline = '';
        $status = '';
        $priority = '';
        $userId = 0;
        $userFirstname = '';
        $userLastname = '';
        $dateToFinish = '';

        if (isset($notification->entity)) {
            if (is_array($notification->entity)) {
                $headline = $notification->entity['headline'] ?? '';
                $status = $notification->entity['status'] ?? '';
                $priority = $notification->entity['priority'] ?? '';
                $userId = (int) ($notification->entity['userId'] ?? 0);
                $userFirstname = $notification->entity['userFirstname'] ?? $notification->entity['user_firstname'] ?? '';
                $userLastname = $notification->entity['userLastname'] ?? $notification->entity['user_lastname'] ?? '';
                $dateToFinish = $notification->entity['dateToFinish'] ?? $notification->entity['timelineDateToFinish'] ?? '';
            } elseif (is_object($notification->entity)) {
                $headline = $notification->entity->headline ?? '';
                $status = $notification->entity->status ?? '';
                $priority = $notification->entity->priority ?? '';
                $userId = (int) ($notification->entity->userId ?? 0);
                $userFirstname = $notification->entity->userFirstname ?? $notification->entity->user_firstname ?? '';
                $userLastname = $notification->entity->userLastname ?? $notification->entity->user_lastname ?? '';
                $dateToFinish = $notification->entity->dateToFinish ?? $notification->entity->timelineDateToFinish ?? '';
            }
        }

        // 1. Task Title
        $taskTitle = ! empty($headline) ? $headline : $notification->message;

        // 2. Status
        $statusName = 'N/A';
        if (! empty($status)) {
            try {
                $ticketService = app()->make(Tickets::class);
                $statusLabelsArray = $ticketService->getStatusLabels($notification->projectId);
                if (! empty($statusLabelsArray[$status]['name'])) {
                    $statusName = $statusLabelsArray[$status]['name'];
                } else {
                    $statusName = (string) $status;
                }
            } catch (\Throwable $e) {
                $statusName = (string) $status;
            }
        }

        // 3. Priority
        $priorityMap = [
            '1' => 'Low',
            '2' => 'Medium',
            '3' => 'High',
            '4' => 'Urgent',
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
        $priorityName = ! empty($priority) ? ($priorityMap[strtolower((string) $priority)] ?? (string) $priority) : 'Medium';

        // 4. Assigned To
        $assignedTo = trim("{$userFirstname} {$userLastname}");
        if (empty($assignedTo) && $userId > 0) {
            try {
                $userService = app()->make(\Leantime\Domain\Users\Services\Users::class);
                $user = $userService->getUser($userId);
                if (! empty($user)) {
                    $assignedTo = trim(($user['firstname'] ?? '').' '.($user['lastname'] ?? ''));
                }
            } catch (\Throwable $e) {
                // Keep default if user service unresolvable
            }
        }
        if (empty($assignedTo)) {
            $assignedTo = 'Unassigned';
        }

        // 5. Due Date
        $formattedDueDate = $this->language->__('label.none');
        if (! empty($dateToFinish) && $dateToFinish !== '0000-00-00 00:00:00' && $dateToFinish !== '0000-00-00') {
            try {
                $formattedDueDate = dtHelper()->parseDbDateTime($dateToFinish)->formatDateForUser();
            } catch (\Throwable $e) {
                $formattedDueDate = (string) $dateToFinish;
            }
        }

        // 6. Link
        $urlLink = is_array($notification->url) && ! empty($notification->url['url']) ? $notification->url['url'] : '';

        // Build clean Telegram message
        $lines = [];
        $lines[] = '📋 <b>'.e($this->projectName).'</b>';
        $lines[] = '';
        $lines[] = '📌 <b>'.$this->language->__('label.title').':</b> '.e($taskTitle);
        $lines[] = '🏷 <b>'.$this->language->__('label.todo_status').':</b> '.e($statusName);
        $lines[] = '⚡ <b>'.$this->language->__('label.priority').':</b> '.e($priorityName);
        $lines[] = '👤 <b>'.$this->language->__('label.assigned_to').':</b> '.e($assignedTo);
        $lines[] = '📅 <b>'.$this->language->__('label.due_date').':</b> '.e($formattedDueDate);

        if (! empty($urlLink)) {
            $hrefUrl = $urlLink;
            // Only rewrite localhost→127.0.0.1 in local/dev environments.
            // Self-hosted installs that legitimately use localhost as their base URL
            // must not be mutated in production.
            if (app()->isLocal()) {
                $hrefUrl = preg_replace('/^(https?:\/\/)localhost(?=[\/:]|$)/i', '${1}127.0.0.1', $hrefUrl);
            }
            $hrefUrl = str_replace('#', '%23', $hrefUrl);
            $lines[] = '';
            $lines[] = '👉 <a href="'.e($hrefUrl).'">'.$this->language->__('label.open_in_leantime').'</a>';
        }

        return implode("\n", $lines);
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
