<?php

namespace Unit\app\Domain\Tickets\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * A milestone's primary dependency lives in zp_tickets.milestoneid, its further dependencies are
 * depends_on rows in zp_entity_relationship (#268). Runs against a faked connection — no DB.
 */
class MilestoneDependenciesRepositoryTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();

        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);

        app()->instance(EnvironmentCore::class, $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]));
        app()->instance(LanguageCore::class, $this->make(LanguageCore::class, ['__' => fn ($key) => $key]));

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));
    }

    private function repoWithConnection(ConnectionInterface $connection): Tickets
    {
        $repo = (new \ReflectionClass(Tickets::class))->newInstanceWithoutConstructor();
        $prop = new \ReflectionProperty(Tickets::class, 'connection');
        $prop->setAccessible(true);
        $prop->setValue($repo, $connection);

        return $repo;
    }

    /**
     * Query builder double: every chained call returns itself, get() returns the rows.
     *
     * @param  array<int, array<string, int>>  $rows
     */
    private function edgeBuilder(array $rows): Mockery\MockInterface
    {
        $builder = Mockery::mock();
        $builder->shouldReceive('select', 'whereIn', 'where', 'orderBy')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(new Collection(array_map(fn ($row) => (object) $row, $rows)));

        return $builder;
    }

    public function test_the_primary_dependency_comes_first_followed_by_the_additional_ones(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('table')->with('zp_entity_relationship')->andReturn($this->edgeBuilder([
            ['entityA' => 5, 'entityB' => 8],
            ['entityA' => 5, 'entityB' => 3],
            ['entityA' => 5, 'entityB' => 3],
            ['entityA' => 6, 'entityB' => 3],
        ]));

        $dependencies = $this->repoWithConnection($connection)->getMilestoneDependenciesForPrimaries([5 => '3', 6 => '', 7 => null]);

        $this->assertSame([5 => [3, 8], 6 => [3], 7 => []], $dependencies);
    }

    public function test_no_milestones_means_no_query(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldNotReceive('table');

        $this->assertSame([], $this->repoWithConnection($connection)->getMilestoneDependenciesForPrimaries([]));
        $this->assertSame([], $this->repoWithConnection($connection)->getMilestoneDependencies([0, '']));
    }

    public function test_additional_dependencies_replace_the_stored_ones(): void
    {
        $builder = Mockery::mock();
        $builder->shouldReceive('where')->andReturnSelf();
        $builder->shouldReceive('delete')->once()->ordered()->andReturn(1);
        $builder->shouldReceive('insert')->once()->ordered()->with(Mockery::on(function (array $rows) {
            return count($rows) === 2
                && $rows[0]['entityA'] === 5 && $rows[0]['entityB'] === 8
                && $rows[1]['entityA'] === 5 && $rows[1]['entityB'] === 9
                && $rows[0]['relationship'] === 'depends_on'
                && $rows[0]['entityAType'] === 'Ticket' && $rows[0]['entityBType'] === 'Ticket'
                && $rows[0]['createdBy'] === 1;
        }));

        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('table')->with('zp_entity_relationship')->andReturn($builder);
        $connection->shouldReceive('transaction')->once()->andReturnUsing(fn (callable $callback) => $callback());

        $this->assertTrue($this->repoWithConnection($connection)->setAdditionalMilestoneDependencies(5, [8, 9, 8], 1));
    }

    public function test_removing_every_additional_dependency_only_deletes(): void
    {
        $builder = Mockery::mock();
        $builder->shouldReceive('where')->andReturnSelf();
        $builder->shouldReceive('delete')->once()->andReturn(2);
        $builder->shouldNotReceive('insert');

        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('table')->with('zp_entity_relationship')->andReturn($builder);
        $connection->shouldReceive('transaction')->once()->andReturnUsing(fn (callable $callback) => $callback());

        $this->assertTrue($this->repoWithConnection($connection)->setAdditionalMilestoneDependencies(5, [], 1));
    }
}
