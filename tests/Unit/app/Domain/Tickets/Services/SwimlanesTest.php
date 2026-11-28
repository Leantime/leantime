<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Language;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Clients\Services\Clients;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Sprints\Services\Sprints;
use Leantime\Domain\Tickets\Repositories\TicketHistory;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Unit\TestCase;

class SwimlanesTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    /** @dataProvider unsetFields */
    public function test_empty_null_and_zero_share_one_lane(string $field): void
    {
        $values = ['', '0', null, 0, '1', '5'];
        $tickets = [];
        foreach ($values as $id => $value) {
            $tickets[] = ['id' => $id + 1, $field => $value];
        }

        $groups = $this->service($tickets)->getAllGrouped(['groupBy' => $field]);

        $this->assertCount(3, $groups);
        $unset = array_values(array_filter($groups, fn ($group) => $group['id'] === '999'));
        $this->assertCount(1, $unset);
        $this->assertSame([1, 2, 3, 4], array_column($unset[0]['items'], 'id'));
        $this->assertSame('0', $unset[0]['value']);
        $this->assertCount(6, array_merge(...array_column(array_values($groups), 'items')));
        $this->assertSame('999', array_values($groups)[2]['id'], 'Unset values sort after real values');
    }

    public static function unsetFields(): array
    {
        return [['priority'], ['storypoints']];
    }

    public function test_real_efforts_remain_distinct_and_keep_descending_order(): void
    {
        $groups = $this->service([
            ['id' => 1, 'storypoints' => '0.5'],
            ['id' => 2, 'storypoints' => '1'],
            ['id' => 3, 'storypoints' => '13'],
        ])->getAllGrouped(['groupBy' => 'storypoints']);

        $this->assertSame(['13', '1', '0.5'], array_column(array_values($groups), 'value'));
    }

    /** @dataProvider dueWindows */
    public function test_alert_uses_the_forward_three_day_window(string $due, ?string $expected): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-28 12:00:00', 'UTC'));
        $groups = ['launch' => ['id' => 'launch', 'label' => 'Launch', 'items' => [
            ['status' => 1, 'dateToFinish' => $due, 'modified' => '2026-11-28 12:00:00'],
        ]]];

        $result = $this->service()->getStatusBreakdownBySwimlane($groups, [1 => 'New']);

        $this->assertSame($expected, $result['launch']['timeAlert']);
        $this->assertSame([1 => 1], $result['launch']['statusCounts']);
        $this->assertSame(1, $result['launch']['totalCount']);
    }

    public static function dueWindows(): array
    {
        return [
            'past' => ['2026-11-28 11:59:59', 'overdue'],
            'now' => ['2026-11-28 12:00:00', 'dueSoon'],
            'tomorrow' => ['2026-11-29 12:00:00', 'dueSoon'],
            'three days' => ['2026-12-01 12:00:00', 'dueSoon'],
            'outside window' => ['2026-12-01 12:00:01', null],
            'next month' => ['2026-12-28 12:00:00', null],
            'no deadline' => ['', null],
            'legacy zero date' => ['0000-00-00 00:00:00', null],
            'legacy no-date value' => ['1969-12-31 00:00:00', null],
        ];
    }

    /** @dataProvider legacyNoDeadlineValues */
    public function test_legacy_no_deadline_values_do_not_override_other_alerts(string $noDeadline): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-28 12:00:00', 'UTC'));
        $groups = [
            ['id' => 'dueSoon', 'label' => 'Due soon', 'items' => [
                ['status' => 1, 'dateToFinish' => $noDeadline, 'modified' => '2026-11-28 12:00:00'],
                ['status' => 1, 'dateToFinish' => '2026-11-29 12:00:00', 'modified' => '2026-11-28 12:00:00'],
            ]],
            ['id' => 'stale', 'label' => 'Stale', 'items' => [
                ['status' => 1, 'dateToFinish' => $noDeadline, 'modified' => '2026-11-01 12:00:00'],
            ]],
        ];

        $result = $this->service()->getStatusBreakdownBySwimlane($groups, [1 => 'New']);

        $this->assertSame('dueSoon', $result['dueSoon']['timeAlert']);
        $this->assertSame('stale', $result['stale']['timeAlert']);
    }

    public static function legacyNoDeadlineValues(): array
    {
        return [
            'zero date' => ['0000-00-00 00:00:00'],
            'legacy no-date value' => ['1969-12-31 00:00:00'],
        ];
    }

    public function test_overdue_precedes_due_soon_and_stale_still_works(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-28 12:00:00', 'UTC'));
        $groups = [
            ['id' => 'mixed', 'label' => 'Mixed', 'items' => [
                ['statusType' => 'NEW', 'dateToFinish' => '2026-11-29 12:00:00'],
                ['statusType' => 'DONE', 'dateToFinish' => '2026-11-27 12:00:00'],
            ]],
            ['id' => 'stale', 'label' => 'Stale', 'items' => [
                ['statusType' => 'NEW', 'modified' => '2026-11-14 12:00:00'],
            ]],
            ['id' => 'empty', 'label' => 'Empty', 'items' => []],
        ];

        $result = $this->service()->getStatusBreakdownBySwimlane($groups, ['NEW' => 'New', 'DONE' => 'Done'], 'statusType');

        $this->assertSame('overdue', $result['mixed']['timeAlert']);
        $this->assertSame(['NEW' => 1, 'DONE' => 1], $result['mixed']['statusCounts']);
        $this->assertSame('stale', $result['stale']['timeAlert']);
        $this->assertNull($result['empty']['timeAlert']);
    }

    /** @dataProvider activityDates */
    public function test_staleness_uses_the_dates_returned_by_the_repository(array $dates, ?string $expected): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-28 12:00:00', 'UTC'));
        $groups = [['id' => 'launch', 'label' => 'Launch', 'items' => [['status' => 1] + $dates]]];
        $result = $this->service()->getStatusBreakdownBySwimlane($groups, [1 => 'New']);
        $this->assertSame($expected, $result['launch']['timeAlert']);
    }

    public static function activityDates(): array
    {
        return [
            'recent edit' => [['modified' => '2026-11-28 12:00:00'], null],
            'old edit' => [['modified' => '2026-11-14 12:00:00'], 'stale'],
            'not yet fourteen days' => [['modified' => '2026-11-14 12:00:01'], null],
            'new unedited ticket' => [['modified' => null, 'date' => '2026-11-28 12:00:00'], null],
            'old unedited ticket' => [['modified' => null, 'date' => '2026-11-14 12:00:00'], 'stale'],
            'zero edit date falls back to creation' => [['modified' => '0000-00-00 00:00:00', 'date' => '2026-11-28 12:00:00'], null],
            'unknown activity' => [[], null],
        ];
    }

    public function test_database_dates_are_compared_as_utc_in_a_non_utc_application(): void
    {
        $timezone = date_default_timezone_get();
        date_default_timezone_set('America/Los_Angeles');
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-28 12:00:00', 'UTC'));
        try {
            $groups = [
                ['id' => 'past', 'label' => 'Past', 'items' => [
                    ['status' => 1, 'dateToFinish' => '2026-11-28 11:00:00'],
                ]],
                ['id' => 'boundary', 'label' => 'Boundary', 'items' => [
                    ['status' => 1, 'dateToFinish' => '2026-12-01 12:00:00'],
                ]],
                ['id' => 'stale', 'label' => 'Stale', 'items' => [
                    ['status' => 1, 'modified' => '2026-11-14 12:00:00'],
                ]],
            ];
            $result = $this->service()->getStatusBreakdownBySwimlane($groups, [1 => 'New']);
            $this->assertSame('overdue', $result['past']['timeAlert']);
            $this->assertSame('dueSoon', $result['boundary']['timeAlert']);
            $this->assertSame('stale', $result['stale']['timeAlert']);
        } finally {
            date_default_timezone_set($timezone);
        }
    }

    private function service(array $tickets = []): Tickets
    {
        return new Tickets(
            language: $this->make(Language::class),
            ticketRepository: $this->make(TicketRepository::class, ['getAllBySearchCriteria' => fn () => $tickets]),
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(Setting::class),
            projectService: $this->make(Projects::class),
            timesheetService: $this->make(Timesheets::class),
            sprintService: $this->make(Sprints::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class),
            commentService: $this->make(Comments::class),
            clientService: $this->make(Clients::class),
            projectModules: $this->allModulesOn(),
        );
    }
}
