<?php

namespace Unit\app\Domain\Notifications\Services;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Notifications\Jobs\DeliverPersonalWebhooks;
use Leantime\Domain\Notifications\Services\WebhookQueue;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\Workers;
use Leantime\Domain\Users\Repositories\Users as UserRepo;
use Unit\TestCase;

/**
 * The personal-webhook queue runner: drains its own zp_queue channel (WEBHOOKS), a bounded
 * batch per scheduler run, through the one fixed DeliverPersonalWebhooks job. Every row it
 * takes leaves the queue whatever happens to it, so no row can block the rows behind it, and
 * the DEFAULT channel is never read or touched. A row runs only when this run's delete removed
 * it, so two runs holding the same rows deliver each one once.
 *
 * Runs the real WebhookQueue over a faked zp_queue table and a recording job stub; the races
 * between two runs use the real repository over an in-memory SQLite zp_queue instead.
 */
class WebhookQueueTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * The fake zp_queue table, keyed by msghash.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $queueTable = [];

    /**
     * Channels the runner listed, in order.
     *
     * @var array<int, string>
     */
    private array $listedChannels = [];

    /**
     * Row limits the runner asked the repository for, in order.
     *
     * @var array<int, int|null>
     */
    private array $requestedLimits = [];

    /**
     * Payloads the job was handed, in order.
     *
     * @var array<int, mixed>
     */
    private array $handledPayloads = [];

    /**
     * Warning log lines, message plus JSON context.
     *
     * @var array<int, string>
     */
    private array $warnings = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->queueTable = [];
        $this->listedChannels = [];
        $this->requestedLimits = [];
        $this->handledPayloads = [];
        $this->warnings = [];
        UnexpectedQueueJob::$constructed = 0;
    }

    /**
     * Adds a row to the fake zp_queue table.
     */
    private function queueRow(string $msghash, string $thedate, mixed $payload, Workers $channel = Workers::WEBHOOKS, string $subject = DeliverPersonalWebhooks::class, int $userId = 7): void
    {
        $this->queueTable[$msghash] = [
            'msghash' => $msghash,
            'channel' => $channel->value,
            'userId' => $userId,
            'subject' => $subject,
            'message' => is_string($payload) ? $payload : serialize($payload),
            'thedate' => $thedate,
            'projectId' => 5,
        ];
    }

    /**
     * The runner over the fake table. listMessageInQueue() and deleteMessageInQueue() answer like
     * the real repository (QueueRepositoryTest pins both): one channel; without a limit every row
     * ordered by userId, projectId, thedate; with a limit that many rows, oldest first, msghash
     * breaking ties. A delete reports true only when every hash removed a row.
     *
     * @param  \Throwable|null  $failOnPayload  Thrown by the job for the payload whose 'n' is 1 when given.
     */
    private function makeRunner(?\Throwable $failOnPayload = null): WebhookQueue
    {
        $queueRepo = $this->make(QueueRepository::class, [
            'listMessageInQueue' => function (Workers $channel, mixed $recipients = null, int $projectId = 0, ?int $limit = null) {
                $this->listedChannels[] = $channel->value;
                $this->requestedLimits[] = $limit;
                $rows = array_values(array_filter($this->queueTable, fn (array $row) => $row['channel'] === $channel->value));

                if ($limit === null) {
                    usort($rows, fn (array $a, array $b) => [$a['userId'], $a['projectId'], $a['thedate']] <=> [$b['userId'], $b['projectId'], $b['thedate']]);

                    return $rows;
                }

                usort($rows, fn (array $a, array $b) => [$a['thedate'], $a['msghash']] <=> [$b['thedate'], $b['msghash']]);

                return array_slice($rows, 0, $limit);
            },
            'deleteMessageInQueue' => function (string|array $msghashes) {
                $everyHashRemovedARow = true;
                foreach ((array) $msghashes as $msghash) {
                    if (! isset($this->queueTable[$msghash])) {
                        $everyHashRemovedARow = false;
                    }
                    unset($this->queueTable[$msghash]);
                }

                return $everyHashRemovedARow;
            },
        ]);

        $job = $this->make(DeliverPersonalWebhooks::class, [
            'handle' => function (mixed $payload) use ($failOnPayload) {
                $this->handledPayloads[] = $payload;
                if ($failOnPayload !== null && ($payload['n'] ?? null) === 1) {
                    throw $failOnPayload;
                }

                return true;
            },
        ]);

        return new WebhookQueue($queueRepo, $job);
    }

    private function recordWarnings(): void
    {
        Log::shouldReceive('warning')->andReturnUsing(function ($message, $context = []) {
            $this->warnings[] = $message.' '.json_encode($context);
        });
    }

    /**
     * An in-memory SQLite zp_queue with one WEBHOOKS row per payload, a second apart in key order.
     *
     * @param  array<string, array<string, mixed>>  $payloadsByMsghash
     */
    private function sqliteQueueTable(array $payloadsByMsghash): SQLiteConnection
    {
        $connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        // The columns SchemaBuilder::createQueueTable() creates.
        $connection->statement('CREATE TABLE zp_queue (
            msghash VARCHAR(50) NOT NULL PRIMARY KEY,
            channel VARCHAR(255) NULL,
            userId INTEGER NOT NULL,
            subject VARCHAR(255) NULL,
            message TEXT NOT NULL,
            thedate DATETIME NOT NULL,
            projectId INTEGER NOT NULL
        )');

        $second = 0;
        foreach ($payloadsByMsghash as $msghash => $payload) {
            $connection->table('zp_queue')->insert([
                'msghash' => $msghash,
                'channel' => Workers::WEBHOOKS->value,
                'userId' => 7,
                'subject' => DeliverPersonalWebhooks::class,
                'message' => serialize($payload),
                'thedate' => sprintf('2026-09-30 10:00:%02d', $second++),
                'projectId' => 5,
            ]);
        }

        return $connection;
    }

    /**
     * A runner over the real QueueRepository on the given connection, with a job stub that records
     * each payload and then calls $duringDelivery, if given.
     */
    private function makeSqliteRunner(SQLiteConnection $connection, ?\Closure $duringDelivery = null): WebhookQueue
    {
        $queueRepo = new QueueRepository(
            $this->make(DbCore::class, ['getConnection' => fn () => $connection]),
            $this->make(UserRepo::class),
        );

        $job = $this->make(DeliverPersonalWebhooks::class, [
            'handle' => function (mixed $payload) use ($duringDelivery) {
                $this->handledPayloads[] = $payload;
                if ($duringDelivery !== null) {
                    $duringDelivery();
                }

                return true;
            },
        ]);

        return new WebhookQueue($queueRepo, $job);
    }

    public function test_every_queued_row_runs_through_the_personal_webhook_job_and_leaves_the_queue(): void
    {
        $this->queueRow('row-1', '2026-09-30 10:00:00', ['n' => 1]);
        $this->queueRow('row-2', '2026-09-30 10:00:01', ['n' => 2]);
        $this->queueRow('row-3', '2026-09-30 10:00:02', ['n' => 3]);

        $this->makeRunner()->processQueue();

        $this->assertSame([['n' => 1], ['n' => 2], ['n' => 3]], $this->handledPayloads, 'Several rows are delivered in one run');
        $this->assertSame([], $this->queueTable);
    }

    public function test_the_default_queue_is_never_read_or_touched(): void
    {
        // A DEFAULT row the DefaultWorker keeps failing on sits at the head of its own channel.
        $this->queueRow('stuck-default', '2026-09-30 09:00:00', ['stuck' => true], Workers::DEFAULT, 'Some\\Other\\FailingJob', 1);
        $this->queueRow('row-1', '2026-09-30 10:00:00', ['n' => 1]);
        $this->queueRow('row-2', '2026-09-30 10:00:01', ['n' => 2]);

        $this->makeRunner()->processQueue();

        $this->assertSame(['webhooks'], $this->listedChannels, 'Only the WEBHOOKS channel is listed');
        $this->assertSame([['n' => 1], ['n' => 2]], $this->handledPayloads, 'A stuck DEFAULT queue cannot hold back webhooks');
        $this->assertSame(['stuck-default'], array_keys($this->queueTable), 'The DEFAULT row is left alone');
    }

    public function test_at_most_ten_rows_run_per_invocation_oldest_first(): void
    {
        // Twelve rows from recipients 1..12, oldest from the highest user id: the unlimited listing
        // orders by userId first, so a newer row of a low user id must not jump ahead of an older one.
        for ($n = 1; $n <= 12; $n++) {
            $this->queueRow("row-{$n}", sprintf('2026-09-30 10:00:%02d', $n), ['n' => $n], userId: 13 - $n);
        }
        $runner = $this->makeRunner();

        $runner->processQueue();

        $this->assertSame([10], $this->requestedLimits, 'The batch is bounded in the query, never by loading the whole backlog');
        $this->assertSame(range(1, 10), array_column($this->handledPayloads, 'n'), 'The ten oldest rows run first');
        $this->assertSame(['row-11', 'row-12'], array_keys($this->queueTable), 'The rest wait for the next run');

        $runner->processQueue();

        $this->assertSame([10, 10], $this->requestedLimits);
        $this->assertSame(range(1, 12), array_column($this->handledPayloads, 'n'));
        $this->assertSame([], $this->queueTable);
    }

    public function test_a_failing_row_is_dropped_logged_by_class_only_and_never_blocks_later_rows(): void
    {
        $this->recordWarnings();
        $this->queueRow('row-1', '2026-09-30 10:00:00', ['n' => 1]);
        $this->queueRow('row-2', '2026-09-30 10:00:01', ['n' => 2]);
        $runner = $this->makeRunner(new \RuntimeException('cURL error 7 for https://1.1.1.1/hooks/secret-token?sig=secret-sig'));

        $runner->processQueue();
        $runner->processQueue();

        $this->assertSame([['n' => 1], ['n' => 2]], $this->handledPayloads, 'The failing row runs once and is never retried');
        $this->assertSame([], $this->queueTable);
        $this->assertCount(1, $this->warnings);
        $this->assertStringContainsString('RuntimeException', $this->warnings[0]);
        $this->assertStringNotContainsString('secret', $this->warnings[0], 'Exception messages can carry the endpoint and its secret');
    }

    public function test_a_row_naming_any_other_job_class_is_dropped_without_resolving_that_class(): void
    {
        $this->recordWarnings();
        $this->queueRow('foreign', '2026-09-30 10:00:00', ['n' => 1], subject: UnexpectedQueueJob::class);
        $this->queueRow('row-2', '2026-09-30 10:00:01', ['n' => 2]);

        $this->makeRunner()->processQueue();

        $this->assertSame(0, UnexpectedQueueJob::$constructed, 'The row subject is never resolved from the container');
        $this->assertSame([['n' => 2]], $this->handledPayloads, 'Only the fixed personal webhook job runs');
        $this->assertSame([], $this->queueTable, 'The foreign row is deleted, not left to block the channel');
        $this->assertCount(1, $this->warnings);
        $this->assertStringNotContainsString('UnexpectedQueueJob', $this->warnings[0]);
    }

    public function test_an_unreadable_row_reaches_the_job_as_an_empty_payload_and_leaves_the_queue(): void
    {
        $this->queueRow('garbage', '2026-09-30 10:00:00', 'not serialized');
        $this->queueRow('object', '2026-09-30 10:00:01', 'O:8:"stdClass":0:{}');

        $this->makeRunner()->processQueue();

        $this->assertSame([], $this->queueTable);
        $this->assertSame([], $this->handledPayloads[0], 'An unreadable message is handed over as an empty payload for the job to reject');
        $this->assertNotInstanceOf(\stdClass::class, $this->handledPayloads[1], 'Queued messages never instantiate objects');
    }

    public function test_an_empty_queue_is_a_no_op(): void
    {
        $this->makeRunner()->processQueue();

        $this->assertSame([], $this->handledPayloads);
        $this->assertSame(['webhooks'], $this->listedChannels);
    }

    public function test_two_runs_that_listed_the_same_row_post_it_once(): void
    {
        $connection = $this->sqliteQueueTable(['row-1' => ['n' => 1]]);
        $firstRun = $this->makeSqliteRunner($connection);
        $secondRun = $this->makeSqliteRunner($connection);

        // The second run goes the moment the first is about to delete: both hold the same listing.
        $secondRunWent = false;
        $connection->beforeExecuting(function (string $query) use (&$secondRunWent, $secondRun) {
            if ($secondRunWent || ! str_starts_with(strtolower($query), 'delete')) {
                return;
            }
            $secondRunWent = true;
            $secondRun->processQueue();
        });

        $firstRun->processQueue();

        $this->assertTrue($secondRunWent);
        $this->assertSame([['n' => 1]], $this->handledPayloads, 'Only the run whose delete removed the row posts it');
        $this->assertSame(0, $connection->table('zp_queue')->count());
    }

    public function test_a_run_started_while_another_is_still_delivering_never_reposts_its_rows(): void
    {
        // The overlap lock expired mid-run (a slow DNS lookup, say) and the next minute's run started.
        $connection = $this->sqliteQueueTable(['row-1' => ['n' => 1], 'row-2' => ['n' => 2]]);
        $secondRun = $this->makeSqliteRunner($connection);
        $secondRunWent = false;
        $firstRun = $this->makeSqliteRunner($connection, function () use (&$secondRunWent, $secondRun) {
            if ($secondRunWent) {
                return;
            }
            $secondRunWent = true;
            $secondRun->processQueue();
        });

        $firstRun->processQueue();

        $this->assertTrue($secondRunWent);
        $this->assertSame([['n' => 1], ['n' => 2]], $this->handledPayloads, 'row-2, still in the first run\'s stale listing, is posted once, by the second run');
        $this->assertSame(0, $connection->table('zp_queue')->count());
    }

    /**
     * The scheduler contract, read from the listener Notifications/register.php actually
     * registers: the WEBHOOKS queue runs every minute on its own named, non-overlapping event,
     * whose overlap lock a crashed run cannot hold for longer than ten minutes.
     */
    public function test_the_scheduler_runs_the_webhook_queue_every_minute_without_overlapping(): void
    {
        $registerFile = realpath(APP_ROOT.'/app/Domain/Notifications/register.php');
        $cronListeners = array_filter(
            EventDispatcher::getEventRegistry()['leantime.core.console.consolekernel.schedule.cron'] ?? [],
            fn (array $entry) => $entry['listener'] instanceof \Closure
                && realpath((new \ReflectionFunction($entry['listener']))->getFileName()) === $registerFile
        );
        $this->assertCount(1, $cronListeners, 'Notifications/register.php schedules the webhook queue');

        $schedule = new Schedule;
        (array_values($cronListeners)[0]['listener'])(['schedule' => $schedule]);

        $events = array_values(array_filter($schedule->events(), fn ($event) => $event->description === 'queue:webhooks'));
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(CallbackEvent::class, $event);
        $this->assertSame('* * * * *', $event->expression, 'Every minute');
        $this->assertTrue($event->withoutOverlapping, 'A slow run is never joined by a second one');
        $this->assertSame(10, $event->expiresAt, 'A run that crashed holding the lock stalls deliveries for ten minutes, not the default 24 hours');

        $runs = 0;
        app()->instance(WebhookQueue::class, $this->make(WebhookQueue::class, [
            'processQueue' => function () use (&$runs) {
                $runs++;
            },
        ]));
        $event->run(app());

        $this->assertSame(1, $runs, 'The scheduled event drains the webhook queue');
    }
}

/**
 * A job class a WEBHOOKS row might name. The runner must never build it.
 */
class UnexpectedQueueJob
{
    public static int $constructed = 0;

    public function __construct()
    {
        self::$constructed++;
    }

    public function handle(mixed $payload): bool
    {
        return true;
    }
}
