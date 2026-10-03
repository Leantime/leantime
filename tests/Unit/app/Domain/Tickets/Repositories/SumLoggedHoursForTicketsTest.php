<?php

namespace Unit\app\Domain\Tickets\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * sumLoggedHoursForTickets() (#1798) must count the same rows as the per-ticket booked hours
 * (Timesheets::getLoggedHoursForTicket): only the given ids, and never rows without a workDate.
 * Runs against a faked connection — no DB.
 */
class SumLoggedHoursForTicketsTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function repoWithConnection(ConnectionInterface $connection): Tickets
    {
        $repo = (new \ReflectionClass(Tickets::class))->newInstanceWithoutConstructor();
        $prop = new \ReflectionProperty(Tickets::class, 'connection');
        $prop->setAccessible(true);
        $prop->setValue($repo, $connection);

        return $repo;
    }

    public function test_sums_only_dated_rows_of_the_given_tickets(): void
    {
        $builder = Mockery::mock();
        $builder->shouldReceive('whereIn')->once()->with('ticketId', [11, 13])->andReturnSelf();
        $builder->shouldReceive('whereNotNull')->once()->with('workDate')->andReturnSelf();
        $builder->shouldReceive('sum')->once()->with('hours')->andReturn('3.75');

        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('table')->once()->with('zp_timesheets')->andReturn($builder);

        $this->assertSame(3.75, $this->repoWithConnection($connection)->sumLoggedHoursForTickets([11, 13]));
    }

    public function test_empty_id_list_skips_the_query(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldNotReceive('table');

        $this->assertSame(0.0, $this->repoWithConnection($connection)->sumLoggedHoursForTickets([]));
    }
}
