<?php

namespace Unit\app\Domain\Timesheets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Unit tests for the Timesheets service helpers extracted during the
 * thin-controller refactor (getUsersTickets, validateAndSaveTime,
 * resolveShowAllTicketFilter, getWeeklyTimesheetsWithTicketIds).
 */
class TimesheetsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        // Session values required by dtHelper() and Auth role checks.
        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);
        session(['userdata.id' => 1]);

        $envMock = $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]);
        app()->instance(EnvironmentCore::class, $envMock);

        $langMock = $this->createMock(LanguageCore::class);
        $langMock->method('__')->willReturnCallback(function ($index) {
            $map = [
                'language.dateformat' => 'Y-m-d',
                'language.timeformat' => 'H:i',
            ];

            return $map[$index] ?? $index;
        });
        app()->instance(LanguageCore::class, $langMock);

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));
    }

    /**
     * Builds a real Timesheets service with each dependency stubbable.
     */
    private function makeService(
        ?TimesheetRepository $timesheetsRepo = null,
        ?UserRepository $userRepo = null,
        ?TicketRepository $ticketRepo = null,
    ): TimesheetService {
        return new TimesheetService(
            $timesheetsRepo ?? $this->make(TimesheetRepository::class),
            $userRepo ?? $this->make(UserRepository::class),
            $ticketRepo ?? $this->make(TicketRepository::class),
        );
    }

    public function test_get_users_tickets_normalizes_false_to_empty_array(): void
    {
        $ticketRepo = $this->make(TicketRepository::class, [
            'getUsersTickets' => fn () => false,
        ]);

        $result = $this->makeService(ticketRepo: $ticketRepo)->getUsersTickets(1, -1);

        $this->assertSame([], $result);
    }

    public function test_get_users_tickets_passes_through_array_result(): void
    {
        $tickets = [['id' => 5], ['id' => 9]];
        $ticketRepo = $this->make(TicketRepository::class, [
            'getUsersTickets' => fn () => $tickets,
        ]);

        $result = $this->makeService(ticketRepo: $ticketRepo)->getUsersTickets(1, -1);

        $this->assertSame($tickets, $result);
    }

    public function test_validate_and_save_time_returns_no_ticket_when_ticket_missing(): void
    {
        $addCalls = 0;
        $repo = $this->make(TimesheetRepository::class, [
            'addTime' => function () use (&$addCalls) {
                $addCalls++;
            },
        ]);

        $values = $this->makeService(timesheetsRepo: $repo)->getDefaultTimeValues();
        $status = $this->makeService(timesheetsRepo: $repo)->validateAndSaveTime($values);

        $this->assertSame('NO_TICKET', $status);
        $this->assertSame(0, $addCalls, 'Invalid time must never reach the repository');
    }

    public function test_validate_and_save_time_returns_no_kind_when_kind_missing(): void
    {
        $service = $this->makeService();
        $values = $service->getDefaultTimeValues();
        $values['ticket'] = 3;
        $values['project'] = 2;

        $this->assertSame('NO_KIND', $service->validateAndSaveTime($values));
    }

    public function test_validate_and_save_time_returns_no_date_when_date_missing(): void
    {
        $service = $this->makeService();
        $values = $service->getDefaultTimeValues();
        $values['ticket'] = 3;
        $values['project'] = 2;
        $values['kind'] = 'DEVELOPMENT';

        $this->assertSame('NO_DATE', $service->validateAndSaveTime($values));
    }

    public function test_validate_and_save_time_returns_no_hours_when_hours_invalid(): void
    {
        $service = $this->makeService();
        $values = $service->getDefaultTimeValues();
        $values['ticket'] = 3;
        $values['project'] = 2;
        $values['kind'] = 'DEVELOPMENT';
        $values['date'] = '2026-05-29 00:00:00';
        $values['hours'] = 0;

        $this->assertSame('NO_HOURS', $service->validateAndSaveTime($values));
    }

    public function test_resolve_ticket_filter_returns_minus_one_when_no_filters(): void
    {
        $this->assertSame('-1', $this->makeService()->resolveShowAllTicketFilter(-1, -1, null));
    }

    public function test_resolve_ticket_filter_keeps_ticket_when_project_matches(): void
    {
        // Project 7 selected, ticket on project 7 -> keep the ticket filter.
        $this->assertSame('42', $this->makeService()->resolveShowAllTicketFilter(7, '42', 7));
    }

    public function test_resolve_ticket_filter_collapses_on_project_mismatch(): void
    {
        // Ticket belongs to project 3 but filter is project 7 -> mismatch -> '-1'.
        $this->assertSame('-1', $this->makeService()->resolveShowAllTicketFilter(7, '42', 3));
    }

    public function test_resolve_ticket_filter_collapses_when_no_project_selected(): void
    {
        // No project selected (-1) but a ticket filter set -> '-1'.
        $this->assertSame('-1', $this->makeService()->resolveShowAllTicketFilter(-1, '42', null));
    }

    public function test_resolve_ticket_filter_ignores_missing_ticket_project(): void
    {
        // Ticket not accessible (null project id) -> no mismatch, keep ticket filter.
        $this->assertSame('42', $this->makeService()->resolveShowAllTicketFilter(7, '42', null));
    }

    public function test_get_weekly_timesheets_with_ticket_ids_derives_existing_ids(): void
    {
        $fromDate = dtHelper()->userNow()->startOfWeek()->setToDbTimezone();
        $workDate = $fromDate->format('Y-m-d H:i:s');

        $rows = [
            [
                'ticketId' => 11,
                'kind' => 'DEVELOPMENT',
                'clientName' => 'Acme',
                'name' => 'Project A',
                'headline' => 'Task A',
                'workDate' => $workDate,
                'hours' => 2,
                'description' => 'work',
            ],
            [
                'ticketId' => 22,
                'kind' => 'TESTING',
                'clientName' => 'Acme',
                'name' => 'Project A',
                'headline' => 'Task B',
                'workDate' => $workDate,
                'hours' => 1,
                'description' => 'qa',
            ],
        ];

        $repo = $this->make(TimesheetRepository::class, [
            'getWeeklyTimesheets' => fn () => $rows,
        ]);

        $result = $this->makeService(timesheetsRepo: $repo)
            ->getWeeklyTimesheetsWithTicketIds(-1, $fromDate, 1);

        $this->assertArrayHasKey('timesheets', $result);
        $this->assertArrayHasKey('existingTicketIds', $result);
        $this->assertSame([11, 22], array_values($result['existingTicketIds']));
    }
}
