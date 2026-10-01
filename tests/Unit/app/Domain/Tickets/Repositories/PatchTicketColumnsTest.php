<?php

namespace Unit\app\Domain\Tickets\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * Regression tests for patchTicket() field-name matching (#3692).
 *
 * PATCHABLE_COLUMNS is mostly camelCase, but 'milestoneid' matches the real column name.
 * The lookup was a case-sensitive isset(), so the documented API/MCP field 'milestoneId'
 * never matched and was dropped — while patchTicket() still reported success, which is the
 * part that makes it dangerous for automation.
 *
 * These call patchTicket() for real against a faked connection and assert on the payload
 * it would have written — no DB.
 */
class PatchTicketColumnsTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Run patchTicket() and capture the column => value payload handed to update().
     *
     * @param  array<string, mixed>  $params
     * @return array{result: bool, updates: array<string, mixed>}
     */
    private function runPatch(array $params): array
    {
        $updates = [];

        $builder = Mockery::mock();
        $builder->shouldReceive('where')->andReturnSelf();
        $builder->shouldReceive('update')->andReturnUsing(function ($payload) use (&$updates) {
            $updates = $payload;

            return 1;
        });
        // addTicketChange() reads the previous row before logging the change; an empty
        // result short-circuits it without touching anything under test.
        $builder->shouldReceive('select')->andReturnSelf();
        $builder->shouldReceive('limit')->andReturnSelf();
        $builder->shouldReceive('first')->andReturn(null);
        $builder->shouldReceive('insert')->andReturn(true);
        $builder->shouldReceive('get')->andReturn(collect());

        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->shouldReceive('table')->andReturn($builder);

        $repo = (new \ReflectionClass(Tickets::class))->newInstanceWithoutConstructor();
        $prop = new \ReflectionProperty(Tickets::class, 'connection');
        $prop->setAccessible(true);
        $prop->setValue($repo, $conn);

        $result = $repo->patchTicket(42, $params);

        return ['result' => $result, 'updates' => $updates];
    }

    public function test_documented_milestone_id_casing_actually_patches(): void
    {
        $run = $this->runPatch(['milestoneId' => 7]);

        $this->assertArrayHasKey(
            'milestoneid',
            $run['updates'],
            'The documented milestoneId field must reach the update as the real column (#3692)'
        );
        $this->assertSame(7, $run['updates']['milestoneid']);
        $this->assertTrue($run['result']);
    }

    public function test_lowercase_milestoneid_still_works(): void
    {
        $run = $this->runPatch(['milestoneid' => 9]);

        $this->assertSame(9, $run['updates']['milestoneid'] ?? null);
    }

    public function test_unknown_fields_are_still_ignored(): void
    {
        $run = $this->runPatch(['bogusColumn' => 'x', 'headline' => 'kept']);

        $this->assertArrayNotHasKey('bogusColumn', $run['updates']);
        $this->assertArrayNotHasKey('boguscolumn', $run['updates']);
        $this->assertSame('kept', $run['updates']['headline'] ?? null);
    }

    public function test_a_patch_of_only_unknown_fields_reports_failure(): void
    {
        $run = $this->runPatch(['bogusColumn' => 'x']);

        $this->assertFalse(
            $run['result'],
            'Nothing patchable means nothing was written, and the caller must be told'
        );
    }
}
