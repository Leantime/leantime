<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;

/**
 * The weekly summary, sent by the reminder job ('reminders:dispatch') right before the morning
 * summary, through the shared {@see ScheduledSummaryRunner} loop.
 *
 * A person's weekly summary is due on their weekly day (ISO 1 Monday to 7 Sunday, default
 * Monday), from their weekly time until {@see ReminderDispatcher::WINDOW_MINUTES} after it, in
 * their timezone: the same window, catch-up and last-run rule as the morning summary. It goes out
 * at most once a week; a week starts on Monday in their timezone, so changing the day after this
 * week's summary went out doesn't send a second one.
 *
 * Combined with the morning summary: when the morning summary is due in the same run and hasn't
 * gone out today, the weekly message includes it as its Today section, and that one message
 * counts as both: its inbox row says so ({@see ReminderRepository::WEEKLY_WITH_TODAY}), and the
 * morning kind's check accepts it. Today is checked again under the send lock, right before the
 * row is written. With the defaults every
 * Monday is one message. Someone whose times differ gets two messages that day, each at the time
 * they chose.
 *
 * Nothing to show (nothing ahead, nothing finished, no plugin section that counts) writes
 * nothing, as the morning summary does. The morning summary then still goes out on its own.
 *
 * Both kinds use the same per-person lock, so their inbox check and write never interleave.
 */
class WeeklySummaryDispatcher extends ScheduledSummaryRunner
{
    /** Where a run that reached its time limit stopped: the last user id it handled. */
    public const CURSOR_KEY = 'reminders:weekly:cursor';

    /** @var array<int, array<string, mixed>> The summary each person's row was recorded with, for deliver(). */
    private array $recorded = [];

    public function __construct(
        private ReminderRepository $reminders,
        private ReminderSettings $settings,
        private WeeklySummaryBuilder $weeklyBuilder,
        private SummaryBuilder $morningBuilder,
        private ReminderNotifier $notifier,
    ) {}

    /**
     * Since when (UTC, the person's local Monday midnight) this week's summary counts as sent,
     * when it is due at $nowUtc: weekly summary on, today is their weekly day, and their local
     * time is inside the window after their weekly time. Null when it isn't due.
     *
     * @param  array{weekly: bool, weeklyDay: int, weeklyTime: string, timezone: string}  $mine
     */
    public static function dueSince(array $mine, CarbonImmutable $nowUtc): ?string
    {
        if (! $mine['weekly']) {
            return null;
        }

        $localNow = $nowUtc->setTimezone($mine['timezone']);
        if ($localNow->dayOfWeekIso !== $mine['weeklyDay']) {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', ReminderDispatcher::dueTime($mine['weeklyTime'])));
        $minutesPast = (int) $localNow->format('G') * 60 + (int) $localNow->format('i') - ($hours * 60 + $minutes);
        if ($minutesPast < 0 || $minutesPast > ReminderDispatcher::WINDOW_MINUTES) {
            return null;
        }

        return $localNow->startOfDay()->subDays($localNow->dayOfWeekIso - 1)->setTimezone('UTC')->format('Y-m-d H:i:s');
    }

    /** Sends nothing when reminders are off for the workspace. The morning kind records the heartbeat. */
    protected function startRun(CarbonImmutable $nowUtc): bool
    {
        return $this->settings->workspaceEnabled();
    }

    protected function usersAfter(int $afterId, int $limit): array
    {
        return $this->reminders->activeUsers($afterId, $limit);
    }

    protected function settingsFor(array $userIds): array
    {
        return $this->settings->forUsers($userIds);
    }

    protected function periodStartIfDue(array $mine, CarbonImmutable $nowUtc): ?string
    {
        return self::dueSince($mine, $nowUtc);
    }

    protected function sentSince(array $sinceByUser): array
    {
        return $this->reminders->weeklySummariesSentSince($sinceByUser);
    }

    /**
     * The week, with today's morning summary folded in when it is due now and hasn't gone out;
     * null when the week has nothing to show.
     */
    protected function buildFor(int $userId, CarbonImmutable $nowUtc, array $mine): ?array
    {
        $today = null;
        $morningSince = ReminderDispatcher::dueSince($mine, $nowUtc);
        if ($morningSince !== null && ! isset($this->reminders->summariesSentSince([$userId => $morningSince])[$userId])) {
            $today = $this->morningBuilder->build($userId, $nowUtc, $mine['timezone'], $mine['taskPreset']);
        }

        $week = $this->weeklyBuilder->buildWeek($userId, $nowUtc, $mine, $today);
        if ($week['isEmpty']) {
            return null;
        }

        // For record(): since when a morning summary would make Today a repeat.
        return $week['today'] !== null ? $week + ['morningSinceUtc' => $morningSince] : $week;
    }

    protected function activeUser(int $userId): ?array
    {
        return $this->reminders->activeUser($userId);
    }

    /**
     * Writes the weekly row, under the person's send lock (shared with the morning kind). Today is
     * checked again here: when another run recorded this morning's summary while this one was
     * being built, Today is dropped, so it never goes out twice. The row records whether Today
     * went out with it ({@see ReminderRepository::WEEKLY_WITH_TODAY}).
     */
    protected function record(array $user, array $summary, CarbonImmutable $sendNowUtc): void
    {
        $userId = (int) $user['id'];
        $since = $summary['morningSinceUtc'] ?? null;
        unset($summary['morningSinceUtc']);

        if ($summary['today'] !== null && is_string($since) && isset($this->reminders->summariesSentSince([$userId => $since])[$userId])) {
            $summary = self::withoutToday($summary);
        }

        $this->recorded[$userId] = $summary;
        $this->notifier->recordWeekly($user, $summary, $sendNowUtc);
    }

    /** Email and push of the summary as it was recorded (maybe without Today). */
    protected function deliver(array $user, array $summary, array $mine): void
    {
        $userId = (int) $user['id'];
        $recorded = $this->recorded[$userId] ?? $summary;
        unset($this->recorded[$userId]);

        $this->notifier->deliverWeekly($user, $recorded, $mine['weeklyDay'], $mine['weeklyTime']);
    }

    /**
     * The week without its Today section.
     *
     * @param  array<string, mixed>  $week
     * @return array<string, mixed>
     */
    private static function withoutToday(array $week): array
    {
        $week['today'] = null;
        $week['sections'] = array_values(array_filter($week['sections'], fn (array $section): bool => ($section['kind'] ?? '') !== 'today'));

        return $week;
    }

    protected function cursorKey(): string
    {
        return self::CURSOR_KEY;
    }

    /** The morning summary's lock: one person's summaries are recorded one at a time. */
    protected function lockKey(int $userId): string
    {
        return 'reminders:summary:'.$userId;
    }

    protected function label(): string
    {
        return 'Weekly summary';
    }

    protected function runLabel(): string
    {
        return 'Weekly summaries';
    }
}
