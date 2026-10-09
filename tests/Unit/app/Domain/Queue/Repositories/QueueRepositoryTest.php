<?php

namespace Unit\app\Domain\Queue\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\Workers;
use Leantime\Domain\Users\Repositories\Users as UserRepo;
use Unit\TestCase;

/**
 * listMessageInQueue() over a real zp_queue table (in-memory SQLite). Without a limit it keeps
 * the user, project, date order its existing callers rely on. With a limit it takes the oldest
 * rows of the channel in the query itself, so a runner never loads a whole backlog to use a few.
 *
 * deleteMessageInQueue() reports true only when every hash it was given removed a row, so a
 * worker holding a stale listing can tell a row it claimed from one another worker already took.
 *
 * addMessageToQueue() writes one row per call, even for a message identical to one queued in the
 * same second: a row's id is its own, never its content. It stamps the row in UTC, so rows queued
 * by requests in different timezones still list in the order they were queued. An insert that
 * fails never throws, so a message that cannot be queued does not stop the notifications sent
 * beside it, and it is logged without its content: the database exception's message is the SQL
 * with the subject and payload filled in.
 */
class QueueRepositoryTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private SQLiteConnection $connection;

    private QueueRepository $queueRepo;

    /**
     * PHP's default timezone as the test started, restored after it.
     */
    private string $defaultTimezone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultTimezone = date_default_timezone_get();

        $this->connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        // The columns SchemaBuilder::createQueueTable() creates.
        $this->connection->statement('CREATE TABLE zp_queue (
            msghash VARCHAR(50) NOT NULL PRIMARY KEY,
            channel VARCHAR(255) NULL,
            userId INTEGER NOT NULL,
            subject VARCHAR(255) NULL,
            message TEXT NOT NULL,
            thedate DATETIME NOT NULL,
            projectId INTEGER NOT NULL
        )');

        $this->queueRepo = new QueueRepository(
            $this->make(DbCore::class, ['getConnection' => fn () => $this->connection]),
            $this->make(UserRepo::class),
        );

        // The oldest rows belong to the highest user ids, and two of them share a timestamp.
        $this->insertRow('c-hash', Workers::WEBHOOKS, userId: 9, thedate: '2026-09-30 10:00:00');
        $this->insertRow('a-hash', Workers::WEBHOOKS, userId: 8, thedate: '2026-09-30 10:00:00');
        $this->insertRow('b-hash', Workers::WEBHOOKS, userId: 7, thedate: '2026-09-30 10:00:01');
        $this->insertRow('d-hash', Workers::WEBHOOKS, userId: 1, thedate: '2026-09-30 10:00:02');
        // Older than all of them, but on another channel.
        $this->insertRow('default-hash', Workers::DEFAULT, userId: 1, thedate: '2026-09-30 09:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        date_default_timezone_set($this->defaultTimezone);

        parent::tearDown();
    }

    private function insertRow(string $msghash, Workers $channel, int $userId, string $thedate): void
    {
        $this->connection->table('zp_queue')->insert([
            'msghash' => $msghash,
            'channel' => $channel->value,
            'userId' => $userId,
            'subject' => 'subject',
            'message' => 'message',
            'thedate' => $thedate,
            'projectId' => 5,
        ]);
    }

    public function test_a_limited_listing_returns_the_oldest_rows_of_the_channel_with_msghash_breaking_ties(): void
    {
        $this->connection->enableQueryLog();

        $rows = $this->queueRepo->listMessageInQueue(Workers::WEBHOOKS, limit: 3);

        $this->assertSame(['a-hash', 'c-hash', 'b-hash'], array_column($rows, 'msghash'), 'Oldest first, whoever the recipient; the newest row waits');
        $this->assertSame(Workers::WEBHOOKS->value, $rows[0]['channel'], 'Rows come back as column arrays, as before');
        $queries = $this->connection->getQueryLog();
        $this->assertCount(1, $queries);
        $this->assertMatchesRegularExpression('/\blimit 3\b/i', $queries[0]['query'], 'The database applies the limit, so a backlog is never loaded whole');
    }

    public function test_a_limit_above_the_backlog_returns_every_row_of_the_channel_oldest_first(): void
    {
        $rows = $this->queueRepo->listMessageInQueue(Workers::WEBHOOKS, limit: 10);

        $this->assertSame(['a-hash', 'c-hash', 'b-hash', 'd-hash'], array_column($rows, 'msghash'));
    }

    public function test_an_unlimited_listing_keeps_the_user_project_date_order_of_existing_callers(): void
    {
        $this->connection->enableQueryLog();

        $rows = $this->queueRepo->listMessageInQueue(Workers::WEBHOOKS);

        $this->assertSame(['d-hash', 'b-hash', 'a-hash', 'c-hash'], array_column($rows, 'msghash'));
        $this->assertDoesNotMatchRegularExpression('/\blimit\b/i', $this->connection->getQueryLog()[0]['query']);
    }

    public function test_only_the_delete_that_removes_a_row_reports_true_so_a_stale_hash_is_never_a_claim(): void
    {
        $this->assertTrue($this->queueRepo->deleteMessageInQueue('a-hash'), 'The call that removes the row owns it');
        $this->assertFalse($this->queueRepo->deleteMessageInQueue('a-hash'), 'A row another worker already deleted is not claimed again');
        $this->assertSame(0, $this->connection->table('zp_queue')->where('msghash', 'a-hash')->count());
    }

    public function test_deleting_several_hashes_reports_false_when_any_was_already_gone_and_still_deletes_the_rest(): void
    {
        $this->queueRepo->deleteMessageInQueue('a-hash');

        $this->assertFalse($this->queueRepo->deleteMessageInQueue(['a-hash', 'b-hash']));
        $this->assertSame(['c-hash', 'd-hash', 'default-hash'], $this->connection->table('zp_queue')->orderBy('msghash')->pluck('msghash')->all());
        $this->assertTrue($this->queueRepo->deleteMessageInQueue(['c-hash', 'd-hash']), 'Every hash removed a row');
    }

    public function test_identical_messages_queued_in_the_same_second_each_keep_their_own_row_and_claim(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 08:00:00', 'UTC'));
        $message = serialize(['subject' => 'Ada updated "Ship it"', 'recipientIds' => [7]]);

        $this->queueRepo->addMessageToQueue(Workers::WEBHOOKS, 'Some\\Job', $message, userId: 7, projectId: 5);
        $this->queueRepo->addMessageToQueue(Workers::WEBHOOKS, 'Some\\Job', $message, userId: 7, projectId: 5);

        $rows = $this->connection->table('zp_queue')->where('subject', 'Some\\Job')->get()->map(fn ($row) => (array) $row)->all();
        $this->assertCount(2, $rows, 'The second message is not dropped as a duplicate of the first');
        [$first, $second] = $rows;
        $this->assertNotSame($first['msghash'], $second['msghash']);
        $this->assertLessThanOrEqual(50, strlen($first['msghash']), 'Fits msghash VARCHAR(50), which SQLite does not enforce');
        $this->assertSame(array_diff_key($first, ['msghash' => true]), array_diff_key($second, ['msghash' => true]), 'Same channel, recipient, project, second and payload');

        $this->assertTrue($this->queueRepo->deleteMessageInQueue($first['msghash']), 'Claiming one row');
        $this->assertTrue($this->queueRepo->deleteMessageInQueue($second['msghash']), 'leaves the other to be claimed on its own');
    }

    public function test_rows_queued_under_different_timezones_are_stored_in_utc_and_listed_in_the_order_they_were_queued(): void
    {
        // A request in Tokyo queues first, at 10:00 UTC (19:00 there); one in Los Angeles a minute later (03:01 there).
        date_default_timezone_set('Asia/Tokyo');
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 10:00:00', 'UTC'));
        $this->queueRepo->addMessageToQueue(Workers::WEBHOOKS, 'Some\\Job', 'queued first, from Tokyo', userId: 2, projectId: 5);

        date_default_timezone_set('America/Los_Angeles');
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 10:01:00', 'UTC'));
        $this->queueRepo->addMessageToQueue(Workers::WEBHOOKS, 'Some\\Job', 'queued later, from Los Angeles', userId: 3, projectId: 5);

        // Both are older than every setUp row, so they make up the batch of two.
        $rows = $this->queueRepo->listMessageInQueue(Workers::WEBHOOKS, limit: 2);

        $this->assertSame(['queued first, from Tokyo', 'queued later, from Los Angeles'], array_column($rows, 'message'), 'Oldest first by when they were queued, whatever the request timezone');
        $this->assertSame(['2026-07-01 10:00:00', '2026-07-01 10:01:00'], array_column($rows, 'thedate'), 'Stored in UTC');
    }

    public function test_a_failed_insert_is_logged_without_its_content_and_is_neither_thrown_nor_reported(): void
    {
        $subject = 'Some\\Job secret-subject-marker';
        $message = serialize([
            'message' => 'Ada updated "secret-message-marker"',
            'url' => 'https://leantime.example.com/tickets/42?token=secret-link-token',
        ]);
        $this->connection->statement('DROP TABLE zp_queue');

        try {
            $this->connection->table('zp_queue')->insert(['subject' => $subject, 'message' => $message]);
            $this->fail('The insert must fail for this test to mean anything');
        } catch (QueryException $e) {
            $this->assertStringContainsString('secret-link-token', $e->getMessage(), 'The failure under test carries the payload in its message');
        }

        // report() hands the exception, message and all, to the handler, which logs it.
        $exceptionHandler = new class implements ExceptionHandler
        {
            /** @var array<int, \Throwable> */
            public array $reported = [];

            public function report(\Throwable $e)
            {
                $this->reported[] = $e;
            }

            public function shouldReport(\Throwable $e)
            {
                return true;
            }

            public function render($request, \Throwable $e)
            {
                throw $e;
            }

            public function renderForConsole($output, \Throwable $e) {}
        };
        $this->app->instance(ExceptionHandler::class, $exceptionHandler);
        $logged = [];
        foreach (['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'] as $level) {
            Log::shouldReceive($level)->andReturnUsing(function ($logMessage, $context = []) use (&$logged, $level) {
                $logged[] = ['level' => $level, 'message' => $logMessage, 'context' => $context];
            });
        }

        // Never throws: a webhook that cannot be queued must not stop the email, push or in-app notification.
        $this->queueRepo->addMessageToQueue(Workers::WEBHOOKS, $subject, $message, userId: 7, projectId: 5);

        $this->assertSame([], array_map('get_class', $exceptionHandler->reported), 'The exception is never reported, so its message never reaches the handler');
        $this->assertSame([[
            'level' => 'error',
            'message' => 'Queue message could not be saved',
            'context' => [
                'channel' => Workers::WEBHOOKS->value,
                'userId' => 7,
                'projectId' => 5,
                'exception' => QueryException::class,
                'sqlState' => 'HY000',
            ],
        ]], $logged, 'Logged once, by channel, recipient, project, exception class and SQLSTATE only');
        $mustNeverBeLogged = [
            // The subject and payload.
            'secret-subject-marker', 'secret-message-marker', 'secret-link-token', 'leantime.example.com',
            // The exception message and the SQL in it.
            'no such table', 'insert into', 'SQLSTATE[', 'zp_queue',
        ];
        foreach ($logged as $entry) {
            $line = $entry['message'].' '.json_encode($entry['context'], JSON_UNESCAPED_SLASHES);
            foreach ($mustNeverBeLogged as $leak) {
                $this->assertStringNotContainsString($leak, $line);
            }
        }
    }

    /**
     * An email's msghash is the same message to the same user in the same second, so a second
     * identical queueing (a double-submitted ticket patch) is the same notification: it is skipped
     * instead of failing on the primary key and reporting the SQL — subject and body — as an error.
     */
    public function test_queueing_the_same_email_twice_in_one_second_keeps_one_row_and_reports_nothing(): void
    {
        CarbonImmutable::setTestNow('2026-10-08 12:00:00');
        $queueRepo = new QueueRepository(
            $this->make(DbCore::class, ['getConnection' => fn () => $this->connection]),
            $this->make(UserRepo::class, ['getUser' => fn () => ['id' => 3, 'username' => 'person@example.com']]),
        );
        Log::shouldReceive('error')->never();

        $queueRepo->queueMessageToUsers([3], 'Ticket #12 was updated', 'Ticket updated', 5);
        $queueRepo->queueMessageToUsers([3], 'Ticket #12 was updated', 'Ticket updated', 5);

        $this->assertSame(1, $this->connection->table('zp_queue')->where('channel', Workers::EMAILS->value)->count());
    }
}
