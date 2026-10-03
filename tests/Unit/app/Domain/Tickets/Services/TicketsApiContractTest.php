<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\TicketHistory;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Unit\TestCase;

/**
 * Contract tests for the ticket write/read paths used by JSON-RPC and MCP clients.
 */
class TicketsApiContractTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        if (! defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost');
        }

        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);

        app()->instance(EnvironmentCore::class, $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]));

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));

        session(['userdata' => ['id' => 1, 'role' => 'admin', 'name' => 'Admin']]);
    }

    /**
     * Service with stubbed repository methods and a permission engine that allows everything.
     *
     * @param  array<string, mixed>  $ticketRepoStubs
     */
    private function service(array $ticketRepoStubs, ?PermissionService $permissions = null): TicketsService
    {
        $service = new TicketsService(
            language: $this->make(LanguageCore::class, ['__' => fn ($key) => $key]),
            ticketRepository: $this->make(TicketRepository::class, $ticketRepoStubs),
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class, [
                'isUserAssignedToProject' => fn () => true,
                'notifyProjectUsers' => fn () => null,
            ]),
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class, ['userNow' => fn () => CarbonImmutable::now('UTC')]),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class)
        );

        $service->setPermissionService($permissions ?? $this->make(PermissionService::class, [
            'currentUserCan' => fn () => true,
            'authorize' => fn () => null,
        ]));

        return $service;
    }

    /** A stored subtask with every field populated. */
    private function storedSubtask(): TicketModel
    {
        return $this->make(TicketModel::class, [
            'id' => 977,
            'projectId' => 9,
            'headline' => 'Stored headline',
            'type' => 'subtask',
            'description' => 'Stored description',
            'status' => 3,
            'priority' => '2',
            'tags' => 'alpha,beta',
            'dateToFinish' => '2026-07-30 23:59:59',
            'editFrom' => '2026-07-28 08:00:00',
            'editTo' => '2026-07-29 17:00:00',
            'dependingTicketId' => 974,
            'milestoneid' => 12,
            'editorId' => '4',
            'sprint' => 3,
            'storypoints' => 5,
            'planHours' => 8,
            'hourRemaining' => 4,
            'acceptanceCriteria' => 'Stored criteria',
            'collaborators' => [6, 7],
        ]);
    }

    // ---------------------------------------------------------------------
    // #3701 / #3702(b): partial updates preserve omitted fields and parent links
    // ---------------------------------------------------------------------

    public function test_update_ticket_with_only_status_preserves_every_other_field(): void
    {
        $written = null;
        $service = $this->service([
            'getTicket' => fn () => $this->storedSubtask(),
            'updateTicket' => function ($values) use (&$written) {
                $written = $values;

                return true;
            },
        ]);

        $result = $service->updateTicket(['id' => 977, 'status' => 4]);

        $this->assertTrue($result);
        $this->assertSame(4, $written['status']);
        $this->assertSame(974, $written['dependingTicketId'], 'a status-only update must not orphan the subtask');
        $this->assertSame('subtask', $written['type']);
        $this->assertSame(9, $written['projectId']);
        $this->assertSame('Stored description', $written['description']);
        $this->assertSame('2', $written['priority']);
        $this->assertSame('alpha,beta', $written['tags']);
        $this->assertSame('2026-07-30 23:59:59', $written['dateToFinish'], 'stored dates are kept verbatim, not re-parsed');
        $this->assertSame('2026-07-28 08:00:00', $written['editFrom']);
        $this->assertSame(12, $written['milestoneid']);
        $this->assertSame('4', $written['editorId']);
        $this->assertSame([6, 7], $written['collaborators']);
        $this->assertSame('Stored criteria', $written['acceptanceCriteria']);
    }

    public function test_update_ticket_still_clears_a_field_that_is_sent_empty(): void
    {
        $written = null;
        $service = $this->service([
            'getTicket' => fn () => $this->storedSubtask(),
            'updateTicket' => function ($values) use (&$written) {
                $written = $values;

                return true;
            },
        ]);

        $service->updateTicket(['id' => 977, 'description' => '', 'dependingTicketId' => '', 'collaborators' => []]);

        $this->assertSame('', $written['description']);
        $this->assertSame('', $written['dependingTicketId']);
        $this->assertSame([], $written['collaborators']);
        $this->assertSame('alpha,beta', $written['tags']);
    }

    public function test_update_ticket_keeps_the_tickets_own_project_instead_of_the_session_project(): void
    {
        session(['currentProject' => 55]);

        $written = null;
        $service = $this->service([
            'getTicket' => fn () => $this->storedSubtask(),
            'updateTicket' => function ($values) use (&$written) {
                $written = $values;

                return true;
            },
        ]);

        $service->updateTicket(['id' => 977, 'headline' => 'Renamed']);

        $this->assertSame(9, $written['projectId']);
        $this->assertSame('Renamed', $written['headline']);
    }

    public function test_upsert_subtask_update_preserves_fields_the_caller_did_not_send(): void
    {
        $written = null;
        $service = $this->service([
            'getTicket' => fn ($id) => (int) $id === 974
                ? $this->make(TicketModel::class, ['id' => 974, 'projectId' => 9, 'milestoneid' => 12])
                : $this->storedSubtask(),
            'getStateLabels' => fn () => [3 => ['name' => 'status.new', 'statusType' => 'NEW'], 4 => ['name' => 'status.in_progress', 'statusType' => 'INPROGRESS']],
            'updateTicket' => function ($values) use (&$written) {
                $written = $values;

                return true;
            },
        ]);

        $this->assertTrue($service->upsertSubtask(['subtaskId' => 977, 'status' => 4], ['id' => 974]));

        $this->assertSame(4, $written['status']);
        $this->assertSame('Stored headline', $written['headline']);
        $this->assertSame('Stored description', $written['description']);
        $this->assertSame('alpha,beta', $written['tags']);
        $this->assertSame(974, $written['dependingTicketId']);
        $this->assertSame('subtask', $written['type']);
    }

    // ---------------------------------------------------------------------
    // #3702: one status contract for create/update
    // ---------------------------------------------------------------------

    /** Seed-style labels: 3 New, 4 In Progress, 0 Done, plus a custom label. */
    private function seedLabels(): array
    {
        return [
            3 => ['name' => 'status.new', 'statusType' => 'NEW', 'sortKey' => 1],
            4 => ['name' => 'status.in_progress', 'statusType' => 'INPROGRESS', 'sortKey' => 3],
            7 => ['name' => 'Needs Review', 'statusType' => 'INPROGRESS', 'sortKey' => 4],
            0 => ['name' => 'status.done', 'statusType' => 'DONE', 'sortKey' => 5],
        ];
    }

    /**
     * @return array{0: TicketsService, 1: \Closure(): ?array}
     */
    private function creatingService(): array
    {
        $created = null;
        $service = $this->service([
            'getStateLabels' => fn () => $this->seedLabels(),
            'getTicket' => fn ($id) => (int) $id === 974 ? $this->make(TicketModel::class, ['id' => 974, 'projectId' => 9]) : false,
            'addTicket' => function ($values) use (&$created) {
                $created = $values;

                return 101;
            },
        ]);

        return [$service, function () use (&$created) {
            return $created;
        }];
    }

    public function test_add_ticket_resolves_a_status_name_instead_of_casting_it_to_done(): void
    {
        [$service, $created] = $this->creatingService();

        $this->assertSame(101, $service->addTicket(['headline' => 'Sub', 'projectId' => 9, 'status' => 'New', 'dependingTicketId' => 974, 'type' => 'subtask']));
        $this->assertSame(3, $created()['status']);
    }

    public function test_add_ticket_accepts_status_types_custom_labels_and_numeric_strings(): void
    {
        [$service, $created] = $this->creatingService();

        $service->addTicket(['headline' => 'A', 'projectId' => 9, 'status' => 'inprogress']);
        $this->assertSame(4, $created()['status']);

        $service->addTicket(['headline' => 'B', 'projectId' => 9, 'status' => 'needs review']);
        $this->assertSame(7, $created()['status']);

        $service->addTicket(['headline' => 'C', 'projectId' => 9, 'status' => '0']);
        $this->assertSame(0, $created()['status']);
    }

    public function test_add_ticket_defaults_to_the_projects_new_status(): void
    {
        [$service, $created] = $this->creatingService();

        $service->addTicket(['headline' => 'Sub', 'projectId' => 9, 'status' => '']);
        $this->assertSame(3, $created()['status']);
    }

    public function test_add_ticket_rejects_an_unknown_status_string(): void
    {
        [$service] = $this->creatingService();

        try {
            $service->addTicket(['headline' => 'Sub', 'projectId' => 9, 'status' => 'Bogus']);
            $this->fail('an unknown status must be rejected, not stored as 0 (Done)');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->getErrorData());
            $this->assertStringContainsString('Bogus', $e->getClientMessage());
        }
    }

    public function test_add_ticket_rejects_a_parent_the_caller_cannot_see(): void
    {
        [$service] = $this->creatingService();

        $this->expectException(ValidationException::class);

        $service->addTicket(['headline' => 'Sub', 'projectId' => 9, 'dependingTicketId' => 5555]);
    }

    public function test_patch_resolves_status_names_and_drops_an_empty_status(): void
    {
        $patched = [];
        $service = $this->service([
            'getStateLabels' => fn () => $this->seedLabels(),
            'getTicket' => fn () => $this->storedSubtask(),
            'patchTicket' => function ($id, $params) use (&$patched) {
                $patched[] = $params;

                return true;
            },
        ]);

        $service->patch(977, ['status' => 'In Progress']);
        $service->patch(977, ['status' => '', 'headline' => 'x']);

        $this->assertSame(['status' => 4], $patched[0]);
        $this->assertSame(['headline' => 'x'], $patched[1]);
    }

    public function test_update_ticket_with_an_empty_status_keeps_the_stored_status(): void
    {
        $written = null;
        $service = $this->service([
            'getStateLabels' => fn () => $this->seedLabels(),
            'getTicket' => fn () => $this->storedSubtask(),
            'updateTicket' => function ($values) use (&$written) {
                $written = $values;

                return true;
            },
        ]);

        $service->updateTicket(['id' => 977, 'status' => '']);

        $this->assertSame(3, $written['status']);
    }

    public function test_get_all_subtasks_is_empty_for_a_parent_the_caller_cannot_see(): void
    {
        $service = $this->service([
            'getTicket' => fn () => false,
            'getAllSubtasks' => function () {
                throw new \RuntimeException('must not list children of an invisible parent');
            },
        ]);

        $this->assertSame([], $service->getAllSubtasks(974));
    }

    // ---------------------------------------------------------------------
    // #3704: no silent no-op patches
    // ---------------------------------------------------------------------

    public function test_patch_ticket_with_only_unknown_fields_is_a_validation_error(): void
    {
        $service = $this->service([
            'getTicket' => fn () => $this->storedSubtask(),
            'patchTicket' => function () {
                throw new \RuntimeException('nothing should be written');
            },
        ]);

        try {
            $service->patchTicket(977, ['stauts' => 4]);
            $this->fail('a patch that applies no field must not report a silent false');
        } catch (ValidationException $e) {
            $this->assertSame(['stauts'], $e->getErrorData()['ignoredFields']);
            $this->assertStringContainsString('stauts', $e->getClientMessage());
        }
    }

    public function test_patch_ticket_strict_mode_rejects_any_unknown_field(): void
    {
        $service = $this->service([
            'getTicket' => fn () => $this->storedSubtask(),
            'patchTicket' => function () {
                throw new \RuntimeException('strict mode must not write a partially unknown patch');
            },
        ]);

        $this->expectException(ValidationException::class);

        $service->patchTicket(977, ['headline' => 'x', 'colour' => 'red'], strict: true);
    }

    public function test_patch_ticket_applies_known_fields_and_reports_ignored_ones(): void
    {
        $patched = null;
        $service = $this->service([
            'getTicket' => fn () => $this->storedSubtask(),
            'patchTicket' => function ($id, $params) use (&$patched) {
                $patched = $params;

                return true;
            },
        ]);

        $values = ['headline' => 'x', 'milestoneId' => 5, 'timeToFinish' => '10:00', 'colour' => 'red'];

        $this->assertSame(['colour'], $service->getIgnoredPatchFields($values));
        $this->assertTrue($service->patchTicket(977, ['headline' => 'x', 'colour' => 'red']));
        $this->assertSame('x', $patched['headline']);
    }

    public function test_patch_ticket_on_a_missing_ticket_is_not_found(): void
    {
        $service = $this->service(['getTicket' => fn () => false]);

        $this->expectException(\Leantime\Core\Exceptions\NotFoundException::class);

        $service->patchTicket(404, ['headline' => 'x']);
    }

    // ---------------------------------------------------------------------
    // #3700: server-side modifiedAfter / statusType filters
    // ---------------------------------------------------------------------

    public function test_get_all_normalizes_the_watcher_filters(): void
    {
        $criteria = null;
        $service = $this->service([
            'getAllBySearchCriteria' => function ($searchCriteria) use (&$criteria) {
                $criteria = $searchCriteria;

                return [];
            },
        ]);

        $service->getAll(['modifiedAfter' => '2026-07-27T02:00:00+02:00', 'statusType' => 'inprogress, not done'], 10);

        $this->assertSame('2026-07-27 00:00:00', $criteria['modifiedAfter'], 'ISO input is converted to a UTC database datetime');
        $this->assertSame('INPROGRESS,NOT_DONE', $criteria['statusType']);
    }

    public function test_get_all_rejects_an_unparseable_modified_after(): void
    {
        $service = $this->service(['getAllBySearchCriteria' => fn () => []]);

        $this->expectException(ValidationException::class);

        $service->getAll(['modifiedAfter' => 'yesterday-ish']);
    }

    public function test_get_all_rejects_an_unknown_status_type(): void
    {
        $service = $this->service(['getAllBySearchCriteria' => fn () => []]);

        $this->expectException(ValidationException::class);

        $service->getAll(['statusType' => 'WAITING']);
    }
}
