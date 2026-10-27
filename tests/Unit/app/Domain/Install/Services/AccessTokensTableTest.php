<?php

namespace Unit\app\Domain\Install\Services;

use Illuminate\Support\Facades\Schema;
use Leantime\Domain\Install\Services\SchemaBuilder;
use Unit\app\Domain\Install\SqliteStandInForInstalledDatabase;
use Unit\TestCase;

/**
 * A fresh install builds zp_access_tokens from SchemaBuilder and never runs update_sql_30504,
 * so the push-notification columns that migration adds must also be part of the create.
 * Without them registerPushToken has nowhere to write and mobile push never arrives.
 */
class AccessTokensTableTest extends TestCase
{
    private const PUSH_COLUMNS = [
        'push_token',
        'push_platform',
        'push_provider',
        'push_token_updated_at',
        'push_invalidated_at',
    ];

    public function test_fresh_install_table_has_the_push_columns_and_index(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        Schema::swap($connection->getSchemaBuilder());

        $builder = (new \ReflectionClass(SchemaBuilder::class))->newInstanceWithoutConstructor();
        $builder->createAccessTokensTable();

        $columns = $connection->getSchemaBuilder()->getColumnListing('zp_access_tokens');
        foreach (self::PUSH_COLUMNS as $column) {
            $this->assertContains($column, $columns, "Fresh installs need {$column} for mobile push");
        }

        $indexes = array_column($connection->getSchemaBuilder()->getIndexes('zp_access_tokens'), 'name');
        $this->assertContains('idx_access_tokens_tokenable_push', $indexes);
    }
}
