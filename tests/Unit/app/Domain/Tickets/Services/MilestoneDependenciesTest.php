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
 * A milestone can depend on several milestones (#268): the first one is stored in
 * zp_tickets.milestoneid, the others as depends_on relationships. Saving rejects dependencies that
 * would form a cycle and drops ids that are not milestones of the milestone's project.
 */
class MilestoneDependenciesTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** Milestones of project 7 — the only ids a dependency may point at. */
    private const PROJECT_MILESTONES = [9, 11, 12, 13, 20, 21];

    /** @var array<int, array<string, mixed>> */
    private array $updatedValues = [];

    /** @var array<int, array<string, mixed>> */
    private array $addedValues = [];

    /** @var array<int, array{0: int, 1: array<int, int>}> */
    private array $storedExtras = [];

    protected function setUp(): void
    {
        parent::setUp();

        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);
        session(['userdata' => ['id' => 1, 'role' => 'owner', 'name' => 'Caller']]);

        app()->instance(EnvironmentCore::class, $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]));
        app()->instance(LanguageCore::class, $this->make(LanguageCore::class, ['__' => fn ($key) => $key]));

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));

        $this->updatedValues = [];
        $this->addedValues = [];
        $this->storedExtras = [];
    }

    /**
     * @param  array<int, array<int, int>>  $graph  Dependencies of project 7's milestones, keyed by milestone id.
     * @param  array<int, int>  $storedDependencies  Dependencies stored on milestone 9.
     */
    private function buildService(array $graph = [], array $storedDependencies = []): TicketsService
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicket' => fn ($id) => $this->make(TicketModel::class, [
                'id' => (int) $id,
                'projectId' => 7,
                'type' => 'milestone',
                'headline' => 'M'.$id,
                'status' => 3,
                'milestoneid' => $storedDependencies[0] ?? '',
                'milestoneDependencies' => $storedDependencies,
            ]),
            'filterMilestoneIdsInProject' => fn (array $ids, int $projectId) => $projectId === 7
                ? array_values(array_filter($ids, fn ($id) => in_array($id, self::PROJECT_MILESTONES, true)))
                : [],
            'getProjectMilestoneDependencies' => fn () => $graph,
            'getStateLabels' => fn () => [3 => ['name' => 'New', 'statusType' => 'NEW']],
            'updateTicket' => function (array $values) {
                $this->updatedValues[] = $values;

                return true;
            },
            'addTicket' => function (array $values) {
                $this->addedValues[] = $values;

                return 42;
            },
            'setAdditionalMilestoneDependencies' => function (int $milestoneId, array $dependencyIds) {
                $this->storedExtras[] = [$milestoneId, $dependencyIds];

                return true;
            },
        ]);

        $service = new TicketsService(
            language: $this->make(LanguageCore::class, ['__' => fn ($key) => $key]),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class, ['isUserAssignedToProject' => fn () => true]),
            timesheetService: $this->make(TimesheetService::class),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class, ['userNow' => fn () => CarbonImmutable::now('UTC')]),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class),
            projectModules: $this->allModulesOn(),
        );
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn () => true,
            'authorize' => fn () => null,
        ]));

        return $service;
    }

    /**
     * @return array<string, mixed>
     */
    private function edit(array $overrides = []): array
    {
        return array_merge(['id' => 9, 'headline' => 'Launch', 'editorId' => 1, 'status' => 3, 'tags' => '#124F7D', 'editFrom' => '', 'editTo' => ''], $overrides);
    }

    public function test_the_first_dependency_is_the_primary_one_and_the_rest_are_stored_separately(): void
    {
        $result = $this->buildService()->quickUpdateMilestone($this->edit(['dependentMilestone' => ['11', '12', '13']]));

        $this->assertTrue($result);
        $this->assertSame(11, $this->updatedValues[0]['milestoneid']);
        $this->assertSame([[9, [12, 13]]], $this->storedExtras);
    }

    public function test_clearing_every_dependency_removes_the_primary_and_the_additional_ones(): void
    {
        $result = $this->buildService([], [11, 12])->quickUpdateMilestone($this->edit(['dependentMilestone' => ['']]));

        $this->assertTrue($result);
        $this->assertSame('', $this->updatedValues[0]['milestoneid']);
        $this->assertSame([[9, []]], $this->storedExtras);
    }

    public function test_a_single_id_still_works_for_callers_that_send_one_dependency(): void
    {
        $this->buildService()->quickUpdateMilestone($this->edit(['dependentMilestone' => '12']));

        $this->assertSame(12, $this->updatedValues[0]['milestoneid']);
        $this->assertSame([[9, []]], $this->storedExtras);
    }

    public function test_an_update_without_the_field_keeps_the_saved_dependencies(): void
    {
        $this->buildService([], [11, 12])->quickUpdateMilestone($this->edit());

        $this->assertSame(11, $this->updatedValues[0]['milestoneid']);
        $this->assertSame([[9, [12]]], $this->storedExtras);
    }

    public function test_self_references_duplicates_and_foreign_milestones_are_dropped(): void
    {
        $this->buildService()->quickUpdateMilestone($this->edit(['dependentMilestone' => [9, 11, 11, 99, 'x', 12]]));

        $this->assertSame(11, $this->updatedValues[0]['milestoneid']);
        $this->assertSame([[9, [12]]], $this->storedExtras);
    }

    public function test_a_direct_cycle_is_rejected_without_writing(): void
    {
        // 20 already depends on 9, so 9 cannot depend on 20.
        $result = $this->buildService([9 => [], 20 => [9]])->quickUpdateMilestone($this->edit(['dependentMilestone' => [11, 20]]));

        $this->assertSame('error', $result['status']);
        $this->assertSame([], $this->updatedValues);
        $this->assertSame([], $this->storedExtras);
    }

    public function test_a_cycle_through_other_milestones_is_rejected(): void
    {
        // 21 -> 20 -> 9: 9 cannot depend on 21.
        $result = $this->buildService([9 => [], 20 => [9], 21 => [20]])->quickUpdateMilestone($this->edit(['dependentMilestone' => [21]]));

        $this->assertSame('error', $result['status']);
        $this->assertSame([], $this->updatedValues);
    }

    public function test_a_diamond_is_not_a_cycle(): void
    {
        // 9 depends on 20 and 21, which both depend on 11.
        $result = $this->buildService([9 => [], 20 => [11], 21 => [11], 11 => []])->quickUpdateMilestone($this->edit(['dependentMilestone' => [20, 21]]));

        $this->assertTrue($result);
        $this->assertSame([[9, [21]]], $this->storedExtras);
    }

    public function test_a_new_milestone_stores_its_dependencies(): void
    {
        $result = $this->buildService()->quickAddMilestone(['headline' => 'Launch', 'projectId' => 7, 'dependentMilestone' => [12, 13, 20]]);

        $this->assertSame(42, $result);
        $this->assertSame(12, $this->addedValues[0]['milestoneid']);
        $this->assertSame([[42, [13, 20]]], $this->storedExtras);
    }

    public function test_a_new_milestone_with_one_dependency_needs_no_additional_rows(): void
    {
        $this->buildService()->quickAddMilestone(['headline' => 'Launch', 'projectId' => 7, 'dependentMilestone' => 12]);

        $this->assertSame(12, $this->addedValues[0]['milestoneid']);
        $this->assertSame([], $this->storedExtras);
    }

    public function test_a_new_milestone_without_dependencies_has_none(): void
    {
        $this->buildService()->quickAddMilestone(['headline' => 'Launch', 'projectId' => 7]);

        $this->assertSame('', $this->addedValues[0]['milestoneid']);
        $this->assertSame([], $this->storedExtras);
    }

    public function test_dependents_are_found_directly_and_through_other_milestones(): void
    {
        $milestones = [];
        foreach ([9 => [], 20 => [9], 21 => [20, 11], 11 => [], 12 => [11]] as $id => $dependencies) {
            $milestones[] = $this->make(TicketModel::class, ['id' => $id, 'milestoneDependencies' => $dependencies]);
        }

        $dependents = $this->buildService()->getMilestoneDependents(9, $milestones);

        $this->assertEqualsCanonicalizing([20, 21], $dependents);
    }
}
