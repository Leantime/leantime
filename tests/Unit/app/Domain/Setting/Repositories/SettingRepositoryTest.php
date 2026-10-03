<?php

namespace Unit\app\Domain\Setting\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\SettingCache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use Unit\TestCase;

/**
 * Regression tests for saveSetting()'s cache handling.
 *
 * saveSetting() used to cache the new value even when updateOrInsert() reported false,
 * so the next getSetting() returned a value the database never stored. Callers that read
 * a setting back to confirm a save (the personal webhook in Users::saveOwnNotificationPreferences)
 * were then told a lost write had succeeded.
 *
 * Also pins getSettingsForKeys()'s uncached read, which the personal webhook queue uses
 * so a long-running worker never delivers from an in-memory copy another process outdated.
 *
 * Faked connection and an in-memory cache — no DB, no cache store.
 */
class SettingRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const KEY = 'usersettings.7.webhook';

    /**
     * The faked zp_settings table: stored rows, what the next updateOrInsert() does,
     * and which keys were read from the table.
     */
    private object $database;

    private SettingCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = new class
        {
            /** @var array<string, mixed> key => value rows in zp_settings */
            public array $rows = [];

            /** true: the row is written; false: nothing is written; Throwable: thrown (DB unavailable) */
            public bool|\Throwable $writeResult = true;

            /** @var array<int, string> keys fetched with first(), in order */
            public array $reads = [];
        };

        $this->cache = new class extends SettingCache
        {
            /** @var array<string, mixed> */
            public array $entries = [];

            public function get(string $key): mixed
            {
                return $this->entries[$key] ?? null;
            }

            public function set(string $key, mixed $value): void
            {
                $this->entries[$key] = $value;
            }

            public function forget(string $key): void
            {
                unset($this->entries[$key]);
            }
        };
    }

    /**
     * Build the real repository over the faked connection and cache.
     */
    private function repository(): SettingRepository
    {
        $database = $this->database;

        /** @var ConnectionInterface&\Mockery\MockInterface $connection */
        $connection = Mockery::mock(ConnectionInterface::class);
        /** @var \Mockery\Expectation $tableExpectation */
        $tableExpectation = $connection->shouldReceive('table');
        $tableExpectation->andReturnUsing(fn (string $table) => new class($database)
        {
            private string $key = '';

            public function __construct(private object $database) {}

            public function where(string $column, mixed $value): static
            {
                $this->key = (string) $value;

                return $this;
            }

            /** @var array<int, string> keys asked for by whereIn() */
            private array $keys = [];

            public function whereIn(string $column, array $values): static
            {
                $this->keys = array_map('strval', $values);

                return $this;
            }

            /** Stands in for getSettingsForKeys()'s uncached read. */
            public function pluck(string $value, string $key): \Illuminate\Support\Collection
            {
                array_push($this->database->reads, ...$this->keys);

                return collect(array_intersect_key($this->database->rows, array_flip($this->keys)));
            }

            public function limit(int $limit): static
            {
                return $this;
            }

            /** Stands in for checkIfInstalled()'s zp_user probe. */
            public function count(): int
            {
                return 1;
            }

            public function first(): ?object
            {
                $this->database->reads[] = $this->key;

                if (! array_key_exists($this->key, $this->database->rows)) {
                    return null;
                }

                return (object) ['key' => $this->key, 'value' => $this->database->rows[$this->key]];
            }

            /** Stands in for an INSERT IGNORE on the zp_settings primary key. */
            public function insertOrIgnore(array $values): int
            {
                if (array_key_exists($values['key'], $this->database->rows)) {
                    return 0;
                }

                $this->database->rows[$values['key']] = $values['value'];

                return 1;
            }

            public function updateOrInsert(array $attributes, array $values): bool
            {
                if ($this->database->writeResult instanceof \Throwable) {
                    throw $this->database->writeResult;
                }

                if ($this->database->writeResult === true) {
                    $this->database->rows[$attributes['key']] = $values['value'];
                }

                return $this->database->writeResult;
            }
        });

        /** @var DbCore&\Mockery\MockInterface $db */
        $db = Mockery::mock(DbCore::class);
        $db->shouldReceive('getConnection')->andReturn($connection);

        return new SettingRepository($db, $this->cache);
    }

    public function test_a_write_that_reports_false_does_not_cache_the_new_value(): void
    {
        // The old value is on file and cached (as it is after the settings page loads).
        $this->database->rows[self::KEY] = 'old';
        $this->cache->set(self::KEY, 'old');
        $this->database->writeResult = false;
        $repository = $this->repository();

        $this->assertFalse($repository->saveSetting(self::KEY, 'new'));

        $this->assertNotSame('new', $this->cache->entries[self::KEY] ?? null, 'a value the database did not store must never be cached');
        $this->assertSame('old', $repository->getSetting(self::KEY), 'reading back must report what the database holds, not the attempted value');
    }

    public function test_a_false_no_op_write_is_confirmed_by_reading_the_database(): void
    {
        // The row already holds the value and the driver reports no changed rows.
        $this->database->rows[self::KEY] = 'same';
        $this->cache->set(self::KEY, 'same');
        $this->database->writeResult = false;
        $repository = $this->repository();

        $this->assertFalse($repository->saveSetting(self::KEY, 'same'));
        $this->database->reads = [];

        $this->assertSame('same', $repository->getSetting(self::KEY));
        $this->assertSame([self::KEY], $this->database->reads, 'the read-back must come from the database, not a cached copy');
    }

    public function test_a_successful_write_caches_the_new_value(): void
    {
        $this->database->rows[self::KEY] = 'old';
        $this->cache->set(self::KEY, 'old');
        $repository = $this->repository();

        $this->assertTrue($repository->saveSetting(self::KEY, 'new'));
        $this->database->reads = [];

        $this->assertSame('new', $this->cache->entries[self::KEY] ?? null);
        $this->assertSame('new', $repository->getSetting(self::KEY));
        $this->assertSame([], $this->database->reads, 'a confirmed write is served from the cache');
    }

    public function test_a_write_that_throws_does_not_cache_the_new_value(): void
    {
        // A lost connection surfaces as an exception from updateOrInsert(), not a false return.
        $this->database->rows[self::KEY] = 'old';
        $this->cache->set(self::KEY, 'old');
        $this->database->writeResult = new PDOException('database unavailable');
        $repository = $this->repository();

        $thrown = null;
        try {
            $repository->saveSetting(self::KEY, 'new');
        } catch (PDOException $e) {
            $thrown = $e;
        }

        $this->assertNotNull($thrown, 'saveSetting() must let the database exception reach the caller');
        $this->assertNotSame('new', $this->cache->entries[self::KEY] ?? null);
    }

    public function test_an_uncached_multi_key_read_comes_from_the_database_and_never_touches_the_cache(): void
    {
        // This process cached 'old' earlier; another process has since stored 'new'.
        $this->database->rows[self::KEY] = 'new';
        $this->cache->set(self::KEY, 'old');
        $repository = $this->repository();

        $this->assertSame([self::KEY => 'old'], $repository->getSettingsForKeys([self::KEY]), 'by default the cached copy is served');
        $this->assertSame([], $this->database->reads);

        $this->assertSame([self::KEY => 'new'], $repository->getSettingsForKeys([self::KEY], useCache: false));
        $this->assertSame([self::KEY], $this->database->reads, 'the uncached read comes from the database');
        // Were it written back, a read racing a concurrent save could put the pre-save value over the saved one.
        $this->assertSame('old', $this->cache->entries[self::KEY], 'an uncached read never writes the cache');
    }

    public function test_add_if_absent_inserts_when_the_key_is_missing_and_evicts_a_cached_miss(): void
    {
        // An earlier read cached the miss.
        $this->cache->set(self::KEY, false);
        $repository = $this->repository();

        $this->assertTrue($repository->addSettingIfAbsent(self::KEY, 'first'));

        $this->assertSame('first', $this->database->rows[self::KEY]);
        $this->assertArrayNotHasKey(self::KEY, $this->cache->entries, 'the cached miss must be dropped');
        $this->assertSame('first', $repository->getSetting(self::KEY));
    }

    public function test_add_if_absent_keeps_the_existing_value_and_evicts_a_cached_miss(): void
    {
        // Another process stored a value after this one cached the miss.
        $this->database->rows[self::KEY] = 'first';
        $this->cache->set(self::KEY, false);
        $repository = $this->repository();

        $this->assertFalse($repository->addSettingIfAbsent(self::KEY, 'second'));

        $this->assertSame('first', $this->database->rows[self::KEY], 'an existing row is never overwritten');
        $this->assertArrayNotHasKey(self::KEY, $this->cache->entries, 'the cached miss must be dropped');
        $this->assertSame('first', $repository->getSetting(self::KEY));
    }
}
