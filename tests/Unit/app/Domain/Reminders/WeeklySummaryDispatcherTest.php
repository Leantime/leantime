<?php

namespace Unit\app\Domain\Reminders;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Leantime\Domain\Reminders\Services\ReminderDispatcher;
use Leantime\Domain\Reminders\Services\ReminderNotifier;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Leantime\Domain\Reminders\Services\SummaryBuilder;
use Leantime\Domain\Reminders\Services\WeeklySummaryBuilder;
use Leantime\Domain\Reminders\Services\WeeklySummaryDispatcher;
use Unit\TestCase;

/**
 * The weekly summary goes out at most once a week, on the person's day, inside the window after
 * their time. When their morning summary is due in the same run, the two are one message that
 * counts as both. Runs both kinds the way the job does (weekly, then morning) on the real inbox
 * check on SQLite. 2026-11-23 is a Monday.
 */
class WeeklySummaryDispatcherTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use ReminderDatabase;

    /** @var array<int, array{0: string, 1: int, 2?: bool}> what went out: kind, user, whether Today was folded in */
    private array $sent = [];

    /** @var array<int, array<string, mixed>> */
    private array $userSettings = [];

    /** @var array<int, true> users whose week has nothing to show */
    private array $emptyWeek = [];

    /** @var array<int, true> users whose day has nothing to show */
    private array $emptyDay = [];

    private bool $workspaceEnabled = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createReminderDatabase();
        $this->addUser(7);
        $this->addUser(8);
        $this->sent = [];
        $this->emptyWeek = [];
        $this->emptyDay = [];
        $this->workspaceEnabled = true;
        Cache::forget(ReminderDispatcher::CURSOR_KEY);
        Cache::forget(WeeklySummaryDispatcher::CURSOR_KEY);

        $settings = ['summary' => true, 'summaryTime' => '08:00', 'taskPreset' => 'default', 'timezone' => 'UTC', 'weekly' => true, 'weeklyDay' => 1, 'weeklyTime' => '08:00', 'weeklyLookBack' => true];
        $this->userSettings = [7 => $settings, 8 => $settings];
    }

    /** One run of the reminder job: the weekly kind, then the morning kind. */
    private function job(string $nowUtc): void
    {
        $now = CarbonImmutable::parse($nowUtc, 'UTC');

        $settings = $this->make(ReminderSettings::class, [
            'forUsers' => fn (array $ids) => array_intersect_key($this->userSettings, array_flip($ids)),
            'workspaceEnabled' => fn () => $this->workspaceEnabled,
            'recordHeartbeat' => fn () => null,
        ]);
        $morning = $this->make(SummaryBuilder::class, [
            'build' => fn (int $userId) => ['isEmpty' => isset($this->emptyDay[$userId]), 'items' => [['id' => 1]]],
        ]);
        $weekly = $this->make(WeeklySummaryBuilder::class, [
            'buildWeek' => fn (int $userId, $now, array $mine, ?array $today = null) => [
                'isEmpty' => isset($this->emptyWeek[$userId]),
                'today' => $today !== null && ! $today['isEmpty'] ? $today : null,
            ],
        ]);
        $notifier = $this->make(ReminderNotifier::class, [
            'recordSummary' => fn (array $user, array $summary, CarbonImmutable $at) => $this->addSummaryRow($user['id'], $at->format('Y-m-d H:i:s')),
            'deliverSummary' => function (array $user) {
                $this->sent[] = ['morning', $user['id']];
            },
            'recordWeekly' => fn (array $user, array $week, CarbonImmutable $at) => $this->addSummaryRow($user['id'], $at->format('Y-m-d H:i:s'), 'weekly_summary'),
            'deliverWeekly' => function (array $user, array $week) {
                $this->sent[] = ['weekly', $user['id'], $week['today'] !== null];
            },
        ]);

        (new WeeklySummaryDispatcher($this->repository(), $settings, $weekly, $morning, $notifier))->run($now);
        (new ReminderDispatcher($this->repository(), $settings, $morning, $notifier))->run($now);
    }

    private function sentTo(int $userId): array
    {
        return array_values(array_filter($this->sent, fn (array $sent) => $sent[1] === $userId));
    }

    public function test_with_the_defaults_monday_is_one_message_that_counts_as_both(): void
    {
        $this->job('2026-11-23 08:00:00');
        $this->job('2026-11-23 08:05:00');
        $this->job('2026-11-23 10:00:00');

        $this->assertSame([['weekly', 7, true]], $this->sentTo(7), 'one message with Today folded in');
        $this->assertSame([['weekly', 8, true]], $this->sentTo(8));
        $this->assertSame(['weekly_summary'], $this->db->table('zp_notifications')->where('userId', 7)->pluck('type')->all(), 'one inbox row');

        $this->job('2026-11-24 08:00:00');
        $this->assertSame([['weekly', 7, true], ['morning', 7]], $this->sentTo(7), 'Tuesday is a morning summary again');
    }

    public function test_different_times_get_two_messages_each_at_its_own_time(): void
    {
        $this->userSettings[7]['weeklyTime'] = '17:00';

        $this->job('2026-11-23 08:00:00');
        $this->assertSame([['morning', 7]], $this->sentTo(7));

        $this->job('2026-11-23 17:00:00');
        $this->assertSame([['morning', 7], ['weekly', 7, false]], $this->sentTo(7), 'the morning summary already went out: not folded in again');
    }

    public function test_a_weekly_summary_sent_before_the_morning_one_was_due_does_not_count_as_it(): void
    {
        $this->userSettings[7]['summaryTime'] = '10:00';

        $this->job('2026-11-23 08:00:00');
        $this->assertSame([['weekly', 7, false]], $this->sentTo(7), 'the morning summary isn\'t due yet');

        $this->job('2026-11-23 10:00:00');
        $this->assertSame([['weekly', 7, false], ['morning', 7]], $this->sentTo(7));
    }

    public function test_it_goes_out_once_a_week_even_when_the_day_changes(): void
    {
        $this->job('2026-11-23 08:00:00');
        $this->userSettings[7]['weeklyDay'] = 3;
        $this->job('2026-11-25 08:00:00');

        $this->assertSame([['weekly', 7, true], ['morning', 7]], $this->sentTo(7), 'Wednesday of the same week: the morning summary only');

        $this->job('2026-12-02 08:00:00');
        $this->assertSame(['weekly', 7, true], $this->sentTo(7)[2], 'the next Wednesday');
    }

    public function test_only_on_the_day_and_inside_the_window(): void
    {
        $this->userSettings[7]['summary'] = false;
        $this->userSettings[7]['weeklyDay'] = 5;

        $this->job('2026-11-23 08:00:00');                  // Monday
        $this->job('2026-11-27 07:55:00');                  // Friday, before the time
        $this->job('2026-11-27 11:05:00');                  // Friday, after the window
        $this->assertSame([], $this->sentTo(7));

        $this->job('2026-11-27 11:00:00');                  // the window's last run
        $this->assertSame([['weekly', 7, false]], $this->sentTo(7));
    }

    public function test_the_day_and_time_are_the_person_s_own(): void
    {
        $this->userSettings[7]['timezone'] = 'America/Los_Angeles';

        $this->job('2026-11-23 08:00:00');                  // Monday 00:00 in Los Angeles
        $this->assertSame([], $this->sentTo(7));

        $this->job('2026-11-23 16:00:00');                  // Monday 08:00 there
        $this->assertSame([['weekly', 7, true]], $this->sentTo(7));
    }

    public function test_an_empty_week_sends_the_morning_summary_on_its_own(): void
    {
        $this->emptyWeek[7] = true;

        $this->job('2026-11-23 08:00:00');

        $this->assertSame([['morning', 7]], $this->sentTo(7));
        $this->assertSame(['morning_summary'], $this->db->table('zp_notifications')->where('userId', 7)->pluck('type')->all());
    }

    public function test_an_empty_day_still_sends_the_week_without_today(): void
    {
        $this->emptyDay[7] = true;

        $this->job('2026-11-23 08:00:00');

        $this->assertSame([['weekly', 7, false]], $this->sentTo(7));
    }

    public function test_turned_off_by_the_person_or_the_workspace(): void
    {
        $this->userSettings[7]['weekly'] = false;
        $this->job('2026-11-23 08:00:00');
        $this->assertSame([['morning', 7]], $this->sentTo(7), 'the morning summary is separate');

        $this->sent = [];
        $this->db->table('zp_notifications')->delete();
        $this->workspaceEnabled = false;
        $this->job('2026-11-30 08:00:00');
        $this->assertSame([], $this->sent);
    }

    public function test_due_since_is_the_local_monday_and_respects_the_last_run(): void
    {
        $mine = ['weekly' => true, 'weeklyDay' => 4, 'weeklyTime' => '23:58', 'timezone' => 'Europe/Berlin'];

        // Thursday 23:55 in Berlin is 22:55 UTC; 23:58 is due at the day's last run.
        $this->assertSame('2026-11-22 23:00:00', WeeklySummaryDispatcher::dueSince($mine, CarbonImmutable::parse('2026-11-26 22:55:00', 'UTC')));
        $this->assertNull(WeeklySummaryDispatcher::dueSince($mine, CarbonImmutable::parse('2026-11-26 22:50:00', 'UTC')));
        $this->assertNull(WeeklySummaryDispatcher::dueSince(['weekly' => false] + $mine, CarbonImmutable::parse('2026-11-26 22:55:00', 'UTC')));
    }
}
