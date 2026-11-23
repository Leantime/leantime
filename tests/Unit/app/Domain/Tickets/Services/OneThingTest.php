<?php

namespace Unit\app\Domain\Tickets\Services;

use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\OneThing;
use Unit\TestCase;

/**
 * "Your one thing": a personal, per-day to-do choice stored in zp_settings. Covers set/get/clear,
 * range reads, strict dates, visibility (hidden or deleted to-dos never leak), and that the
 * to-do itself is never written to.
 */
class OneThingTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<string, string> In-memory zp_settings. */
    private array $store = [];

    /** @var array<int, TicketModel> */
    private array $tickets = [];

    /** @var int[] Projects whose to-dos the signed-in person may view. */
    private array $visibleProjects = [3];

    private int $settingsReads = 0;

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 7, 'role' => 'editor', 'name' => 'Editor']]);

        $this->store = [];
        $this->settingsReads = 0;
        $this->visibleProjects = [3];
        $this->tickets = [
            11 => $this->ticket(11, 'Call the food bank', 3),
            12 => $this->ticket(12, 'Order shelving', 3, 4),
            20 => $this->ticket(20, 'Hidden budget review', 9),
            30 => $this->ticket(30, 'Soft opening', 3, 3, 'milestone'),
        ];
    }

    public function test_set_then_get_returns_the_task(): void
    {
        $service = $this->service();

        $set = $service->setOneThing('2026-11-23', 11);
        $got = $service->getOneThing('2026-11-23');

        $expected = [
            'date' => '2026-11-23',
            'taskId' => 11,
            'task' => ['id' => 11, 'headline' => 'Call the food bank', 'projectId' => 3, 'status' => 3],
        ];
        $this->assertSame($expected, $set);
        $this->assertSame($expected, $got);
        $this->assertSame('{"taskId":11}', $this->store['usersettings.7.oneThing.2026-11-23']);
    }

    public function test_nothing_set_reads_as_null(): void
    {
        $this->assertNull($this->service()->getOneThing('2026-11-23'));
    }

    public function test_setting_again_replaces_the_day(): void
    {
        $service = $this->service();
        $service->setOneThing('2026-11-23', 11);
        $service->setOneThing('2026-11-23', 12);

        $this->assertSame(12, $service->getOneThing('2026-11-23')['taskId']);
    }

    public function test_clear_removes_the_day_and_is_fine_when_empty(): void
    {
        $service = $this->service();
        $service->setOneThing('2026-11-23', 11);

        $this->assertTrue($service->clearOneThing('2026-11-23'));
        $this->assertNull($service->getOneThing('2026-11-23'));
        $this->assertArrayNotHasKey('usersettings.7.oneThing.2026-11-23', $this->store);
        $this->assertTrue($service->clearOneThing('2026-11-24'));
    }

    public function test_setting_never_writes_to_the_task(): void
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicket' => fn ($id) => $this->tickets[(int) $id] ?? false,
            'patchTicket' => function () {
                throw new \RuntimeException('one thing must not change the to-do');
            },
            'updateTicket' => function () {
                throw new \RuntimeException('one thing must not change the to-do');
            },
        ]);
        $before = clone $this->tickets[11];

        $this->service($ticketRepository)->setOneThing('2026-11-23', 11);

        $this->assertEquals($before, $this->tickets[11]);
    }

    public function test_cannot_set_a_task_you_cannot_see_or_that_does_not_exist(): void
    {
        $service = $this->service();

        foreach ([20, 999, 0, -4] as $taskId) {
            try {
                $service->setOneThing('2026-11-23', $taskId);
                $this->fail("task $taskId should be refused");
            } catch (ValidationException $e) {
                // Same message for hidden and missing, so a hidden to-do's existence isn't revealed.
                $this->assertSame(["That to-do doesn't exist, or you can't see it."], $e->getErrorData()['taskId']);
            }
        }

        $this->assertSame([], $this->store);
    }

    public function test_milestones_are_not_a_one_thing(): void
    {
        $this->expectException(ValidationException::class);
        $this->service()->setOneThing('2026-11-23', 30);
    }

    public function test_a_task_you_can_no_longer_see_reads_as_null_and_is_kept(): void
    {
        $service = $this->service();
        $service->setOneThing('2026-11-23', 11);

        $this->visibleProjects = [];

        $this->assertNull($service->getOneThing('2026-11-23'));
        $this->assertSame([], $service->getOneThings('2026-11-23', '2026-11-23'));
        $this->assertArrayHasKey('usersettings.7.oneThing.2026-11-23', $this->store, 'not auto-deleted');
    }

    public function test_a_deleted_task_reads_as_null(): void
    {
        $service = $this->service();
        $service->setOneThing('2026-11-23', 11);

        unset($this->tickets[11]);

        $this->assertNull($service->getOneThing('2026-11-23'));
    }

    public function test_range_returns_set_days_oldest_first_in_one_read(): void
    {
        $service = $this->service();
        $service->setOneThing('2026-11-25', 12);
        $service->setOneThing('2026-11-23', 11);
        $service->setOneThing('2026-11-24', 11);
        $this->store['usersettings.7.oneThing.2026-11-26'] = 'not json';
        $this->settingsReads = 0;

        $range = $service->getOneThings('2026-11-22', '2026-11-28');

        $this->assertSame(['2026-11-23', '2026-11-24', '2026-11-25'], array_column($range, 'date'));
        $this->assertSame([11, 11, 12], array_column($range, 'taskId'));
        $this->assertSame(1, $this->settingsReads, 'the whole range is one settings read');
    }

    public function test_range_validation_and_cap(): void
    {
        $service = $this->service();

        $this->assertSame([], $service->getOneThings('2026-11-01', '2027-01-01'), '62 days inclusive is allowed');

        foreach ([['2026-11-24', '2026-11-23'], ['2026-11-01', '2027-01-02'], ['2026-11-1', '2026-11-02']] as [$from, $until]) {
            try {
                $service->getOneThings($from, $until);
                $this->fail("$from..$until should be refused");
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->getErrorData());
            }
        }
    }

    public function test_dates_must_be_strict_real_days(): void
    {
        $service = $this->service();

        foreach (['2026-02-30', '2026-11-23T00:00', '23/11/2026', '2026-1-5', '', 'today'] as $date) {
            try {
                $service->getOneThing($date);
                $this->fail("'$date' should be refused");
            } catch (ValidationException $e) {
                $this->assertSame(['Use a date like 2026-11-23.'], $e->getErrorData()['date']);
            }
        }
    }

    public function test_keys_are_per_person(): void
    {
        $service = $this->service();
        $service->setOneThing('2026-11-23', 11);

        session(['userdata' => ['id' => 8, 'role' => 'editor', 'name' => 'Other']]);

        $this->assertNull($service->getOneThing('2026-11-23'));
    }

    public function test_requires_a_signed_in_person(): void
    {
        session()->forget('userdata');

        $this->expectException(AuthorizationException::class);
        $this->service()->getOneThing('2026-11-23');
    }

    public function test_rpc_gate_ignores_the_session_project(): void
    {
        // The selected project (99) is one the person lost access to; these personal calls must still pass.
        session(['currentProject' => 99]);
        config(['permissions.enforce' => true]);

        $engineCalls = [];
        $enforcer = new PermissionEnforcer($this->make(PermissionService::class, [
            'currentUserCan' => function (string $key, ?int $projectId = null, ?bool $forceGlobal = null) use (&$engineCalls): bool {
                $engineCalls[] = [$key, $projectId];

                return $projectId !== 99;
            },
        ]));

        foreach (['getOneThing' => ['date' => '2026-11-23'], 'setOneThing' => ['date' => '2026-11-23', 'taskId' => 11], 'clearOneThing' => ['date' => '2026-11-23'], 'getOneThings' => ['from' => '2026-11-23', 'until' => '2026-11-24']] as $method => $params) {
            $enforcer->enforce(OneThing::class, $method, $params);
        }

        $this->assertSame([], $engineCalls, 'the gate defers to the per-to-do check instead of the session project');

        // The per-to-do check still runs against the to-do's own project.
        $this->assertSame(11, $this->service()->setOneThing('2026-11-23', 11)['taskId']);
    }

    public function test_a_nul_byte_in_a_date_is_a_field_error_not_a_crash(): void
    {
        $service = $this->service();

        foreach (["2026-11-23\0", "2026\0-11-23", "\0"] as $date) {
            try {
                $service->getOneThing($date);
                $this->fail('a NUL byte should be refused');
            } catch (ValidationException $e) {
                $this->assertSame(['Use a date like 2026-11-23.'], $e->getErrorData()['date']);
            }
        }

        $this->expectException(ValidationException::class);
        $service->getOneThings("2026-11-23\0", '2026-11-24');
    }

    public function test_task_id_parsing_is_strict(): void
    {
        $this->assertSame(5, OneThing::taskIdFrom('{"taskId":5}'));
        foreach ([false, null, '', 'x', '{"taskId":"5"}', '{"taskId":0}', '{"taskId":-2}', '[]', 5] as $stored) {
            $this->assertNull(OneThing::taskIdFrom($stored));
        }
    }

    private function service(?TicketRepository $ticketRepository = null): OneThing
    {
        $settings = $this->make(SettingRepository::class, [
            'getSetting' => fn (string $key, mixed $default = false) => $this->store[$key] ?? $default,
            'saveSetting' => function (string $key, mixed $value): bool {
                $this->store[$key] = $value;

                return true;
            },
            'deleteSetting' => function (string $key): void {
                unset($this->store[$key]);
            },
            'getSettingsForKeys' => function (array $keys, bool $useCache = true): array {
                $this->settingsReads++;

                return array_intersect_key($this->store, array_flip($keys));
            },
        ]);

        $ticketRepository ??= $this->make(TicketRepository::class, [
            'getTicket' => fn ($id) => $this->tickets[(int) $id] ?? false,
        ]);

        $service = new OneThing($settings, $ticketRepository);
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn (string $key, ?int $projectId = null): bool => $key === 'tickets.view' && in_array($projectId, $this->visibleProjects, true),
        ]));

        return $service;
    }

    private function ticket(int $id, string $headline, int $projectId, int $status = 3, string $type = 'task'): TicketModel
    {
        $ticket = new TicketModel;
        $ticket->id = $id;
        $ticket->headline = $headline;
        $ticket->projectId = $projectId;
        $ticket->status = $status;
        $ticket->type = $type;
        $ticket->editFrom = '2026-11-23 09:00:00';
        $ticket->dateToFinish = '2026-11-30 17:00:00';

        return $ticket;
    }
}
