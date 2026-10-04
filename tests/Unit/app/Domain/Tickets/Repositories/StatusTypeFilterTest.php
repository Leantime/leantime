<?php

namespace Unit\app\Domain\Tickets\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\Processor;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * The statusType ticket filter (#3700) resolves status types against EACH project's own labels,
 * because the same status id can mean "Done" in one project and "In Progress" in another.
 * Exercised against a real query builder (MySQL grammar) — no DB.
 */
class StatusTypeFilterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function applyFilter(array $projectIds, string $statusTypes): Builder
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $query = (new Builder($connection, new MySqlGrammar, new Processor))->from('zp_tickets');

        $labelsByProject = [
            // Project 1: default seed.
            1 => [3 => ['statusType' => 'NEW'], 4 => ['statusType' => 'INPROGRESS'], 0 => ['statusType' => 'DONE'], -1 => ['statusType' => 'NONE']],
            // Project 2: status 4 is repurposed as Done.
            2 => [3 => ['statusType' => 'NEW'], 4 => ['statusType' => 'DONE'], 5 => ['statusType' => 'INPROGRESS']],
        ];

        $repo = Mockery::mock(Tickets::class)->makePartial();
        $repo->shouldReceive('getStateLabels')->andReturnUsing(fn ($projectId) => $labelsByProject[$projectId] ?? []);

        $method = new \ReflectionMethod(Tickets::class, 'applyStatusTypeFilter');
        $method->setAccessible(true);
        $method->invoke($repo, $query, $projectIds, $statusTypes);

        return $query;
    }

    public function test_status_type_is_resolved_per_project(): void
    {
        $query = $this->applyFilter([1, 2], 'INPROGRESS');

        $this->assertSame(
            'select * from `zp_tickets` where ((`zp_tickets`.`projectId` = ? and `zp_tickets`.`status` in (?)) or (`zp_tickets`.`projectId` = ? and `zp_tickets`.`status` in (?)))',
            $query->toSql()
        );
        $this->assertSame([1, 4, 2, 5], $query->getBindings());
    }

    public function test_not_done_covers_every_non_done_type(): void
    {
        $query = $this->applyFilter([2], 'NOT_DONE');

        $this->assertSame([2, 3, 5], $query->getBindings());
    }

    public function test_no_matching_status_matches_nothing(): void
    {
        $query = $this->applyFilter([3], 'DONE');

        $this->assertStringContainsString('1 = 0', $query->toSql());
    }
}
