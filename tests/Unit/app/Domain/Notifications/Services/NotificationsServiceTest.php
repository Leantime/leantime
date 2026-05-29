<?php

namespace Unit\app\Domain\Notifications\Services;

use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationRepository;
use Leantime\Domain\Notifications\Services\Notifications;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Unit tests for the flash-notification orchestration extracted from the
 * Notifications GetLatestGrowl controller into the Notifications service.
 */
class NotificationsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a Notifications service with stubbed dependencies. The
     * consumeFlashNotification logic only touches the session, so the
     * collaborators just need to exist.
     */
    private function makeService(): Notifications
    {
        return new Notifications(
            $this->make(DbCore::class),
            $this->make(NotificationRepository::class),
            $this->make(UserRepository::class),
            $this->make(LanguageCore::class),
        );
    }

    public function test_consume_flash_notification_returns_null_when_empty(): void
    {
        session(['notification' => '']);

        $service = $this->makeService();

        $this->assertNull($service->consumeFlashNotification());
    }

    public function test_consume_flash_notification_returns_payload_and_clears_session(): void
    {
        session(['notification' => 'Saved!']);
        session(['notificationType' => 'success']);
        session(['eventId' => 'ticket-42']);

        $service = $this->makeService();

        $payload = $service->consumeFlashNotification();

        $this->assertSame([
            'notification' => 'Saved!',
            'type' => 'success',
            'eventId' => 'ticket-42',
        ], $payload);

        // Read-once: session keys are cleared after consumption.
        $this->assertSame('', session('notification'));
        $this->assertSame('', session('notificationType'));
        $this->assertSame('', session('eventId'));
    }

    public function test_consume_flash_notification_defaults_missing_type_and_event(): void
    {
        session()->forget('notificationType');
        session()->forget('eventId');
        session(['notification' => 'Hello']);

        $service = $this->makeService();

        $payload = $service->consumeFlashNotification();

        $this->assertSame([
            'notification' => 'Hello',
            'type' => '',
            'eventId' => '',
        ], $payload);
    }

    // ---------------------------------------------------------------------
    // markRead() — session-based JSON-RPC wrapper (must target the session user)
    // ---------------------------------------------------------------------

    public function test_mark_read_all_targets_the_session_user(): void
    {
        session(['userdata' => ['id' => 42]]);

        $capturedUserId = null;
        $repo = $this->make(NotificationRepository::class, [
            'markAllNotificationRead' => function ($userId) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return true;
            },
        ]);

        $service = new Notifications(
            $this->make(DbCore::class),
            $repo,
            $this->make(UserRepository::class),
            $this->make(LanguageCore::class),
        );

        $this->assertTrue($service->markRead('all'));
        $this->assertSame(42, $capturedUserId, "markRead('all') must use the session user");
    }

    public function test_mark_read_specific_id_delegates_to_repo(): void
    {
        session(['userdata' => ['id' => 7]]);

        $calledWith = null;
        $repo = $this->make(NotificationRepository::class, [
            'markNotificationRead' => function ($id) use (&$calledWith) {
                $calledWith = $id;

                return true;
            },
        ]);

        $service = new Notifications(
            $this->make(DbCore::class),
            $repo,
            $this->make(UserRepository::class),
            $this->make(LanguageCore::class),
        );

        $this->assertTrue($service->markRead(5));
        $this->assertSame(5, $calledWith);
    }
}
