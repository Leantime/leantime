<?php

namespace Unit\app\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Notifications\Services\Messengers;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Unit\TestCase;

class MessengersServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function makeNotification(int $projectId = 1, string $message = 'Test notification'): NotificationModel
    {
        $notification = new NotificationModel();
        $notification->projectId = $projectId;
        $notification->message = $message;
        $notification->url = ['url' => 'https://example.com/ticket/123'];

        return $notification;
    }

    public function test_send_notification_to_messengers_skips_telegram_when_unconfigured(): void
    {
        $posted = false;
        $client = $this->make(Client::class, [
            'post' => function () use (&$posted) {
                $posted = true;

                return new Response(200);
            },
        ]);

        $settingRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => false,
        ]);

        $language = $this->make(LanguageCore::class);

        $messengers = new Messengers($client, $settingRepo, $language);
        $messengers->sendNotificationToMessengers($this->makeNotification(), 'Test Project', ['telegram']);

        $this->assertFalse($posted);
    }

    public function test_telegram_webhook_returns_false_when_hook_missing_required_fields(): void
    {
        $posted = false;
        $client = $this->make(Client::class, [
            'post' => function () use (&$posted) {
                $posted = true;

                return new Response(200);
            },
        ]);

        $settingRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => serialize([
                'telegramBotToken' => '12345:ABC',
                'telegramChatId' => '',
                'telegramTopicId' => '',
            ]),
        ]);

        $language = $this->make(LanguageCore::class);

        $messengers = new Messengers($client, $settingRepo, $language);
        $messengers->sendNotificationToMessengers($this->makeNotification(), 'Test Project', ['telegram']);

        $this->assertFalse($posted);
    }

    public function test_telegram_webhook_posts_to_api_and_succeeds(): void
    {
        $capturedUrl = null;
        $capturedOptions = null;

        $client = $this->make(Client::class, [
            'post' => function ($url, $options) use (&$capturedUrl, &$capturedOptions) {
                $capturedUrl = $url;
                $capturedOptions = $options;

                return new Response(200, [], json_encode(['ok' => true]));
            },
        ]);

        $settingRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => serialize([
                'telegramBotToken' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
                'telegramChatId' => '987654321',
                'telegramTopicId' => '',
            ]),
        ]);

        $language = $this->make(LanguageCore::class);

        $messengers = new Messengers($client, $settingRepo, $language);
        $messengers->sendNotificationToMessengers($this->makeNotification(1, 'Task created'), 'Acme Project', ['telegram']);

        $this->assertSame('https://api.telegram.org/bot123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11/sendMessage', $capturedUrl);
        $this->assertArrayHasKey('json', $capturedOptions);
        $this->assertSame('987654321', $capturedOptions['json']['chat_id']);
        $this->assertStringContainsString('<b>Acme Project</b>', $capturedOptions['json']['text']);
        $this->assertStringContainsString('Task created', $capturedOptions['json']['text']);
        $this->assertStringContainsString('https://example.com/ticket/123', $capturedOptions['json']['text']);
        $this->assertSame('HTML', $capturedOptions['json']['parse_mode']);
        $this->assertArrayNotHasKey('message_thread_id', $capturedOptions['json']);
    }

    public function test_telegram_webhook_includes_message_thread_id_when_topic_id_provided(): void
    {
        $capturedOptions = null;

        $client = $this->make(Client::class, [
            'post' => function ($url, $options) use (&$capturedOptions) {
                $capturedOptions = $options;

                return new Response(200, [], json_encode(['ok' => true]));
            },
        ]);

        $settingRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => serialize([
                'telegramBotToken' => '123456:ABC-DEF',
                'telegramChatId' => '-1001234567890',
                'telegramTopicId' => '42',
            ]),
        ]);

        $language = $this->make(LanguageCore::class);

        $messengers = new Messengers($client, $settingRepo, $language);
        $messengers->sendNotificationToMessengers($this->makeNotification(), 'Test Project', ['telegram']);

        $this->assertArrayHasKey('json', $capturedOptions);
        $this->assertSame('-1001234567890', $capturedOptions['json']['chat_id']);
        $this->assertSame(42, $capturedOptions['json']['message_thread_id']);
    }

    public function test_telegram_webhook_catches_guzzle_exception_and_returns_false(): void
    {
        $client = $this->make(Client::class, [
            'post' => function () {
                throw new RequestException('API connection error', new Request('POST', 'test'));
            },
        ]);

        $settingRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => serialize([
                'telegramBotToken' => '123456:ABC-DEF',
                'telegramChatId' => '987654321',
                'telegramTopicId' => '',
            ]),
        ]);

        $language = $this->make(LanguageCore::class);

        $messengers = new Messengers($client, $settingRepo, $language);

        $reflectedMethod = new \ReflectionMethod($messengers, 'telegramWebhook');
        $reflectedMethod->setAccessible(true);
        $result = $reflectedMethod->invoke($messengers, $this->makeNotification());

        $this->assertFalse($result);
    }
}
