<?php

namespace Unit\app\Domain\Search;

use Leantime\Domain\Search\Services\Search;

/**
 * Raw filter input from the results page must validate into the provider vocabulary, with
 * day bounds converted from the user's timezone to UTC.
 */
class SearchFiltersTest extends \Unit\TestCase
{
    public function test_empty_input_yields_no_filters(): void
    {
        $this->assertSame([], Search::normalizeFilters([]));
        $this->assertSame([], Search::normalizeFilters(['projectId' => '0', 'from' => '', 'to' => 'nope', 'mine' => '0']));
    }

    public function test_project_and_mine_are_normalized(): void
    {
        $filters = Search::normalizeFilters(['projectId' => '7', 'mine' => '1', 'unknown' => 'x']);

        $this->assertSame(['projectId' => 7, 'mine' => true], $filters);
    }

    public function test_dates_become_utc_day_bounds_in_user_timezone(): void
    {
        session(['usersettings.timezone' => 'America/New_York']);

        $filters = Search::normalizeFilters(['from' => '2026-07-01', 'to' => '2026-07-01']);

        // EDT is UTC-4: the local day runs 04:00 UTC to 03:59:59 UTC the next day.
        $this->assertSame('2026-07-01 04:00:00', $filters['from']);
        $this->assertSame('2026-07-02 03:59:59', $filters['to']);
    }

    public function test_invalid_dates_are_ignored(): void
    {
        $filters = Search::normalizeFilters(['from' => '2026-13-45', 'to' => '01/02/2026']);

        $this->assertArrayNotHasKey('from', $filters);
        $this->assertArrayNotHasKey('to', $filters);
    }
}
