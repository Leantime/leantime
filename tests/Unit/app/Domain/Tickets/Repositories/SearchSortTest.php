<?php

namespace Unit\app\Domain\Tickets\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\Processor;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Leantime\Domain\Tickets\Services\KanbanViewSettings;
use Unit\TestCase;

/**
 * ORDER BY produced for the ticket search sort keys the kanban card sort (#1536) maps to.
 * Exercised against a real query builder (MySQL grammar) — no DB.
 */
class SearchSortTest extends TestCase
{
    private function orderSqlFor(string $sort): string
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $grammar = new MySqlGrammar;
        $query = (new Builder($connection, $grammar, new Processor))->from('zp_tickets');

        $dbHelper = $this->createMock(DatabaseHelper::class);
        $dbHelper->method('wrapColumn')->willReturnCallback(fn (string $column) => $grammar->wrap($column));

        $repo = (new \ReflectionClass(Tickets::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Tickets::class, 'dbHelper'))->setValue($repo, $dbHelper);

        $method = new \ReflectionMethod(Tickets::class, 'applySearchSort');
        $method->invoke($repo, $query, $sort);

        return str_replace('select * from `zp_tickets` ', '', $query->toSql());
    }

    public function test_due_date_puts_null_and_legacy_sentinel_dates_last(): void
    {
        $this->assertSame(
            "order by (CASE WHEN `zp_tickets`.`dateToFinish` IS NULL OR `zp_tickets`.`dateToFinish` < '1970-01-02' THEN 1 ELSE 0 END), `zp_tickets`.`dateToFinish` asc, `zp_tickets`.`sortindex` asc, `zp_tickets`.`id` desc",
            $this->orderSqlFor(KanbanViewSettings::repositorySortKey('dueDate'))
        );

        // Both "no date" sentinels Leantime stores (see showKanban's due-date check) fall below the cut-off.
        $this->assertLessThan('1970-01-02', '0000-00-00 00:00:00');
        $this->assertLessThan('1970-01-02', '1969-12-31 00:00:00');
    }

    public function test_priority_and_effort_put_unset_values_last(): void
    {
        $this->assertStringStartsWith(
            "order by (CASE WHEN `zp_tickets`.`priority` IS NULL OR `zp_tickets`.`priority` = '' OR `zp_tickets`.`priority` = '0' THEN 1 ELSE 0 END), `zp_tickets`.`priority` asc",
            $this->orderSqlFor(KanbanViewSettings::repositorySortKey('priority'))
        );

        $this->assertStringStartsWith(
            'order by (CASE WHEN `zp_tickets`.`storypoints` IS NULL OR `zp_tickets`.`storypoints` <= 0 THEN 1 ELSE 0 END), `zp_tickets`.`storypoints` desc',
            $this->orderSqlFor(KanbanViewSettings::repositorySortKey('effort'))
        );
    }

    public function test_every_kanban_sort_option_maps_to_an_ordering(): void
    {
        $this->assertSame('order by `zp_tickets`.`kanbanSortIndex` asc, `zp_tickets`.`id` desc', $this->orderSqlFor(KanbanViewSettings::repositorySortKey('manual')));
        $this->assertSame('order by `zp_tickets`.`date` desc, `zp_tickets`.`sortindex` asc, `zp_tickets`.`id` desc', $this->orderSqlFor(KanbanViewSettings::repositorySortKey('created')));
        $this->assertSame('order by `zp_tickets`.`headline` asc, `zp_tickets`.`id` desc', $this->orderSqlFor(KanbanViewSettings::repositorySortKey('title')));

        foreach (array_keys(KanbanViewSettings::SORT_OPTIONS) as $sortOption) {
            $this->assertStringStartsWith('order by ', $this->orderSqlFor(KanbanViewSettings::repositorySortKey($sortOption)), $sortOption.' must order the query');
        }

        $this->assertStringNotContainsString('order by', $this->orderSqlFor('unknown'), 'unknown keys add no order');
    }
}
