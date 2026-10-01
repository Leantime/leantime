<?php

namespace Unit\app\Domain\Install;

use Illuminate\Database\ConnectionInterface;
use Leantime\Domain\Install\Repositories\Install;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * Regression tests for migration update_sql_30526 — the hygiene pass that
 * deletes goal↔milestone `tracked_by` edges violating the same-project product
 * rule (promoted by the pre-guard 30524/30525 backfill) and edges orphaned by
 * the pre-cascade generic ticket delete. Behavior pinned with a faked
 * connection — no DB.
 *
 * The migration issues two pluck reads on the aliased edge table (cross-project
 * first, then orphans) and one chunked whereIn-delete on the plain table; the
 * fake serves them in that order.
 */
class UpdateSql30526Test extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @param  int[]  $crossProjectIds  edge ids the cross-project read returns
     * @param  int[]  $orphanIds  edge ids the orphan read returns
     * @return array{result: mixed, deleted: int[]}
     */
    private function runMigration(array $crossProjectIds, array $orphanIds, bool $tablesExist = true): array
    {
        $deleted = [];
        // The two aliased pluck reads arrive in a fixed order (cross-project,
        // then orphans) — serve them from a queue shared across builder
        // instances.
        $pluckQueue = new \ArrayObject([$crossProjectIds, $orphanIds]);

        $schema = Mockery::mock();
        $schema->shouldReceive('hasTable')->andReturn($tablesExist);

        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->shouldReceive('getSchemaBuilder')->andReturn($schema);
        $conn->shouldReceive('table')->andReturnUsing(
            function (string $table) use ($pluckQueue, &$deleted) {
                return new class($pluckQueue, $deleted)
                {
                    private array $whereInIds = [];

                    public function __construct(private \ArrayObject $pluckQueue, private array &$deleted) {}

                    public function join(...$a): static
                    {
                        return $this;
                    }

                    public function leftJoin(...$a): static
                    {
                        return $this;
                    }

                    public function where(...$a): static
                    {
                        return $this;
                    }

                    public function whereColumn(...$a): static
                    {
                        return $this;
                    }

                    public function whereIn($column, $ids): static
                    {
                        $this->whereInIds = $ids;

                        return $this;
                    }

                    public function pluck($column)
                    {
                        $sets = $this->pluckQueue->getArrayCopy();
                        $next = array_shift($sets);
                        $this->pluckQueue->exchangeArray($sets);

                        return collect($next ?? []);
                    }

                    public function delete(): int
                    {
                        foreach ($this->whereInIds as $id) {
                            $this->deleted[] = (int) $id;
                        }

                        return count($this->whereInIds);
                    }
                };
            }
        );

        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        $prop = new \ReflectionProperty(Install::class, 'connection');
        $prop->setAccessible(true);
        $prop->setValue($install, $conn);

        return ['result' => $install->update_sql_30526(), 'deleted' => $deleted];
    }

    public function test_deletes_cross_project_and_orphaned_edges(): void
    {
        $out = $this->runMigration(crossProjectIds: [11, 12], orphanIds: [13]);

        $this->assertTrue($out['result']);
        $this->assertSame([11, 12, 13], $out['deleted']);
    }

    public function test_deduplicates_an_edge_that_is_both_cross_project_and_orphaned(): void
    {
        $out = $this->runMigration(crossProjectIds: [11], orphanIds: [11, 12]);

        $this->assertTrue($out['result']);
        $this->assertSame([11, 12], $out['deleted'], 'an id in both sets is deleted once');
    }

    public function test_a_clean_graph_deletes_nothing(): void
    {
        $out = $this->runMigration(crossProjectIds: [], orphanIds: []);

        $this->assertTrue($out['result']);
        $this->assertSame([], $out['deleted']);
    }

    public function test_is_a_no_op_when_the_required_tables_are_absent(): void
    {
        $out = $this->runMigration(crossProjectIds: [11], orphanIds: [12], tablesExist: false);

        $this->assertTrue($out['result']);
        $this->assertSame([], $out['deleted'], 'missing schema short-circuits before any delete');
    }
}
