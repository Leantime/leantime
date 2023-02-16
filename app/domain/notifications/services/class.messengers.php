<?php

namespace leantime\domain\services\notifications;

use GuzzleHttp\Client;
use League\HTMLToMarkdown\HtmlConverter;
use leantime\core;
use leantime\domain\repositories;
use leantime\domain\services;
use leantime\domain\models;

class messengers
{
    private Client $httpClient;
    private repositories\setting $settingsRepo;
    private core\language $language;
    private array $supportedMessengers = array("slack", "discord", "mattermost", "zulip");
    private $projectName = '';

    /**
     * __construct - get database connection
     *
     * @access public
     */
    public function __construct()
    {

        $this->httpClient = new Client();
        $this->language = core\language::getInstance();
        $this->settingsRepo = new repositories\setting();
    }

    public function sendNotificationToMessengers(models\notifications\notification $notification, $projectName, array|string $messengers = "all"): void
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
    private function slackWebhook(models\notifications\notification $notification)
    {

        $slackWebhookURL = $this->settingsRepo->getSetting("projectsettings." . $notification->projectId . ".slackWebhookURL");

        if ($slackWebhookURL !== "" && $slackWebhookURL !== false) {
            $message = $this->prepareMessage($notification);

            $data = array(
                'text'        => '',
                'attachments' => $message
            );

            $data_string = json_encode($data);

            try {
                $this->httpClient->post($slackWebhookURL, [
                    'body' => $data_string,
                    'headers' => [ 'Content-Type' => 'application/json' ]
                ]);

                return true;
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
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
    private function mattermostWebhook(models\notifications\notification $notification)
    {

        $mattermostWebhookURL = $this->settingsRepo->getSetting("projectsettings." . $notification->projectId . ".mattermostWebhookURL");

        if ($mattermostWebhookURL !== "" && $mattermostWebhookURL !== false) {
            $message = $this->prepareMessage($notification);


            $data = array(
                'username' => "Leantime",
                "icon_url" => '',
                'text' => '',
                'attachments' => $message
            );

            $data_string = json_encode($data);

            try {
                $this->httpClient->post($mattermostWebhookURL, [
                    'body' => $data_string
                ]);

                return true;
            } catch (\Exception $e) {
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
    private function zulipWebhook(models\notifications\notification $notification)
    {

        $zulipWebhookSerialized = $this->settingsRepo->getSetting("projectsettings." . $notification->projectId . ".zulipHook");

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
                "type" => "stream",
                "to" => $zulipWebhook['zulipStream'],
                "topic" => $zulipWebhook['zulipTopic'],
                'content' => $prepareChatMessage
            );

            $curlUrl = $botURL . '?' . http_build_query($data);

            $data_string = json_encode($data);

            try {
                $this->httpClient->post($curlUrl, [
                    'body' => $data_string,
                    'headers' => [ 'Content-Type' => 'application/json' ],
                    'auth' => [
                        $botEmail,
                        $botKey
                    ]
                ]);

                return true;
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
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
    public function discordWebhook(models\notifications\notification $notification)
    {



        $converter = false;

        for ($i = 1; 3 >= $i; $i++) {
            $discordWebhookURL = $this->settingsRepo->getSetting('projectsettings.' . $notification->projectId . '.discordWebhookURL' . $i);
            if ($discordWebhookURL !== "" && $discordWebhookURL !== false) {
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
                    'content' => 'Leantime' . ' - ' . $_SESSION["companysettings.sitename"],
                    'avatar_url' => 'https://s3-us-west-2.amazonaws.com/leantime-website/wp-content/uploads/2019/03/22224016/logoIcon.png',
                    'tts' => false,
                    'embeds' => [
                        [
                            'title' => $notification->subject,
                            'type' => 'rich',
                            'description' => $converter->convert($notification->message),
                            'url' => $url_link,
                            'timestamp' => $timestamp,
                            'color' => hexdec('1b75bb'),
                            'footer' => [
                                'text' => 'Leantime',
                                'icon_url' => $url_link,
                            ],
                            'author' => [
                                'name' =>  $this->projectName,
                                'url' => $url_link
                            ],
                            'fields' => $fields,
                        ]
                    ]

                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                try {
                    $response = $this->httpClient->post($discordWebhookURL, [
                        'body' => $data_string,
                        'headers' => [ 'Content-Type' => 'application/json' ]
                    ]);
                } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                    error_log($e);
                }
            }
        }

        return true;
    }

    public function prepareMessage(\leantime\domain\models\notifications\notification $notification): array
    {



        $prepareChatMessage = $notification->message;
        if ($notification->url !== false) {
            $prepareChatMessage .= " <" . $notification->url['url'] . "|" . $notification->url['text'] . ">";
        }

        $message = array([
            'fallback' => $notification->subject,
            'pretext'  => $notification->subject,
            'color'    => '#1b75bb',
            'fields'   => array(
                [
                    'title' => $this->language->__("headlines.project_with_name") . " " . $this->projectName,
                    'value' => $prepareChatMessage,
                    'short' => false
                ]
            )
        ]);

        return $message;
    }
}
