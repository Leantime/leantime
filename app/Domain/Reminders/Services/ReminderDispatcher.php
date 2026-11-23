<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;

/**
 * The morning summary, sent by the reminder job ('reminders:dispatch', every five minutes, never
 * two at once, right after the weekly summary) through the shared {@see ScheduledSummaryRunner} loop (claiming by inbox row,
 * per-person lock, run budget and resume cursor are described there).
 *
 * A person's morning summary is due from their summary time until {@see WINDOW_MINUTES} after
 * it, in their timezone. A run that was missed catches up on the next one inside that window;
 * after it, nothing goes out that day: a "this morning" summary in the afternoon is noise. The
 * window never crosses midnight; times after the day's last run ({@see LAST_RUN_TIME}) are due
 * at that run.
 *
 * The inbox is the record of what was sent. A summary writes its inbox row first, then the email
 * and push. A person who already has a summary row since their local midnight gets nothing more
 * that day. A weekly summary that included Today (recorded on its inbox row) counts as that
 * day's morning summary too.
 *
 * An empty day writes nothing. A later run inside the window still sends one summary if something
 * has come due since, so a person gets at most one summary a day, and maybe none.
 *
 * Every run records a heartbeat, so admins can be told when the scheduler isn't running.
 */
class ReminderDispatcher extends ScheduledSummaryRunner
{
    /** How long after the summary time a summary may still go out. */
    public const WINDOW_MINUTES = 180;

    /**
     * The day's last five-minute run. A summary time after it (23:56 to 23:59) is due at this
     * run instead, a few minutes early, because the next run is already the next day.
     */
    public const LAST_RUN_TIME = '23:55';

    /** Where a run that reached its time limit stopped: the last user id it handled. */
    public const CURSOR_KEY = 'reminders:dispatch:cursor';

    public function __construct(
        private ReminderRepository $reminders,
        private ReminderSettings $settings,
        private SummaryBuilder $builder,
        private ReminderNotifier $notifier,
    ) {}

    /**
     * Since when (UTC, the person's local midnight) a person's summary counts as sent today, when
     * it is due at $nowUtc: summary on, and their local time inside the window after their
     * summary time. Null when it isn't due.
     *
     * @param  array{summary: bool, summaryTime: string, taskPreset: string, timezone: string}  $mine
     */
    public static function dueSince(array $mine, CarbonImmutable $nowUtc): ?string
    {
        if (! $mine['summary']) {
            return null;
        }

        $localNow = $nowUtc->setTimezone($mine['timezone']);
        $minutesPast = self::minutesOfDay($localNow->format('H:i')) - self::minutesOfDay(self::dueTime($mine['summaryTime']));
        if ($minutesPast < 0 || $minutesPast > self::WINDOW_MINUTES) {
            return null;
        }

        return $localNow->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
    }

    /** Records the heartbeat; sends nothing when reminders are off for the workspace. */
    protected function startRun(CarbonImmutable $nowUtc): bool
    {
        $this->settings->recordHeartbeat($nowUtc->format('Y-m-d H:i:s'));

        return $this->settings->workspaceEnabled();
    }

    protected function usersAfter(int $afterId, int $limit): array
    {
        return $this->reminders->activeUsers($afterId, $limit);
    }

    /**
     * @return array<int, array{summary: bool, summaryTime: string, taskPreset: string, timezone: string}>
     */
    protected function settingsFor(array $userIds): array
    {
        return $this->settings->forUsers($userIds);
    }

    /** The person's local midnight (UTC) while their summary is due; see {@see dueSince()}. */
    protected function periodStartIfDue(array $mine, CarbonImmutable $nowUtc): ?string
    {
        return self::dueSince($mine, $nowUtc);
    }

    /**
     * Today's morning summary row, or a weekly summary row that included today's morning summary
     * (the weekly kind runs first in the same job and folds it in when both are due; see
     * {@see WeeklySummaryDispatcher}). A weekly summary without Today doesn't count, so someone
     * whose times differ gets both.
     */
    protected function sentSince(array $sinceByUser): array
    {
        return $this->reminders->summariesSentSince($sinceByUser);
    }

    /** The morning summary, or null on an empty day. */
    protected function buildFor(int $userId, CarbonImmutable $nowUtc, array $mine): ?array
    {
        $summary = $this->builder->build($userId, $nowUtc, $mine['timezone'], $mine['taskPreset']);

        return $summary['isEmpty'] ? null : $summary;
    }

    protected function activeUser(int $userId): ?array
    {
        return $this->reminders->activeUser($userId);
    }

    protected function record(array $user, array $summary, CarbonImmutable $sendNowUtc): void
    {
        $this->notifier->recordSummary($user, $summary, $sendNowUtc);
    }

    protected function deliver(array $user, array $summary, array $mine): void
    {
        $this->notifier->deliverSummary($user, $summary, $mine['summaryTime']);
    }

    protected function cursorKey(): string
    {
        return self::CURSOR_KEY;
    }

    protected function lockKey(int $userId): string
    {
        return 'reminders:summary:'.$userId;
    }

    protected function label(): string
    {
        return 'Morning summary';
    }

    protected function runLabel(): string
    {
        return 'Morning summaries';
    }

    /**
     * When a summary time becomes due: the time itself (any minute, e.g. 08:02 goes out with the
     * 08:05 run), except after the day's last run, which gets it instead.
     */
    public static function dueTime(string $summaryTime): string
    {
        return $summaryTime > self::LAST_RUN_TIME ? self::LAST_RUN_TIME : $summaryTime;
    }

    private static function minutesOfDay(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return $hours * 60 + $minutes;
    }
}
