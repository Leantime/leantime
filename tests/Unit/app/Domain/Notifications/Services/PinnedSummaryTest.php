<?php

namespace Unit\app\Domain\Notifications\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationRepository;
use Leantime\Domain\Notifications\Services\Notifications;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\app\Domain\Reminders\ReminderDatabase;
use Unit\TestCase;

/**
 * Today's morning summary is pinned on top of the bell until the person's next local midnight.
 * Reading it clears the unread highlight, not the pin. The inbox API marks the row with
 * pinned = true and keeps its newest-first order. Runs the real repositories on SQLite.
 */
class PinnedSummaryTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use ReminderDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createReminderDatabase();
        $this->addUser(7);
        $this->addUser(8);

        $config = $this->make(Environment::class);
        $config->defaultTimezone = 'UTC';
        app()->instance(ReminderSettings::class, new ReminderSettings($this->settingRepository(), $config));
        $this->settingRepository()->saveSetting('usersettings.7.timezone', 'America/New_York');

        session(['userdata' => ['id' => 7]]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function service(): Notifications
    {
        $repository = (new \ReflectionClass(NotificationRepository::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(NotificationRepository::class, 'db'))->setValue($repository, $this->db);

        return new Notifications($repository, $this->make(UserRepository::class), $this->make(LanguageCore::class));
    }

    private function add(int $userId, string $type, string $datetimeUtc, int $read = 0): int
    {
        return (int) $this->db->table('zp_notifications')->insertGetId(['userId' => $userId, 'read' => $read, 'type' => $type, 'module' => 'x', 'moduleId' => 0, 'datetime' => $datetimeUtc, 'url' => '', 'message' => $type, 'authorId' => 0]);
    }

    /** 15:00 in New York on Nov 22 (20:00 UTC). New York's Nov 22 began at 05:00 UTC. */
    private function now(): CarbonImmutable
    {
        return CarbonImmutable::parse('2026-11-22 20:00:00', 'UTC');
    }

    public function test_today_s_summary_is_first_and_newer_notifications_sit_beneath_it(): void
    {
        $yesterday = $this->add(7, 'morning_summary', '2026-11-21 13:00:00');
        $today = $this->add(7, 'morning_summary', '2026-11-22 13:00:00');
        $older = $this->add(7, 'assigned', '2026-11-22 09:00:00');
        $newer = $this->add(7, 'comment', '2026-11-22 18:00:00');

        $rows = $this->service()->getBellNotifications(7, $this->now());

        $this->assertSame([$today, $newer, $older, $yesterday], array_map(fn ($row) => (int) $row['id'], $rows));
        $this->assertSame([true, false, false, false], array_column($rows, 'pinned'), 'yesterday\'s summary sorts by date');
    }

    public function test_the_pin_follows_the_person_s_own_midnight(): void
    {
        // 04:30 UTC on Nov 22 is still Nov 21 in New York: not today's.
        $this->add(7, 'morning_summary', '2026-11-22 04:30:00');
        $this->add(7, 'comment', '2026-11-22 06:00:00');

        $rows = $this->service()->getBellNotifications(7, $this->now());
        $this->assertSame([false, false], array_column($rows, 'pinned'));
        $this->assertSame('comment', $rows[0]['type']);

        // The next New York midnight unpins it too.
        $this->add(7, 'morning_summary', '2026-11-22 13:00:00');
        $nextDay = CarbonImmutable::parse('2026-11-23 05:30:00', 'UTC');
        $this->assertSame([false, false, false], array_column($this->service()->getBellNotifications(7, $nextDay), 'pinned'));
    }

    public function test_reading_the_summary_keeps_the_pin(): void
    {
        $this->add(7, 'comment', '2026-11-22 18:00:00');
        $today = $this->add(7, 'morning_summary', '2026-11-22 13:00:00', read: 1);

        $rows = $this->service()->getBellNotifications(7, $this->now());

        $this->assertSame($today, (int) $rows[0]['id']);
        $this->assertTrue($rows[0]['pinned']);
        $this->assertSame(1, (int) $rows[0]['read'], 'read, but still pinned');
    }

    public function test_the_pinned_summary_is_found_even_when_it_is_not_among_the_newest_rows(): void
    {
        $today = $this->add(7, 'morning_summary', '2026-11-22 13:00:00');
        for ($i = 0; $i < 100; $i++) {
            $this->add(7, 'comment', '2026-11-22 19:00:00');
        }

        $rows = $this->service()->getBellNotifications(7, $this->now());

        $this->assertSame($today, (int) $rows[0]['id']);
        $this->assertCount(101, $rows);
    }

    public function test_the_inbox_api_flags_the_pin_without_reordering(): void
    {
        $today = $this->add(7, 'morning_summary', '2026-11-22 13:00:00');
        $newer = $this->add(7, 'comment', '2026-11-22 18:00:00');
        $this->add(8, 'morning_summary', '2026-11-22 13:00:00');

        $rows = $this->service()->pinTodaysSummary($this->service()->getAllNotifications(7), 7, false, $this->now());

        $this->assertSame([$newer, $today], array_map(fn ($row) => (int) $row['id'], $rows), 'still newest first');
        $this->assertSame([false, true], array_column($rows, 'pinned'));

        CarbonImmutable::setTestNow($this->now());
        $inbox = $this->service()->getInbox();
        $this->assertSame([$newer, $today], array_map(fn ($row) => (int) $row['id'], $inbox));
        $this->assertArrayHasKey('pinned', $inbox[0], 'every row carries the flag');
    }

    public function test_someone_else_s_summary_is_never_pinned_for_you(): void
    {
        $this->add(8, 'morning_summary', '2026-11-22 13:00:00');
        $this->add(7, 'comment', '2026-11-22 18:00:00');

        $this->assertSame([false], array_column($this->service()->getBellNotifications(7, $this->now()), 'pinned'));
    }
}
