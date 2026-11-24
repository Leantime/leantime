<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sends one kind of scheduled summary to everyone it's due for, at most once per period, from a
 * job that runs every few minutes. The morning summary ({@see ReminderDispatcher}) is the first
 * kind; the loop lives here so other kinds (a weekly summary) send the same way.
 *
 * How a run works, for every kind:
 * - Active users are read in chunks of {@see USERS_PER_CHUNK}. For each chunk, the kind says who
 *   is due right now and since when their current period started (a local midnight, a week
 *   start); people who already have the kind's inbox row since then are skipped.
 * - The inbox is the record of what was sent. A summary is built first, without a lock. Then,
 *   under that person's short lock ({@see SEND_LOCK_SECONDS}, in the default cache store, the one
 *   the scheduler's locks use), the inbox is checked again and the row written. Email and push
 *   go out after the row exists, so a second run either can't get the lock or finds the row.
 * - Right before the row is written, the clock is read again: a summary whose period or window
 *   ended while the run was busy is skipped, and the row carries the real send time.
 * - A run starts no new person after {@see MAX_RUN_SECONDS} (well inside the scheduler's
 *   10-minute overlap lock). It saves where it stopped; the next run continues there and wraps
 *   around, so people late in the list are reached even when the ones before them keep runs busy.
 * - An empty summary writes nothing, so a later run inside the window may still send one.
 *
 * A kind supplies the parts that differ: who is due and since when, how the "already sent"
 * check reads the inbox, how a summary is built, recorded and delivered, and its cache keys.
 */
abstract class ScheduledSummaryRunner
{
    /** How many users are read per chunk. */
    public const USERS_PER_CHUNK = 200;

    /** A run starts no new person after this long (the overlap lock lasts 10 minutes). */
    public const MAX_RUN_SECONDS = 480;

    /** How long one person's send lock lasts; it covers one inbox check and one insert. */
    public const SEND_LOCK_SECONDS = 60;

    /** The time run() was given (tests), or null to read the real clock at each send. */
    private ?CarbonImmutable $fixedNow = null;

    /**
     * Sends this kind's summary to everyone it's due for.
     *
     * @param  CarbonImmutable|null  $nowUtc  The run's time (tests); the real clock when null.
     * @return int How many summaries were sent.
     */
    public function run(?CarbonImmutable $nowUtc = null): int
    {
        $this->fixedNow = $nowUtc;
        $nowUtc ??= CarbonImmutable::now('UTC');
        $startedAt = $this->seconds();

        if (! $this->startRun($nowUtc)) {
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

            while (($users = $this->usersAfter($afterId, static::USERS_PER_CHUNK)) !== []) {
                $isFullChunk = count($users) === static::USERS_PER_CHUNK;
                $users = array_values(array_filter($users, fn (array $user): bool => (int) $user['id'] <= $upTo));
                if ($users === []) {
                    break;
                }

                $afterId = (int) end($users)['id'];
                $settings = $this->settingsFor(array_column($users, 'id'));

                // Who is due now, and since when their current period started.
                $since = [];
                foreach ($users as $user) {
                    $periodStart = $this->periodStartIfDue($settings[$user['id']], $nowUtc);
                    if ($periodStart !== null) {
                        $since[$user['id']] = $periodStart;
                    }
                }

                $alreadySent = $this->sentSince($since);

                foreach (array_keys($since) as $userId) {
                    if (isset($alreadySent[$userId])) {
                        continue;
                    }

                    if ($this->seconds() - $startedAt > static::MAX_RUN_SECONDS) {
                        Log::info($this->runLabel().': run reached '.static::MAX_RUN_SECONDS.' seconds; the next run continues from here.');
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
     * Builds and sends one person's summary, at most once per period even when runs overlap. The
     * slow part (building) happens first, without a lock. Then, under the person's lock, the inbox
     * is checked again and the row written; that is all the lock covers, so it can't expire while
     * held. Email and push follow once the row exists: any other run now finds the row.
     *
     * @param  string  $periodStartUtc  When the person's current period started (UTC).
     * @param  CarbonImmutable  $nowUtc  The run's time; the summary is built for it.
     * @param  array<string, mixed>  $mine  The person's settings, from {@see settingsFor()}.
     * @return bool Whether a summary went out.
     */
    private function sendOnce(int $userId, string $periodStartUtc, CarbonImmutable $nowUtc, array $mine): bool
    {
        try {
            $summary = $this->buildFor($userId, $nowUtc, $mine);
            if ($summary === null) {
                return false;
            }

            // The run may have been busy for minutes: still the same period, still inside the
            // window? Otherwise this summary is stale.
            $sendNow = $this->currentTime();
            if ($this->periodStartIfDue($mine, $sendNow) !== $periodStartUtc) {
                return false;
            }

            $user = $this->activeUser($userId);
            if ($user === null) {
                return false;
            }

            $lock = $this->sendLock($userId);
            if (! $lock->get()) {
                return false; // another run is recording this person's summary right now
            }

            try {
                if (isset($this->sentSince([$userId => $periodStartUtc])[$userId])) {
                    return false;
                }

                $this->record($user, $summary, $sendNow);
            } finally {
                $lock->release();
            }

            $this->deliver($user, $summary, $mine);

            return true;
        } catch (\Throwable $e) {
            Log::warning($this->label().' failed for user '.$userId.': '.$e->getMessage());

            return false;
        }
    }

    /**
     * Runs before anything is sent (a heartbeat, a workspace switch).
     *
     * @return bool False to send nothing in this run.
     */
    abstract protected function startRun(CarbonImmutable $nowUtc): bool;

    /**
     * Active users after an id, in id order.
     *
     * @return list<array{id: int|string}>
     */
    abstract protected function usersAfter(int $afterId, int $limit): array;

    /**
     * Each user's settings for this kind (whatever {@see periodStartIfDue()}, {@see buildFor()}
     * and {@see deliver()} need), keyed by user id.
     *
     * @param  array<int, int|string>  $userIds
     * @return array<int, array<string, mixed>>
     */
    abstract protected function settingsFor(array $userIds): array;

    /**
     * When the person's current period started (UTC, 'Y-m-d H:i:s'), if their summary is due at
     * $nowUtc; null when it isn't due. Two calls in the same period return the same value.
     *
     * @param  array<string, mixed>  $mine
     */
    abstract protected function periodStartIfDue(array $mine, CarbonImmutable $nowUtc): ?string;

    /**
     * Which of these people already have this kind's inbox row since their period start.
     *
     * @param  array<int, string>  $sinceByUser  user id => period start (UTC)
     * @return array<int, true>
     */
    abstract protected function sentSince(array $sinceByUser): array;

    /**
     * The person's summary, or null when there's nothing to send.
     *
     * @param  array<string, mixed>  $mine
     * @return array<string, mixed>|null
     */
    abstract protected function buildFor(int $userId, CarbonImmutable $nowUtc, array $mine): ?array;

    /**
     * The recipient (still active), or null.
     *
     * @return array<string, mixed>|null
     */
    abstract protected function activeUser(int $userId): ?array;

    /**
     * Writes the inbox row, the record that this period's summary went out. A failure must throw:
     * then nothing is delivered and the person stays due for the next run.
     *
     * @param  array<string, mixed>  $user
     * @param  array<string, mixed>  $summary
     */
    abstract protected function record(array $user, array $summary, CarbonImmutable $sendNowUtc): void;

    /**
     * Email and push, after the inbox row is written.
     *
     * @param  array<string, mixed>  $user
     * @param  array<string, mixed>  $summary
     * @param  array<string, mixed>  $mine
     */
    abstract protected function deliver(array $user, array $summary, array $mine): void;

    /** Where a run that reached its time limit stopped (a cache key). */
    abstract protected function cursorKey(): string;

    /** The cache key of one person's send lock. */
    abstract protected function lockKey(int $userId): string;

    /** How log lines name one summary of this kind ("Morning summary failed for user 7: …"). */
    abstract protected function label(): string;

    /** How log lines name a run of this kind ("Morning summaries: run reached …"). */
    abstract protected function runLabel(): string;

    /** The last user id a run handled before it ran out of time; 0 when the last run finished. */
    protected function loadCursor(): int
    {
        return (int) Cache::get($this->cursorKey(), 0);
    }

    protected function saveCursor(int $lastHandledUserId): void
    {
        Cache::put($this->cursorKey(), max(0, $lastHandledUserId), CarbonImmutable::now()->addDay());
    }

    protected function clearCursor(): void
    {
        Cache::forget($this->cursorKey());
    }

    /** One person's send lock, in the default cache store (the one the scheduler's locks use). */
    protected function sendLock(int $userId): Lock
    {
        return Cache::lock($this->lockKey($userId), static::SEND_LOCK_SECONDS);
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
}
