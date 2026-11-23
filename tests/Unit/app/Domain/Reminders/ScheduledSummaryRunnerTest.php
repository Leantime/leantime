<?php

namespace Unit\app\Domain\Reminders;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Leantime\Domain\Reminders\Services\ScheduledSummaryRunner;
use Unit\TestCase;

/**
 * The shared loop behind scheduled summaries, run with a stand-in kind that isn't the morning
 * summary (a weekly one, due on Mondays from 08:00): the loop itself knows nothing about days.
 * The morning summary's own behaviour is covered by ReminderDispatcherTest.
 */
class ScheduledSummaryRunnerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('test-weekly:cursor');
    }

    public function test_a_kind_sends_once_per_its_own_period(): void
    {
        $runner = new WeeklyTestRunner(users: [1, 2]);

        $this->assertSame(2, $runner->run(CarbonImmutable::parse('2026-11-23 08:00:00', 'UTC')));   // Monday
        $this->assertSame(0, $runner->run(CarbonImmutable::parse('2026-11-23 08:05:00', 'UTC')));   // same week
        $this->assertSame(0, $runner->run(CarbonImmutable::parse('2026-11-24 08:00:00', 'UTC')));   // Tuesday: not due
        $this->assertSame(2, $runner->run(CarbonImmutable::parse('2026-11-30 08:00:00', 'UTC')));   // next Monday

        $this->assertSame([1, 2, 1, 2], array_column($runner->delivered, 'user'));
        $this->assertSame(['2026-11-23 08:00:00', '2026-11-23 08:00:00', '2026-11-30 08:00:00', '2026-11-30 08:00:00'], array_column($runner->inbox, 'at'));
    }

    public function test_nothing_to_send_records_nothing_and_a_failed_record_delivers_nothing(): void
    {
        $runner = new WeeklyTestRunner(users: [1, 2, 3]);
        $runner->emptyFor = [2];
        $runner->recordFailsFor = [3];

        $this->assertSame(1, $runner->run(CarbonImmutable::parse('2026-11-23 08:00:00', 'UTC')));

        $this->assertSame([1], array_column($runner->inbox, 'user'));
        $this->assertSame([1], array_column($runner->delivered, 'user'), 'no email or push without the inbox row');
    }

    public function test_a_kind_uses_its_own_cursor_and_lock_keys(): void
    {
        $runner = new WeeklyTestRunner(users: [1, 2]);
        $runner->lockHeld = [2];

        $this->assertSame(1, $runner->run(CarbonImmutable::parse('2026-11-23 08:00:00', 'UTC')));

        $this->assertSame(['test-weekly:lock:1', 'test-weekly:lock:2'], $runner->locksAsked);
        $this->assertSame(0, (int) Cache::get('test-weekly:cursor', 0), 'a finished run clears its cursor');
    }
}

/**
 * A weekly summary kind for the test: due on Mondays from 08:00 UTC, its period is the week.
 */
class WeeklyTestRunner extends ScheduledSummaryRunner
{
    /** @var list<array{user: int, at: string}> */
    public array $inbox = [];

    /** @var list<array{user: int}> */
    public array $delivered = [];

    /** @var list<int> */
    public array $emptyFor = [];

    /** @var list<int> */
    public array $recordFailsFor = [];

    /** @var list<int> Users whose lock another run holds. */
    public array $lockHeld = [];

    /** @var list<string> */
    public array $locksAsked = [];

    /**
     * @param  list<int>  $users
     */
    public function __construct(private array $users) {}

    protected function startRun(CarbonImmutable $nowUtc): bool
    {
        return true;
    }

    protected function usersAfter(int $afterId, int $limit): array
    {
        $after = array_values(array_filter($this->users, fn (int $id): bool => $id > $afterId));

        return array_map(fn (int $id): array => ['id' => $id], array_slice($after, 0, $limit));
    }

    protected function settingsFor(array $userIds): array
    {
        return array_fill_keys(array_map('intval', $userIds), ['weekly' => true]);
    }

    protected function periodStartIfDue(array $mine, CarbonImmutable $nowUtc): ?string
    {
        if (! $nowUtc->isMonday() || $nowUtc->format('H:i') < '08:00') {
            return null;
        }

        return $nowUtc->startOfWeek()->format('Y-m-d H:i:s');
    }

    protected function sentSince(array $sinceByUser, array $settingsByUser): array
    {
        $sent = [];
        foreach ($this->inbox as $row) {
            if (isset($sinceByUser[$row['user']]) && $row['at'] >= $sinceByUser[$row['user']]) {
                $sent[$row['user']] = true;
            }
        }

        return $sent;
    }

    protected function buildFor(int $userId, CarbonImmutable $nowUtc, array $mine): ?array
    {
        return in_array($userId, $this->emptyFor, true) ? null : ['items' => [1]];
    }

    protected function activeUser(int $userId): ?array
    {
        return ['id' => $userId];
    }

    protected function record(array $user, array $summary, CarbonImmutable $sendNowUtc): void
    {
        if (in_array($user['id'], $this->recordFailsFor, true)) {
            throw new \RuntimeException('inbox row not written');
        }

        $this->inbox[] = ['user' => $user['id'], 'at' => $sendNowUtc->format('Y-m-d H:i:s')];
    }

    protected function deliver(array $user, array $summary, array $mine): void
    {
        $this->delivered[] = ['user' => $user['id']];
    }

    protected function sendLock(int $userId): \Illuminate\Contracts\Cache\Lock
    {
        $this->locksAsked[] = $this->lockKey($userId);
        $lock = parent::sendLock($userId);
        if (in_array($userId, $this->lockHeld, true)) {
            $lock->get(); // another run holds it: the runner's own get() fails

            return Cache::lock($this->lockKey($userId), static::SEND_LOCK_SECONDS);
        }

        return $lock;
    }

    protected function cursorKey(): string
    {
        return 'test-weekly:cursor';
    }

    protected function lockKey(int $userId): string
    {
        return 'test-weekly:lock:'.$userId;
    }

    protected function label(): string
    {
        return 'Test weekly summary';
    }

    protected function runLabel(): string
    {
        return 'Test weekly summaries';
    }
}
