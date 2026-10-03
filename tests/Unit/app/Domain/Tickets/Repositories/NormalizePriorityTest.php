<?php

namespace Unit\app\Domain\Tickets\Repositories;

use Leantime\Domain\Tickets\Repositories\Tickets;
use Unit\TestCase;

/**
 * Priority is rendered into css class names and used as a lookup key, so only the canonical
 * forms may reach the database: '' (none) or a single digit '0'-'5'.
 */
class NormalizePriorityTest extends TestCase
{
    public function test_canonical_values_are_accepted(): void
    {
        $this->assertSame('', Tickets::normalizePriority(''));
        $this->assertSame('', Tickets::normalizePriority(null));
        $this->assertSame('0', Tickets::normalizePriority('0'));
        $this->assertSame('5', Tickets::normalizePriority('5'));
        $this->assertSame('3', Tickets::normalizePriority(3));
    }

    public function test_non_canonical_values_are_rejected(): void
    {
        foreach (['+1', ' 1 ', '1 ', '01', '6', '-1', '2.5', '1e0', 'x', 6, -1, 2.0, true, []] as $invalidPriority) {
            $this->assertNull(
                Tickets::normalizePriority($invalidPriority),
                'Priority '.var_export($invalidPriority, true).' must be rejected'
            );
        }
    }
}
