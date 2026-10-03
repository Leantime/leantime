<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
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
}
