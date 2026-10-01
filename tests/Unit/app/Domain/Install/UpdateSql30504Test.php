<?php

namespace Unit\app\Domain\Install;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Leantime\Domain\Install\Repositories\Install;
use Leantime\Domain\Install\Services\SchemaBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * Regression tests for migration update_sql_30504 (#3706) — the push-notification
 * columns on zp_access_tokens.
 *
 * The migration used to ALTER the table unguarded. Installs whose zp_access_tokens
 * was never created (update_sql_30400 swallowed a failed CREATE and still returned
 * success, so 3.4.0 was recorded as applied) then hit
 * "1146 Table 'zp_access_tokens' doesn't exist" here and could not upgrade at all.
 *
 * These pin the self-heal: create the table when it is missing, and leave it alone
 * when it is not — no DB, facades faked.
 */
class UpdateSql30504Test extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Run update_sql_30504 with the Schema/DB facades faked.
     *
     * @param  bool  $tableExists  what Schema::hasTable reports for zp_access_tokens
     * @return array{result: mixed, recreated: bool}
     */
    private function runMigration(bool $tableExists): array
    {
        // swap() rather than shouldReceive(): the latter resolves the real facade root
        // first, and unit tests run with `database.default => []`, so building the
        // DatabaseManager blows up before any expectation is set.
        $schema = Mockery::mock();
        $schema->shouldReceive('hasTable')->with('zp_access_tokens')->andReturn($tableExists);
        // The column/index work is not under test here: accept the calls and skip the
        // closures so no Blueprint is needed.
        $schema->shouldReceive('table')->andReturnNull();
        $schema->shouldReceive('hasColumn')->andReturn(true);
        Schema::swap($schema);

        $db = Mockery::mock();
        $db->shouldReceive('select')->andReturn([]);
        DB::swap($db);

        $recreated = false;
        $builder = Mockery::mock(SchemaBuilder::class);
        $builder->shouldReceive('createAccessTokensTable')
            ->andReturnUsing(function () use (&$recreated): void {
                $recreated = true;
            });
        app()->instance(SchemaBuilder::class, $builder);

        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();

        return ['result' => $install->update_sql_30504(), 'recreated' => $recreated];
    }

    public function test_recreates_the_table_when_it_is_missing_instead_of_failing(): void
    {
        $run = $this->runMigration(tableExists: false);

        $this->assertTrue(
            $run['recreated'],
            'A missing zp_access_tokens must be recreated, not left for the ALTER to die on (#3706)'
        );
        $this->assertTrue(
            $run['result'],
            'The migration must succeed on an install that reached 30504 without the table'
        );
    }

    public function test_leaves_an_existing_table_alone(): void
    {
        $run = $this->runMigration(tableExists: true);

        $this->assertFalse(
            $run['recreated'],
            'An install that already has zp_access_tokens must not have it recreated'
        );
        $this->assertTrue($run['result'], 'The migration must still report success');
    }
}
