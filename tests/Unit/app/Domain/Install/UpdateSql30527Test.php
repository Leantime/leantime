<?php

namespace Unit\app\Domain\Install;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Install\Repositories\Install;
use Leantime\Domain\Install\Services\SchemaBuilder;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\Workers;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Setting\Services\SettingCache;
use Leantime\Domain\Users\Repositories\Users as UserRepo;
use Unit\TestCase;

/**
 * The queue runner reads one channel oldest first, msghash breaking ties, a few rows at a time
 * (Queue::listMessageInQueue with a limit). zp_queue is indexed on (channel, thedate, msghash) in
 * that order, so the read walks the index instead of scanning and sorting the whole table.
 *
 * Fresh installs get the index from SchemaBuilder. Installs at db-version 3.5.26 get it from
 * update_sql_30527 through updateDB(), which records 3.5.27 only when the index is in place.
 * Everything runs against real in-memory SQLite tables, and the query plan is SQLite's own.
 *
 * On MySQL and MariaDB, InnoDB's COMPACT and REDUNDANT row formats cannot hold the index (767-byte
 * key columns), so the migration first moves such a queue table to DYNAMIC. Those cases use
 * SqliteStandInForInstalledDatabase presented as MySQL or MariaDB; the real engines were checked
 * separately.
 */
class UpdateSql30527Test extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, array{0: string, 1: string}> db-version writes updateDB() made */
    private array $savedSettings = [];

    protected function setUp(): void
    {
        parent::setUp();

        // updateDB() clears the installation cache on a version change; keep that in memory.
        config(['cache.stores.installation' => ['driver' => 'array']]);
    }

    /**
     * zp_queue as installs before 3.5.27 have it (update_sql_20109): primary key on msghash,
     * single-column indexes on projectId and userId, holding two queued rows.
     */
    private function createQueueTableAsBefore3527(Connection $connection): void
    {
        $queueTable = $connection->getTablePrefix().'zp_queue';
        $connection->statement('CREATE TABLE '.$queueTable.' (
            msghash VARCHAR(50) NOT NULL PRIMARY KEY,
            channel VARCHAR(255) NULL,
            userId INTEGER NOT NULL,
            subject VARCHAR(255) NULL,
            message TEXT NOT NULL,
            thedate DATETIME NOT NULL,
            projectId INTEGER NOT NULL
        )');
        $connection->statement('CREATE INDEX projectId ON '.$queueTable.' (projectId)');
        $connection->statement('CREATE INDEX userId ON '.$queueTable.' (userId)');

        foreach (['a-hash' => '2026-09-30 10:00:00', 'b-hash' => '2026-09-30 10:00:01'] as $msghash => $thedate) {
            $connection->table('zp_queue')->insert([
                'msghash' => $msghash,
                'channel' => Workers::WEBHOOKS->value,
                'userId' => 1,
                'subject' => 'subject',
                'message' => 'message '.$msghash,
                'thedate' => $thedate,
                'projectId' => 5,
            ]);
        }
    }

    /**
     * An Install repository migrating the given database, with the real AppSettings as the code
     * version.
     */
    private function installer(Connection $connection): Install
    {
        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        foreach (['connection' => $connection, 'config' => app('config'), 'settings' => new AppSettings] as $property => $value) {
            (new \ReflectionProperty(Install::class, $property))->setValue($install, $value);
        }

        return $install;
    }

    /**
     * Runs updateDB() the way the updater does, on an install whose recorded db-version is
     * 3.5.26 and whose code version is the real AppSettings.
     */
    private function updateFrom3526(Connection $connection): array|bool
    {
        $this->app->instance(SettingCache::class, $this->make(SettingCache::class, ['forget' => null]));
        $this->app->instance(SettingRepository::class, $this->make(SettingRepository::class, [
            'getSetting' => fn (string $key) => $key === 'db-version' ? '3.5.26' : false,
        ]));
        $this->app->instance(SettingService::class, $this->make(SettingService::class, [
            'saveSetting' => function ($key, $value): bool {
                $this->savedSettings[] = [$key, $value];

                return true;
            },
        ]));

        return $this->installer($connection)->updateDB();
    }

    /**
     * @return array<int, array<int, string>> the column lists of zp_queue's indexes
     */
    private function queueIndexColumns(Connection $connection): array
    {
        return array_map(fn (array $index) => $index['columns'], $connection->getSchemaBuilder()->getIndexes('zp_queue'));
    }

    /**
     * @return array<int, string> the row-format changes the migration ran
     */
    private function rowFormatChanges(SqliteStandInForInstalledDatabase $connection): array
    {
        return array_values(array_filter($connection->statementsRun, fn (string $statement) => stripos($statement, 'row_format') !== false));
    }

    /**
     * Asks SQLite how it would run the batch read Queue::listMessageInQueue issues for a
     * runner: it must find the channel through the index and take rows in index order, with
     * no temporary B-tree to sort them.
     */
    private function assertTheBatchReadWalksTheChannelIndex(Connection $connection): void
    {
        $queueRepo = new QueueRepository(
            $this->make(DbCore::class, ['getConnection' => fn () => $connection]),
            $this->make(UserRepo::class),
        );

        $connection->enableQueryLog();
        $connection->flushQueryLog();
        $queueRepo->listMessageInQueue(Workers::WEBHOOKS, limit: 10);
        $batchRead = $connection->getQueryLog()[0];

        $plan = implode(' | ', array_map(
            fn ($step) => $step->detail,
            $connection->select('EXPLAIN QUERY PLAN '.$batchRead['query'], $batchRead['bindings'])
        ));

        $this->assertStringContainsString('USING INDEX idx_queue_channel_thedate_msghash (channel=?)', $plan, 'The channel is found through the composite index');
        $this->assertStringNotContainsString('TEMP B-TREE', $plan, 'Rows come off the index already in thedate, msghash order, so nothing is sorted');
    }

    public function test_a_fresh_install_indexes_the_queue_for_the_channel_batch_read(): void
    {
        config([
            'database.connections.fresh_install' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'database.default' => 'fresh_install',
        ]);

        app(SchemaBuilder::class)->createAllTables();

        $connection = DB::connection();
        $this->assertTrue($connection->getSchemaBuilder()->hasIndex('zp_queue', 'idx_queue_channel_thedate_msghash'));
        $this->assertContains(['channel', 'thedate', 'msghash'], $this->queueIndexColumns($connection), 'Columns in exactly this order');
        $this->assertTheBatchReadWalksTheChannelIndex($connection);
    }

    public function test_updating_from_3_5_26_indexes_the_existing_queue_keeps_its_rows_and_records_3_5_27(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createQueueTableAsBefore3527($connection);

        $this->assertSame(true, $this->updateFrom3526($connection));

        $this->assertSame([['db-version', '3.5.27']], $this->savedSettings, 'Only the one new migration runs, and its version is recorded');
        $this->assertTrue($connection->getSchemaBuilder()->hasIndex('zp_queue', 'idx_queue_channel_thedate_msghash'));
        $this->assertSame(['a-hash', 'b-hash'], $connection->table('zp_queue')->orderBy('msghash')->pluck('msghash')->all(), 'Queued messages survive the migration');
        $this->assertTheBatchReadWalksTheChannelIndex($connection);
    }

    public function test_rerunning_the_update_keeps_a_single_index_and_the_queued_rows(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createQueueTableAsBefore3527($connection);

        // The recorded version still reads 3.5.26 the second time, as after a lost settings write.
        $this->assertSame(true, $this->updateFrom3526($connection));
        $this->assertSame(true, $this->updateFrom3526($connection));

        $this->assertSame([['db-version', '3.5.27'], ['db-version', '3.5.27']], $this->savedSettings);
        $channelIndexes = array_filter($this->queueIndexColumns($connection), fn (array $columns) => $columns === ['channel', 'thedate', 'msghash']);
        $this->assertCount(1, $channelIndexes, 'The rerun finds the index and adds no second one');
        $this->assertSame(2, $connection->table('zp_queue')->count());
    }

    public function test_an_index_already_on_those_columns_under_another_name_is_kept_and_not_duplicated(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createQueueTableAsBefore3527($connection);
        $connection->statement('CREATE INDEX dba_queue_batch ON zp_queue (channel, thedate, msghash)');

        $this->assertSame(true, $this->updateFrom3526($connection));

        $this->assertSame([['db-version', '3.5.27']], $this->savedSettings);
        $this->assertFalse($connection->getSchemaBuilder()->hasIndex('zp_queue', 'idx_queue_channel_thedate_msghash'));
        $this->assertTrue($connection->getSchemaBuilder()->hasIndex('zp_queue', 'dba_queue_batch'));
    }

    public function test_an_index_that_cannot_be_built_fails_the_update_and_leaves_db_version_at_3_5_26(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createQueueTableAsBefore3527($connection);
        // Our index name is taken by an index on other columns: it does not serve the batch read,
        // and the migration will not drop an index it did not create.
        $connection->statement('CREATE INDEX idx_queue_channel_thedate_msghash ON zp_queue (channel)');

        $result = $this->updateFrom3526($connection);

        $this->assertIsArray($result);
        $this->assertStringStartsWith('Migration 30527 failed: ', $result[0]);
        $this->assertSame([], $this->savedSettings, 'db-version is not advanced past a failed migration');
        $this->assertSame(2, $connection->table('zp_queue')->count());
    }

    public function test_an_install_without_a_queue_table_skips_the_index_and_records_3_5_27(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;

        $this->assertSame(true, $this->updateFrom3526($connection));

        $this->assertSame([['db-version', '3.5.27']], $this->savedSettings);
        $this->assertFalse($connection->getSchemaBuilder()->hasTable('zp_queue'), 'The migration adds an index; it does not create the table');
    }

    /**
     * @return array<string, array{0: string, 1: string}> driver, row format information_schema reports
     */
    public static function rowFormatsTooNarrowForTheIndex(): array
    {
        return [
            'MySQL, COMPACT' => ['mysql', 'Compact'],
            'MySQL, REDUNDANT' => ['mysql', 'Redundant'],
            'MariaDB driver, COMPACT' => ['mariadb', 'Compact'],
        ];
    }

    /**
     * @dataProvider rowFormatsTooNarrowForTheIndex
     */
    public function test_a_mysql_family_innodb_queue_in_a_767_byte_row_format_moves_to_dynamic_before_the_index_is_built(string $driver, string $rowFormat): void
    {
        $connection = new SqliteStandInForInstalledDatabase($driver, 'lt_');
        $this->createQueueTableAsBefore3527($connection);
        $connection->queueTableStatus = ['table_engine' => 'InnoDB', 'table_row_format' => $rowFormat];
        $connection->statementsRun = [];

        $this->assertSame(true, $this->updateFrom3526($connection));

        $this->assertSame([['db-version', '3.5.27']], $this->savedSettings);
        $this->assertCount(1, $connection->informationSchemaReads);
        $this->assertSame(['leantime', 'lt_zp_queue'], $connection->informationSchemaReads[0]['bindings'], 'Schema and prefixed table name are bound, not interpolated');
        $this->assertStringNotContainsString('zp_queue', $connection->informationSchemaReads[0]['query']);
        $this->assertSame(
            ['alter table "lt_zp_queue" row_format = dynamic', 'create index "idx_queue_channel_thedate_msghash" on "lt_zp_queue" ("channel", "thedate", "msghash")'],
            $connection->statementsRun,
            'The table moves to DYNAMIC first, then the index is built on it'
        );
        $this->assertSame(['a-hash', 'b-hash'], $connection->table('zp_queue')->orderBy('msghash')->pluck('msghash')->all());
    }

    /**
     * @return array<string, array{0: string, 1: string}> engine, row format information_schema reports
     */
    public static function queueTablesThatNeedNoRebuild(): array
    {
        return [
            'InnoDB, DYNAMIC (every current default)' => ['InnoDB', 'Dynamic'],
            'InnoDB, COMPRESSED' => ['InnoDB', 'Compressed'],
            'not InnoDB, whatever row format it reports' => ['MyISAM', 'Compact'],
        ];
    }

    /**
     * @dataProvider queueTablesThatNeedNoRebuild
     */
    public function test_a_mysql_queue_table_that_needs_no_row_format_change_is_indexed_without_a_rebuild(string $engine, string $rowFormat): void
    {
        $connection = new SqliteStandInForInstalledDatabase('mysql');
        $this->createQueueTableAsBefore3527($connection);
        $connection->queueTableStatus = ['table_engine' => $engine, 'table_row_format' => $rowFormat];

        $this->assertSame(true, $this->updateFrom3526($connection));

        $this->assertSame([['db-version', '3.5.27']], $this->savedSettings);
        $this->assertCount(1, $connection->informationSchemaReads, 'The row format is looked up');
        $this->assertSame([], $this->rowFormatChanges($connection), 'The table is not rebuilt');
        $this->assertTrue($connection->getSchemaBuilder()->hasIndex('zp_queue', 'idx_queue_channel_thedate_msghash'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function driversOutsideTheMysqlFamily(): array
    {
        return ['SQLite' => ['sqlite'], 'Postgres' => ['pgsql']];
    }

    /**
     * @dataProvider driversOutsideTheMysqlFamily
     */
    public function test_other_databases_never_look_up_or_change_the_row_format(string $driver): void
    {
        $connection = new SqliteStandInForInstalledDatabase($driver);
        $this->createQueueTableAsBefore3527($connection);
        // Would trigger a rebuild on MySQL; must not even be read here.
        $connection->queueTableStatus = ['table_engine' => 'InnoDB', 'table_row_format' => 'Compact'];

        $this->assertSame(true, $this->installer($connection)->update_sql_30527());

        $this->assertSame([], $connection->informationSchemaReads);
        $this->assertSame([], $this->rowFormatChanges($connection));
        $this->assertTrue($connection->getSchemaBuilder()->hasIndex('zp_queue', 'idx_queue_channel_thedate_msghash'));
    }

    public function test_a_failed_row_format_change_fails_the_update_and_leaves_db_version_at_3_5_26(): void
    {
        $connection = new SqliteStandInForInstalledDatabase('mysql');
        $this->createQueueTableAsBefore3527($connection);
        $connection->queueTableStatus = ['table_engine' => 'InnoDB', 'table_row_format' => 'Compact'];
        $connection->rowFormatChangeFails = true;

        $result = $this->updateFrom3526($connection);

        $this->assertIsArray($result);
        $this->assertStringStartsWith('Migration 30527 failed: ', $result[0]);
        $this->assertStringContainsString('row_format = dynamic', $result[0], 'The error names the statement that failed');
        $this->assertSame([], $this->savedSettings, 'db-version is not advanced past a failed migration');
        $this->assertFalse($connection->getSchemaBuilder()->hasIndex('zp_queue', 'idx_queue_channel_thedate_msghash'), 'No index is attempted on a table that could not hold it');
        $this->assertSame(2, $connection->table('zp_queue')->count());
    }
}
