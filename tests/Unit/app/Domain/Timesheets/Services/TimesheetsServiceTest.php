<?php

namespace Unit\app\Domain\Timesheets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Timesheets\Permissions\TimesheetsPermissions;
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

    /** Permission stub that grants everything (default for the non-authz helper tests). */
    private function allowingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'authorize' => fn () => null,
            'currentUserCan' => fn () => true,
        ]);
    }

    /**
     * Permission stub that grants a specific allow-list of keys (others denied). Used to model a
     * non-manager (editor): timesheets.view/create/edit/delete granted, timesheets.manage denied.
     *
     * @param  array<int, string>  $granted
     */
    private function permissionsGranting(array $granted): PermissionService
    {
        return $this->make(PermissionService::class, [
            'authorize' => function (string $key) use ($granted): void {
                if (! in_array($key, $granted, true)) {
                    throw new AuthorizationException;
                }
            },
            'currentUserCan' => fn (string $key) => in_array($key, $granted, true),
        ]);
    }

    /**
     * Builds a real Timesheets service with each dependency stubbable and a permission service
     * (defaults to allow-all).
     */
    private function makeService(
        ?TimesheetRepository $timesheetsRepo = null,
        ?UserRepository $userRepo = null,
        ?TicketRepository $ticketRepo = null,
        ?PermissionService $perms = null,
    ): TimesheetService {
        $service = new TimesheetService(
            $timesheetsRepo ?? $this->make(TimesheetRepository::class),
            $userRepo ?? $this->make(UserRepository::class),
            $ticketRepo ?? $this->make(TicketRepository::class),
        );
        $service->setPermissionService($perms ?? $this->allowingPermissions());

        return $service;
    }

    // Non-manager (editor) verb set: own-time keys, but NOT timesheets.manage.
    private const EDITOR_KEYS = [
        TimesheetsPermissions::VIEW,
        TimesheetsPermissions::CREATE,
        TimesheetsPermissions::EDIT,
        TimesheetsPermissions::DELETE,
    ];

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

    // ---------------------------------------------------------------------
    // Ownership / fail-closed authorization (session user id = 1).
    // ---------------------------------------------------------------------

    public function test_get_timesheet_returns_own_entry_for_editor(): void
    {
        $repo = $this->make(TimesheetRepository::class, [
            'getTimesheet' => fn () => ['id' => 5, 'userId' => 1, 'projectId' => 9],
        ]);

        $result = $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))->getTimesheet(5);

        $this->assertSame(5, $result['id']);
    }

    public function test_get_timesheet_soft_denies_another_users_entry_for_editor(): void
    {
        // Editor (no timesheets.manage) reading another user's entry → false, same as not-found.
        $repo = $this->make(TimesheetRepository::class, [
            'getTimesheet' => fn () => ['id' => 5, 'userId' => 2, 'projectId' => 9],
        ]);

        $result = $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))->getTimesheet(5);

        $this->assertFalse($result);
    }

    public function test_get_timesheet_returns_false_for_missing(): void
    {
        $repo = $this->make(TimesheetRepository::class, ['getTimesheet' => fn () => false]);

        $this->assertFalse($this->makeService(timesheetsRepo: $repo)->getTimesheet(999));
    }

    public function test_update_invoices_requires_manage(): void
    {
        $updated = 0;
        $repo = $this->make(TimesheetRepository::class, [
            'updateInvoices' => function () use (&$updated) {
                $updated++;

                return true;
            },
        ]);

        $this->expectException(AuthorizationException::class);
        try {
            $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))->updateInvoices([1], [], []);
        } finally {
            $this->assertSame(0, $updated, 'Invoices must not be touched without timesheets.manage');
        }
    }

    public function test_get_all_for_own_user_needs_only_view(): void
    {
        $repo = $this->make(TimesheetRepository::class, ['getAll' => fn () => [['id' => 1]]]);

        $result = $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))
            ->getAll(dtHelper()->userNow(), dtHelper()->userNow(), userId: 1);

        $this->assertSame([['id' => 1]], $result);
    }

    public function test_get_all_for_another_user_requires_manage(): void
    {
        $repo = $this->make(TimesheetRepository::class, ['getAll' => fn () => [['id' => 1]]]);

        $this->expectException(AuthorizationException::class);
        $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))
            ->getAll(dtHelper()->userNow(), dtHelper()->userNow(), userId: 2);
    }

    public function test_get_all_for_all_users_requires_manage(): void
    {
        // userId null = the company-wide report → manager only.
        $repo = $this->make(TimesheetRepository::class, ['getAll' => fn () => [['id' => 1]]]);

        $this->expectException(AuthorizationException::class);
        $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))
            ->getAll(dtHelper()->userNow(), dtHelper()->userNow(), userId: null);
    }

    public function test_delete_time_denies_another_users_entry_for_editor(): void
    {
        $deleted = 0;
        $repo = $this->make(TimesheetRepository::class, [
            'getTimesheet' => fn () => ['id' => 5, 'userId' => 2],
            'deleteTime' => function () use (&$deleted) {
                $deleted++;
            },
        ]);

        $result = $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))->deleteTime(5);

        $this->assertFalse($result);
        $this->assertSame(0, $deleted, "An editor must not delete another user's time");
    }

    public function test_delete_time_allows_own_entry_for_editor(): void
    {
        $deleted = 0;
        $repo = $this->make(TimesheetRepository::class, [
            'getTimesheet' => fn () => ['id' => 5, 'userId' => 1],
            'deleteTime' => function () use (&$deleted) {
                $deleted++;
            },
        ]);

        $result = $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))->deleteTime(5);

        $this->assertTrue($result);
        $this->assertSame(1, $deleted);
    }

    public function test_users_ticket_hours_soft_denies_another_user_for_editor(): void
    {
        $loaded = 0;
        $repo = $this->make(TimesheetRepository::class, [
            'getUsersTicketHours' => function () use (&$loaded) {
                $loaded++;

                return 7;
            },
        ]);

        $result = $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))->getUsersTicketHours(3, 2);

        $this->assertSame(0, $result);
        $this->assertSame(0, $loaded, "An editor must not read another user's ticket hours");
    }

    public function test_users_tickets_soft_denies_another_user_for_editor(): void
    {
        $repo = $this->make(TimesheetRepository::class);
        $ticketRepo = $this->make(TicketRepository::class, ['getUsersTickets' => fn () => [['id' => 1]]]);

        $result = $this->makeService(timesheetsRepo: $repo, ticketRepo: $ticketRepo, perms: $this->permissionsGranting(self::EDITOR_KEYS))
            ->getUsersTickets(2, -1);

        $this->assertSame([], $result);
    }

    public function test_add_time_pins_non_manager_to_own_user(): void
    {
        $captured = null;
        $repo = $this->make(TimesheetRepository::class, [
            'addTime' => function ($values) use (&$captured) {
                $captured = $values;
            },
        ]);

        // Editor (no manage) tries to log for user 2 → pinned to self (user 1).
        $this->makeService(timesheetsRepo: $repo, perms: $this->permissionsGranting(self::EDITOR_KEYS))
            ->addTime(['userId' => 2, 'hours' => 1]);

        $this->assertSame(1, $captured['userId'], 'A non-manager must be pinned to their own userId');
    }
}
