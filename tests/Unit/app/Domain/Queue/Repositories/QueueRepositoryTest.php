<?php

namespace Unit\app\Domain\Queue\Repositories;

use Illuminate\Database\SQLiteConnection;
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
 */
class QueueRepositoryTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private SQLiteConnection $connection;

    private QueueRepository $queueRepo;

    protected function setUp(): void
    {
        parent::setUp();

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
}
