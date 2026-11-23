<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;

/**
 * The reminder job ('reminders:dispatch', every five minutes, never two at once).
 *
 * A person's morning summary is due from their summary time until {@see WINDOW_MINUTES} after
 * it, in their timezone. A run that was missed catches up on the next one inside that window;
 * after it, nothing goes out that day: a "this morning" summary in the afternoon is noise. The
 * window never crosses midnight; times after the day's last run ({@see LAST_RUN_TIME}) are due
 * at that run.
 *
 * The inbox is the record of what was sent. A summary writes its inbox row first, then the email
 * and push. A person who already has a summary row since their local midnight gets nothing more
 * that day.
 *
 * Sending once doesn't depend on the scheduler's overlap lock alone, which expires after 10
 * minutes while a slow run could still be going. Each person's send takes its own short lock
 * (same cache store as the scheduler's) and checks the inbox again inside it, so two runs can
 * never both send: the second can't get the lock, or finds the row. A run also stops starting
 * new people after {@see MAX_RUN_SECONDS}, well inside the overlap lock; the next run starts where
 * it stopped (and wraps around), so the people after that point are reached even when the ones
 * before it keep runs busy. Right before a send the clock is read again: a summary whose day or
 * window ended while the run was busy is skipped, and the inbox row carries the real send time.
 *
 * An empty day writes nothing. A later run inside the window still sends one summary if something
 * has come due since, so a person gets at most one summary a day, and maybe none.
 *
 * Every run records a heartbeat, so admins can be told when the scheduler isn't running.
 */
class ReminderDispatcher
{
    public const USERS_PER_CHUNK = 200;

    /** How long after the summary time a summary may still go out. */
    public const WINDOW_MINUTES = 180;

    /**
     * The day's last five-minute run. A summary time after it (23:56 to 23:59) is due at this
     * run instead, a few minutes early, because the next run is already the next day.
     */
    public const LAST_RUN_TIME = '23:55';

    /** A run starts no new person after this long (the overlap lock lasts 10 minutes). */
    public const MAX_RUN_SECONDS = 480;

    /** How long one person's send lock lasts; it covers one inbox check and one insert. */
    public const SEND_LOCK_SECONDS = 60;

    /** Where a run that reached its time limit stopped: the last user id it handled. */
    public const CURSOR_KEY = 'reminders:dispatch:cursor';

    /** The time run() was given (tests), or null to read the real clock at each send. */
    private ?CarbonImmutable $fixedNow = null;

    public function __construct(
        private ReminderRepository $reminders,
        private ReminderSettings $settings,
        private SummaryBuilder $builder,
        private ReminderNotifier $notifier,
    ) {}

    /**
     * @return int How many summaries were sent.
     */
    public function run(?CarbonImmutable $nowUtc = null): int
    {
        $this->fixedNow = $nowUtc;
        $nowUtc ??= CarbonImmutable::now('UTC');
        $startedAt = $this->seconds();
        $this->settings->recordHeartbeat($nowUtc->format('Y-m-d H:i:s'));

        if (! $this->settings->workspaceEnabled()) {
            return 0;
        }

        $sent = 0;

        // Start after where the last run ran out of time, then wrap around to the start: people
        // late in the list are reached even when the ones before them keep runs busy (an empty
        // summary writes nothing, so the same people are rebuilt on every run).
        $resumeAfter = $this->loadCursor();

        foreach ([[$resumeAfter, PHP_INT_MAX], [0, $resumeAfter]] as [$afterId, $upTo]) {
            if ($afterId >= $upTo) {
                continue;
            }

            while (($users = $this->reminders->activeUsers($afterId, self::USERS_PER_CHUNK)) !== []) {
                $isFullChunk = count($users) === self::USERS_PER_CHUNK;
                $users = array_values(array_filter($users, fn (array $user): bool => (int) $user['id'] <= $upTo));
                if ($users === []) {
                    break;
                }

                $afterId = (int) end($users)['id'];
                $settings = $this->settings->forUsers(array_column($users, 'id'));

                // Who is due: summary on, local time inside the window after their summary time.
                $since = [];
                foreach ($users as $user) {
                    $dueSince = self::dueSince($settings[$user['id']], $nowUtc);
                    if ($dueSince !== null) {
                        $since[$user['id']] = $dueSince;
                    }
                }

                $alreadySent = $this->reminders->summariesSentSince($since);

                foreach (array_keys($since) as $userId) {
                    if (isset($alreadySent[$userId])) {
                        continue;
                    }

                    if ($this->seconds() - $startedAt > self::MAX_RUN_SECONDS) {
                        Log::info('Morning summaries: run reached '.self::MAX_RUN_SECONDS.' seconds; the next run continues from here.');
                        $this->saveCursor($userId - 1);

                        return $sent;
                    }

                    if ($this->sendOnce($userId, $since[$userId], $nowUtc, $settings[$userId])) {
                        $sent++;
                    }
                }

                if (! $isFullChunk) {
                    break;
                }
            }
        }

        $this->clearCursor();

        return $sent;
    }

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

    /**
     * Builds and sends one person's summary, at most once a day even when runs overlap. The slow
     * part (building) happens first, without a lock. Then, under the person's lock, the inbox is
     * checked again and the row written; that is all the lock covers, so it can't expire while
     * held. Email and push follow once the row exists: any other run now finds the row.
     *
     * @param  array{summary: bool, summaryTime: string, taskPreset: string, timezone: string}  $mine
     * @return bool Whether a summary went out.
     */
    private function sendOnce(int $userId, string $sinceUtc, CarbonImmutable $nowUtc, array $mine): bool
    {
        try {
            $summary = $this->builder->build($userId, $nowUtc, $mine['timezone'], $mine['taskPreset']);
            if ($summary['isEmpty']) {
                return false;
            }

            // The run may have been busy for minutes: still the same local day, still inside the
            // window? Otherwise this summary is stale (yesterday's, or "this morning" too late).
            $sendNow = $this->currentTime();
            if (self::dueSince($mine, $sendNow) !== $sinceUtc) {
                return false;
            }

            $user = $this->reminders->activeUser($userId);
            if ($user === null) {
                return false;
            }

            $lock = $this->sendLock($userId);
            if (! $lock->get()) {
                return false; // another run is recording this person's summary right now
            }

            try {
                if (isset($this->reminders->summariesSentSince([$userId => $sinceUtc])[$userId])) {
                    return false;
                }

                $this->notifier->recordSummary($user, $summary, $sendNow);
            } finally {
                $lock->release();
            }

            $this->notifier->deliverSummary($user, $summary, $mine['summaryTime']);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Morning summary failed for user '.$userId.': '.$e->getMessage());

            return false;
        }
    }

    /** The last user id a run handled before it ran out of time; 0 when the last run finished. */
    protected function loadCursor(): int
    {
        return (int) Cache::get(self::CURSOR_KEY, 0);
    }

    protected function saveCursor(int $lastHandledUserId): void
    {
        Cache::put(self::CURSOR_KEY, max(0, $lastHandledUserId), CarbonImmutable::now()->addDay());
    }

    protected function clearCursor(): void
    {
        Cache::forget(self::CURSOR_KEY);
    }

    /** One person's send lock, in the default cache store (the one the scheduler's locks use). */
    protected function sendLock(int $userId): Lock
    {
        return Cache::lock('reminders:summary:'.$userId, self::SEND_LOCK_SECONDS);
    }

    /** The time a summary is recorded at: the time run() was given, else the real clock now. */
    protected function currentTime(): CarbonImmutable
    {
        return $this->fixedNow ?? CarbonImmutable::now('UTC');
    }

    /** Seconds on a steady clock, to bound a run. */
    protected function seconds(): float
    {
        return hrtime(true) / 1e9;
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
