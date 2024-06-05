<?php

namespace Leantime\Domain\Notifications\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\HTMLToMarkdown\HtmlConverter;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 *
 */
class Messengers
{
    private Client $httpClient;
    private SettingRepository $settingsRepo;
    private LanguageCore $language;
    private array $supportedMessengers = array("slack", "discord", "mattermost", "zulip");
    private string $projectName = '';

    /**
     * __construct - get database connection
     *
     * @access public
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
     * @param NotificationModel $notification
     * @param $projectName
     * @param array|string $messengers
     * @return void
     */
    /**
     * @param NotificationModel $notification
     * @param $projectName
     * @param array|string      $messengers
     * @return void
     */
    public function sendNotificationToMessengers(NotificationModel $notification, $projectName, array|string $messengers = "all"): void
    {
        $this->projectName = $projectName;

        $messengersToSend = array();
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
            $this->{"" . $messenger . "Webhook"}($notification);
        }
    }

    /**
     * slackWebhook
     *
     * @access public
     */
    private function slackWebhook(NotificationModel $notification): bool
    {
        $slackWebhookURL = $this->settingsRepo->getSetting("projectsettings.{$notification->projectId}.slackWebhookURL");

        if ($slackWebhookURL !== "" && $slackWebhookURL !== false) {
            $message = $this->prepareMessage($notification);

            $data = array(
                'text'        => '',
                'attachments' => $message,
            );

            $data_string = json_encode($data);

            try {
                $this->httpClient->post($slackWebhookURL, [
                    'body' => $data_string,
                    'headers' => ['Content-Type' => 'application/json'],
                ]);

                return true;
            } catch (GuzzleException $e) {
                error_log($e);

                return false;
            }
        }

        return false;
    }

    /**
     * mattermostWebhook
     *
     * @access public
     */
    private function mattermostWebhook(NotificationModel $notification): bool
    {

        $mattermostWebhookURL = $this->settingsRepo->getSetting("projectsettings.{$notification->projectId}.mattermostWebhookURL");

        if ($mattermostWebhookURL !== '' && $mattermostWebhookURL !== false) {
            $message = $this->prepareMessage($notification);

            $data = array(
                'username' => "Leantime",
                'icon_url' => '',
                'text' => '',
                'attachments' => $message,
            );

            $data_string = json_encode($data);

            try {
                $this->httpClient->post($mattermostWebhookURL, [
                    'body' => $data_string,
                ]);

                return true;
            } catch (Exception $e) {
                error_log($e);

                return false;
            }
        }

        return false;
    }

    /**
     *  zulipWebhook
     *
     * @access public
     */
    private function zulipWebhook(NotificationModel $notification): bool
    {
        $zulipWebhookSerialized = $this->settingsRepo->getSetting("projectsettings.{$notification->projectId}.zulipHook");

        if ($zulipWebhookSerialized !== false && $zulipWebhookSerialized !== "") {
            $zulipWebhook = unserialize($zulipWebhookSerialized);

            $botEmail = $zulipWebhook['zulipEmail'];
            $botKey = $zulipWebhook['zulipBotKey'];
            $botURL = $zulipWebhook['zulipURL'] . "/api/v1/messages";

            $prepareChatMessage = "**Project: " . $this->projectName . "** \n\r" . $notification->message;
            if ($notification->url !== false) {
                $prepareChatMessage .= " " . $notification->url['url'] . "";
            }

            $data = array(
                'type' => 'stream',
                'to' => $zulipWebhook['zulipStream'],
                'topic' => $zulipWebhook['zulipTopic'],
                'content' => $prepareChatMessage,
            );

            $curlUrl = $botURL . '?' . http_build_query($data);

            $data_string = json_encode($data);

            try {
                $this->httpClient->post($curlUrl, [
                    'body' => $data_string,
                    'headers' => ['Content-Type' => 'application/json'],
                    'auth' => [
                        $botEmail,
                        $botKey,
                    ],
                ]);

                return true;
            } catch (GuzzleException $e) {
                error_log($e);

                return false;
            }
        }

        return false;
    }

    /**
     * mattermostWebhook
     *
     * @access public
     */
    public function discordWebhook(NotificationModel $notification): bool
    {
        $converter = false;

        for ($i = 1; 3 >= $i; $i++) {
            $discordWebhookURL = $this->settingsRepo->getSetting("projectsettings.{$notification->projectId}.discordWebhookURL{$i}");
            if ($discordWebhookURL !== '' && $discordWebhookURL !== false) {
                if (!$converter) {
                    $converter = new HtmlConverter();
                }
                $timestamp = date('c', strtotime('now'));
                $fields = [
                    // Additional data to be sent; e.g.:
                    //[
                    //  'name' => $subject,
                    //  'value' => $message,
                    //  'inline' => FALSE
                    //],
                ];
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
                            'title' => $notification->subject,
                            'type' => 'rich',
                            'description' => html_entity_decode($converter->convert($notification->message)),
                            'url' => $url_link,
                            'timestamp' => $timestamp,
                            'color' => hexdec('1b75bb'),
                            'footer' => [
                                'text' => 'Leantime',
                                'icon_url' => $url_link,
                            ],
                            'author' => [
                                'name' =>  $this->projectName,
                                'url' => $url_link,
                            ],
                            'fields' => $fields,
                        ],
                    ],

                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                try {
                    $this->httpClient->post($discordWebhookURL, [
                        'body' => $data_string,
                        'headers' => ['Content-Type' => 'application/json'],
                    ]);
                } catch (GuzzleException $e) {
                    error_log($e);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param NotificationModel $notification
     * @return array[]
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
        $statusLabelsArray = $ticketService->getStatusLabels($notification->projectId);
        $message = array(
            [
                'color'    => '#1b75bb',
                'fallback' => $notification->message,
                'pretext'  => $notification->message,
                'title' => $headline,
                'title_link' => $notification->url['url'],
                'fields'   => array(
                    [
                        'title' => $this->language->__("headlines.project_with_name") . ' ' . $this->projectName,
                        'value' => $this->language->__("label.todo_status") . ': ' . empty($statusLabelsArray[$status]) ? '' : $statusLabelsArray[$status]['name'],
                        'short' => false,
                    ],
                ),
            ],
        );

        return $message;
    }
}
