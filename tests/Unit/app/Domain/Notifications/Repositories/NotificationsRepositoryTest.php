<?php

namespace Unit\app\Domain\Notifications\Repositories;

use Illuminate\Database\SQLiteConnection;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationsRepository;
use Unit\TestCase;

/**
 * latestByEntity() over a real zp_notifications table (in-memory SQLite): for each entity asked
 * about, the newest datetime among the user's READ rows and among the UNREAD ones, and nothing for
 * entities with no notification at all.
 */
class NotificationsRepositoryTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private SQLiteConnection $connection;

    private NotificationsRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        $this->connection->statement('CREATE TABLE zp_notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            userId INTEGER NOT NULL,
            read INTEGER NULL,
            type VARCHAR(45) NULL,
            module VARCHAR(45) NULL,
            moduleId INTEGER NULL,
            datetime DATETIME NULL,
            url VARCHAR(255) NULL,
            authorId INTEGER NULL,
            message TEXT NULL
        )');

        $this->repo = new NotificationsRepository(
            $this->make(DbCore::class, ['getConnection' => fn () => $this->connection]),
        );
    }

    private function insert(int $userId, ?int $read, string $module, int $moduleId, string $datetime): void
    {
        $this->connection->table('zp_notifications')->insert([
            'userId' => $userId,
            'read' => $read,
            'type' => 'projectUpdate',
            'module' => $module,
            'moduleId' => $moduleId,
            'datetime' => $datetime,
            'url' => 'https://app.example.com/',
            'authorId' => 9,
            'message' => 'm',
        ]);
    }

    public function test_returns_the_newest_read_and_unread_datetimes_per_entity_and_skips_the_rest(): void
    {
        $this->insert(2, 1, 'tickets', 12, '2026-10-30 10:00:00');
        $this->insert(2, 1, 'tickets', 12, '2026-10-30 11:00:00'); // newest read
        $this->insert(2, 0, 'tickets', 12, '2026-10-30 12:00:00'); // newest unread
        $this->insert(2, null, 'comments', 77, '2026-10-30 10:00:00'); // legacy NULL = unread
        $this->insert(2, 1, 'comments', 78, '2026-10-30 10:00:00'); // read, but not asked about
        $this->insert(3, 1, 'tickets', 13, '2026-10-30 10:00:00'); // another user

        $result = $this->repo->latestByEntity(2, [
            ['module' => 'tickets', 'moduleId' => 12],
            ['module' => 'comments', 'moduleId' => 77],
            ['module' => 'tickets', 'moduleId' => 13],
        ]);

        ksort($result);
        $this->assertSame([
            'comments:77' => ['read' => null, 'unread' => '2026-10-30 10:00:00'],
            'tickets:12' => ['read' => '2026-10-30 11:00:00', 'unread' => '2026-10-30 12:00:00'],
        ], $result);
    }

    public function test_identifiers_are_quoted_by_the_grammar(): void
    {
        $this->connection->enableQueryLog();

        $this->repo->latestByEntity(2, [['module' => 'tickets', 'moduleId' => 12]]);

        $sql = $this->connection->getQueryLog()[0]['query'];
        $this->assertStringContainsString('"moduleId"', $sql, 'camelCase columns are created quoted; PostgreSQL folds unquoted ones to lower case');
        $this->assertStringContainsString('"read"', $sql);
        $this->assertStringContainsString('"datetime"', $sql);
        $this->assertStringContainsString('as "lastRead"', $sql);
        $this->assertStringContainsString('as "lastUnread"', $sql);
        $this->assertDoesNotMatchRegularExpression('/(?<=[\s(,])(moduleId|lastRead|lastUnread|datetime)(?=[\s),])/', $sql, 'no bare camelCase identifier anywhere');
    }

    public function test_no_entities_means_no_query_and_an_empty_result(): void
    {
        $this->connection->enableQueryLog();

        $this->assertSame([], $this->repo->latestByEntity(2, []));
        $this->assertSame([], $this->connection->getQueryLog());
    }

    public function test_duplicate_entities_are_asked_once(): void
    {
        $this->insert(2, 1, 'tickets', 12, '2026-10-30 10:00:00');
        $this->connection->enableQueryLog();

        $result = $this->repo->latestByEntity(2, [
            ['module' => 'tickets', 'moduleId' => 12],
            ['module' => 'tickets', 'moduleId' => '12'],
        ]);

        $this->assertSame(['tickets:12' => ['read' => '2026-10-30 10:00:00', 'unread' => null]], $result);
        $this->assertCount(1, $this->connection->getQueryLog());
        $this->assertSame(1, substr_count($this->connection->getQueryLog()[0]['query'], '"moduleId" = ?'), 'one pair in the WHERE, not two');
    }
}
