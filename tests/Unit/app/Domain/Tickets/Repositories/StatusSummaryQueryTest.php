<?php

namespace Unit\app\Domain\Tickets\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\Processor;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * The status summary (#3703) aggregates in SQL, so its base query must carry the same project
 * access scope as getAllBySearchCriteria() and the summary filters. Compiled with a real query
 * builder against a faked connection — no DB.
 */
class StatusSummaryQueryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function baseQuery(?int $projectId, bool $includeSubtasks): Builder
    {
        session(['userdata' => ['id' => 4, 'clientId' => 2]]);

        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('table')->andReturnUsing(fn ($table) => (new Builder($connection, new MySqlGrammar, new Processor))->from($table));
        $connection->shouldReceive('raw')->andReturnUsing(fn ($value) => new Expression($value));

        $repo = (new \ReflectionClass(Tickets::class))->newInstanceWithoutConstructor();
        $property = new \ReflectionProperty(Tickets::class, 'connection');
        $property->setAccessible(true);
        $property->setValue($repo, $connection);

        $method = new \ReflectionMethod(Tickets::class, 'statusSummaryBaseQuery');
        $method->setAccessible(true);

        return $method->invoke($repo, $projectId, '', $includeSubtasks);
    }

    public function test_unscoped_summary_is_limited_to_accessible_open_projects(): void
    {
        $sql = $this->baseQuery(null, true)->toSql();

        $this->assertStringContainsString('left join `zp_relationuserproject` as `rup`', $sql);
        $this->assertStringContainsString('`requestor`.`role` >= ?', $sql);
        $this->assertStringContainsString('`zp_projects`.`state` <> ?', $sql);
        $this->assertStringContainsString('`zp_tickets`.`type` <> ?', $sql);
        $this->assertStringNotContainsString('`zp_tickets`.`projectId` = ?', $sql);
    }

    public function test_project_and_subtask_filters(): void
    {
        $query = $this->baseQuery(9, false);

        $this->assertStringContainsString('`zp_tickets`.`projectId` = ?', $query->toSql());
        $this->assertContains('subtask', $query->getBindings());
        $this->assertContains(9, $query->getBindings());
    }
}
