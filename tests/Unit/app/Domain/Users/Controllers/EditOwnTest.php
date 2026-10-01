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
 * The notifications tab of the own-profile form: a rejected webhook URL or an
 * unsaved webhook setting must surface as an error, never as the "saved"
 * confirmation.
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
     * Submits the notifications tab through the real controller with the given
     * (stubbed) user service.
     */
    private function submitNotificationsTab(UserService $userService): void
    {
        app()->instance(UserService::class, $userService);

        $_POST = [
            'formToken' => 'expected-token',
            'savenotifications' => '1',
            'webhookUrl' => 'http://insecure.example.com/hook',
            'webhookEnabled' => '1',
        ];

        $tpl = $this->make(Template::class, [
            'setNotification' => function (string $message, string $type) {
                $this->notifications[] = [$message, $type];
            },
        ]);
        $language = $this->make(LanguageCore::class, ['__' => fn ($key) => $key]);

        $response = (new EditOwn($this->make(IncomingRequest::class), $tpl, $language))->post();

        $this->assertStringEndsWith('/users/editOwn#notifications', $response->headers->get('Location'));
    }

    public function test_rejected_webhook_url_shows_an_error_and_no_success(): void
    {
        $this->submitNotificationsTab($this->make(UserService::class, [
            'saveOwnNotificationPreferences' => function () {
                throw ValidationException::withMessages(['webhookUrl' => ['notification.invalid_webhook_url']]);
            },
        ]));

        $this->assertSame([['notification.invalid_webhook_url', 'error']], $this->notifications);
    }

    public function test_unsaved_webhook_setting_shows_a_generic_error_and_no_success(): void
    {
        $this->submitNotificationsTab($this->make(UserService::class, [
            'saveOwnNotificationPreferences' => function () {
                throw new WebhookSettingNotSavedException;
            },
        ]));

        $this->assertSame([['short_notifications.not_saved', 'error']], $this->notifications);
    }

    public function test_unexpected_failures_are_not_swallowed_into_a_notification(): void
    {
        // Only the known outcomes are handled; anything else (e.g. a DB error) must reach the exception handler.
        $this->expectExceptionObject(new \RuntimeException('database went away'));

        $this->submitNotificationsTab($this->make(UserService::class, [
            'saveOwnNotificationPreferences' => function () {
                throw new \RuntimeException('database went away');
            },
        ]));
    }

    public function test_accepted_preferences_show_the_success_confirmation(): void
    {
        $this->submitNotificationsTab($this->make(UserService::class, [
            'saveOwnNotificationPreferences' => fn () => null,
        ]));

        $this->assertSame([['notifications.changed_profile_settings_successfully', 'success']], $this->notifications);
    }
}
