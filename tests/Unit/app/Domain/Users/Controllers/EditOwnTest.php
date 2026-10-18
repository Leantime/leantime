<?php

namespace Unit\app\Domain\Users\Controllers;

use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\UI\Template;
use Leantime\Domain\Users\Controllers\EditOwn;
use Leantime\Domain\Users\Exceptions\WebhookSettingNotSavedException;
use Leantime\Domain\Users\Services\Users as UserService;
use Unit\TestCase;

/**
 * The notifications and webhooks tabs of the own-profile form: a rejected
 * webhook URL or an unsaved webhook setting must surface as an error, never as
 * the "saved" confirmation.
 */
class EditOwnTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Notifications the controller set, as [message, type] pairs.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private array $notifications = [];

    private array $originalPost = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalPost = $_POST;
        $this->notifications = [];
        session(['userdata.id' => 7, 'formTokenName' => 'formToken', 'formTokenValue' => 'expected-token']);
    }

    protected function tearDown(): void
    {
        $_POST = $this->originalPost;

        parent::tearDown();
    }

    /**
     * Submits a tab of the own-profile form through the real controller with the
     * given (stubbed) user service and returns the redirect target.
     *
     * @param  array<string, string>  $post  The tab's fields, without the form token.
     */
    private function submitTab(UserService $userService, array $post): string
    {
        app()->instance(UserService::class, $userService);

        $_POST = ['formToken' => 'expected-token'] + $post;

        $tpl = $this->make(Template::class, [
            'setNotification' => function (string $message, string $type) {
                $this->notifications[] = [$message, $type];
            },
        ]);
        $language = $this->make(LanguageCore::class, ['__' => fn ($key) => $key]);

        $response = (new EditOwn($this->make(IncomingRequest::class), $tpl, $language))->post();

        return (string) $response->headers->get('Location');
    }

    private function submitWebhooksTab(UserService $userService): void
    {
        $location = $this->submitTab($userService, [
            'savewebhooks' => '1',
            'webhookUrl' => 'http://insecure.example.com/hook',
            'webhookEnabled' => '1',
        ]);

        $this->assertStringEndsWith('/users/editOwn#webhooks', $location);
    }

    public function test_rejected_webhook_url_shows_an_error_and_no_success(): void
    {
        $this->submitWebhooksTab($this->make(UserService::class, [
            'saveOwnWebhookSettings' => function () {
                throw ValidationException::withMessages(['webhookUrl' => ['notification.invalid_webhook_url']]);
            },
        ]));

        $this->assertSame([['notification.invalid_webhook_url', 'error']], $this->notifications);
    }

    public function test_unsaved_webhook_setting_shows_a_generic_error_and_no_success(): void
    {
        $this->submitWebhooksTab($this->make(UserService::class, [
            'saveOwnWebhookSettings' => function () {
                throw new WebhookSettingNotSavedException;
            },
        ]));

        $this->assertSame([['short_notifications.not_saved', 'error']], $this->notifications);
    }

    public function test_unexpected_failures_are_not_swallowed_into_a_notification(): void
    {
        // Only the known outcomes are handled; anything else (e.g. a DB error) must reach the exception handler.
        $this->expectExceptionObject(new \RuntimeException('database went away'));

        $this->submitWebhooksTab($this->make(UserService::class, [
            'saveOwnWebhookSettings' => function () {
                throw new \RuntimeException('database went away');
            },
        ]));
    }

    public function test_accepted_webhook_shows_the_success_confirmation(): void
    {
        $this->submitWebhooksTab($this->make(UserService::class, [
            'saveOwnWebhookSettings' => fn () => null,
        ]));

        $this->assertSame([['notifications.changed_profile_settings_successfully', 'success']], $this->notifications);
    }

    public function test_notification_preferences_are_saved_without_the_webhook(): void
    {
        $webhookSaves = 0;

        $location = $this->submitTab($this->make(UserService::class, [
            'saveOwnNotificationPreferences' => fn () => null,
            'saveOwnWebhookSettings' => function () use (&$webhookSaves) {
                $webhookSaves++;
            },
        ]), ['savenotifications' => '1', 'notifications' => 'on']);

        $this->assertStringEndsWith('/users/editOwn#notifications', $location);
        $this->assertSame(0, $webhookSaves);
        $this->assertSame([['notifications.changed_profile_settings_successfully', 'success']], $this->notifications);
    }
}
