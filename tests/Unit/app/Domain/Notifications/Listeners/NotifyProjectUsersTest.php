<?php

namespace Unit\app\Domain\Notifications\Listeners;

use Leantime\Domain\Notifications\Listeners\NotifyProjectUsers;
use Leantime\Domain\Notifications\Services\Notifications;
use Unit\TestCase;

/**
 * #3201: notification times showed up shifted by the user's UTC offset (6h for US Central).
 * The Localization middleware sets PHP's default timezone to the USER's, so date() produced
 * local time, which the bell then rendered as if it were UTC.
 */
class NotifyProjectUsersTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private string $originalTimezone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalTimezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
        parent::tearDown();
    }

    public function test_notification_time_is_stored_in_utc_regardless_of_the_request_timezone(): void
    {
        date_default_timezone_set('America/Chicago');

        $stored = null;
        $this->app->instance(Notifications::class, $this->make(Notifications::class, [
            'addNotifications' => function (array $notifications) use (&$stored) {
                $stored = $notifications;

                return true;
            },
        ]));

        (new NotifyProjectUsers)->handle([
            'users' => [['id' => 5]],
            'type' => 'projectUpdate',
            'module' => 'tickets',
            'moduleId' => 1,
            'message' => 'm',
            'url' => 'u',
        ]);

        $storedTime = new \DateTimeImmutable($stored[0]['datetime'], new \DateTimeZone('UTC'));
        $utcNow = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $this->assertLessThan(120, abs($utcNow->getTimestamp() - $storedTime->getTimestamp()), 'datetime must be UTC, not America/Chicago local time');
    }
}
