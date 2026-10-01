<?php

namespace Leantime\Domain\Notifications\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Jobs\DeliverPersonalWebhooks;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\Workers;

/**
 * WebhookQueue — drains the WEBHOOKS zp_queue channel that Webhooks::queueToUsers()
 * fills, one row per personal-webhook recipient. Scheduled every minute by
 * Notifications/register.php.
 *
 * Its own channel and runner so personal webhooks never wait on — or hold up —
 * the DEFAULT queue: DefaultWorker takes one row per run and stops at a row that
 * fails. Here each run takes up to MAX_ROWS_PER_RUN rows, oldest first, and
 * every row it takes leaves the queue whatever its outcome, so no row is ever
 * retried or blocks the rows behind it.
 *
 * Every row runs through the one fixed DeliverPersonalWebhooks job; the row's
 * subject is only compared against it, never resolved as a class.
 *
 * Intentionally carries no @api tags: only the scheduler may run deliveries.
 */
class WebhookQueue
{
    /**
     * Rows handled per run. This bounds the batch, not the run's duration: each
     * delivery can use the transport's full timeout, and the SSRF guard's DNS
     * lookups happen before that timeout starts, bounded only by the system
     * resolver. So a run can outlast the minute; the schedule's withoutOverlapping()
     * keeps the next minute from starting a second run meanwhile, and should that
     * lock expire first, processQueue()'s claim still posts each row at most once.
     */
    public const MAX_ROWS_PER_RUN = 10;

    public function __construct(
        private QueueRepository $queueRepo,
        private DeliverPersonalWebhooks $deliverJob,
    ) {}

    /**
     * Runs up to MAX_ROWS_PER_RUN queued personal-webhook rows, oldest first —
     * the repository takes that batch in the query, so a backlog is never loaded
     * whole. Each row is deleted before it runs: attempted at most once, so a row
     * that fails — or stops the process — can never block the ones behind it.
     * The delete is the claim: a row runs only if this run's delete removed it, so
     * a run whose listing went stale (another run already took the row) skips it.
     */
    public function processQueue(): void
    {
        // Oldest first, not the unlimited listing's user order, so a busy recipient can't starve the rest.
        $rows = $this->queueRepo->listMessageInQueue(Workers::WEBHOOKS, limit: self::MAX_ROWS_PER_RUN);
        if (! is_array($rows) || $rows === []) {
            return;
        }

        foreach ($rows as $row) {
            $claimedByThisRun = $this->queueRepo->deleteMessageInQueue((string) $row['msghash']);
            if (! $claimedByThisRun) {
                continue;
            }

            $this->runRow($row);
        }
    }

    /**
     * Runs one row through DeliverPersonalWebhooks. Logs by exception class
     * only: a message can carry the endpoint URL and its secret.
     *
     * @param  array<string, mixed>  $row  A zp_queue row from the WEBHOOKS channel.
     */
    private function runRow(array $row): void
    {
        if (($row['subject'] ?? null) !== DeliverPersonalWebhooks::class) {
            Log::warning('Personal webhook queue row dropped: not a personal webhook job');

            return;
        }

        try {
            $this->deliverJob->handle(safe_unserialize((string) $row['message']));
        } catch (\Throwable $e) {
            Log::warning('Personal webhook queue row failed', ['exception' => get_class($e)]);
        }
    }
}
