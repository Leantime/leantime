<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;

/**
 * The reminder job ('reminders:dispatch', every five minutes, never two at once).
 *
 * A person's morning summary is due from their summary time until {@see WINDOW_MINUTES} after
 * it, in their timezone. A run that was missed catches up on the next one inside that window;
 * after it, nothing goes out that day: a "this morning" summary in the afternoon is noise.
 *
 * The inbox is the record of what was sent. A summary writes its inbox row first, then the email
 * and push. A person who already has a summary row since their local midnight gets nothing more
 * that day. The job runs under the scheduler's overlap lock (and on one server, where the cache
 * can share locks), so the check and the write can't race.
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
        $nowUtc ??= CarbonImmutable::now('UTC');
        $this->settings->recordHeartbeat($nowUtc->format('Y-m-d H:i:s'));

        if (! $this->settings->workspaceEnabled()) {
            return 0;
        }

        $sent = 0;
        $afterId = 0;

        while (($users = $this->reminders->activeUsers($afterId, self::USERS_PER_CHUNK)) !== []) {
            $afterId = (int) end($users)['id'];
            $settings = $this->settings->forUsers(array_column($users, 'id'));

            // Who is due: summary on, local time inside the window after their summary time.
            $since = [];
            foreach ($users as $user) {
                $mine = $settings[$user['id']];
                if (! $mine['summary']) {
                    continue;
                }

                $localNow = $nowUtc->setTimezone($mine['timezone']);
                $minutesPast = self::minutesOfDay($localNow->format('H:i')) - self::minutesOfDay($mine['summaryTime']);
                if ($minutesPast < 0 || $minutesPast > self::WINDOW_MINUTES) {
                    continue;
                }

                $since[$user['id']] = $localNow->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
            }

            $alreadySent = $this->reminders->summariesSentSince($since);

            foreach (array_keys($since) as $userId) {
                if (isset($alreadySent[$userId])) {
                    continue;
                }

                try {
                    $summary = $this->builder->build($userId, $nowUtc, $settings[$userId]['timezone'], $settings[$userId]['taskPreset']);
                    if ($summary['isEmpty']) {
                        continue;
                    }

                    $user = $this->reminders->activeUser($userId);
                    if ($user === null) {
                        continue;
                    }

                    $this->notifier->sendSummary($user, $summary, $nowUtc, $settings[$userId]['summaryTime']);
                    $sent++;
                } catch (\Throwable $e) {
                    Log::warning('Morning summary failed for user '.$userId.': '.$e->getMessage());
                }
            }

            if (count($users) < self::USERS_PER_CHUNK) {
                break;
            }
        }

        return $sent;
    }

    private static function minutesOfDay(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return $hours * 60 + $minutes;
    }
}
