<?php

namespace Unit\app\Domain\Sprints\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Domain\Reports\Repositories\Reports as ReportRepository;
use Leantime\Domain\Sprints\Models\Sprints as SprintModel;
use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Unit\TestCase;

/**
 * Unit tests for the Sprints service helpers extracted during the
 * thin-controller refactor (getNewSprint, deleteSprint, and the
 * required-date validation now living in addSprint/editSprint).
 */
class SprintsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Sprints service, allowing each dependency to be
     * overridden with a stub so we can observe the persistence calls.
     */
    private function makeService(
        ?SprintRepository $sprintRepo = null,
        ?ReportRepository $reportRepo = null,
    ): SprintService {
        return new SprintService(
            $sprintRepo ?? $this->make(SprintRepository::class),
            $reportRepo ?? $this->make(ReportRepository::class),
        );
    }

    public function test_get_new_sprint_uses_default_thirteen_day_window(): void
    {
        $sprint = $this->makeService()->getNewSprint();

        $this->assertInstanceOf(SprintModel::class, $sprint);
        $this->assertNull($sprint->id);

        // The end date should be exactly 13 days after the start date.
        $this->assertSame(13, (int) $sprint->startDate->diffInDays($sprint->endDate));
    }

    public function test_delete_sprint_delegates_to_repository_and_clears_session(): void
    {
        session(['currentSprint' => '99']);

        $deletedId = null;
        $repo = $this->make(SprintRepository::class, [
            // deleteSprint now loads the sprint to authorize delete against its project.
            'getSprint' => fn () => $this->make(SprintModel::class, ['id' => 42, 'projectId' => 9]),
            'delSprint' => function ($id) use (&$deletedId) {
                $deletedId = $id;
            },
        ]);

        $service = $this->makeService(sprintRepo: $repo);
        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => fn () => null,
        ]));

        $service->deleteSprint(42);

        $this->assertSame(42, $deletedId);
        $this->assertSame('', session('currentSprint'));
    }

    public function test_add_sprint_throws_when_dates_missing(): void
    {
        $addCalls = 0;
        $repo = $this->make(SprintRepository::class, [
            'addSprint' => function () use (&$addCalls) {
                $addCalls++;

                return 1;
            },
        ]);

        // Authorization now runs before date validation (authorize-first), so allow it and assert
        // the validation still rejects the missing dates before any write reaches the repository.
        $service = $this->makeService(sprintRepo: $repo);
        $service->setPermissionService($this->make(PermissionService::class, ['authorize' => fn () => null]));

        $this->expectException(MissingParameterException::class);

        try {
            $service->addSprint(['startDate' => '', 'endDate' => '']);
        } finally {
            $this->assertSame(0, $addCalls, 'An invalid sprint must never reach the repository');
        }
    }

    public function test_edit_sprint_throws_when_end_date_missing(): void
    {
        $editCalls = 0;
        $repo = $this->make(SprintRepository::class, [
            // editSprint loads the existing sprint to authorize against its project before it
            // validates the dates, so the load must be stubbed even on the validation-failure path.
            'getSprint' => fn () => $this->make(SprintModel::class, ['id' => 5, 'projectId' => 9]),
            'editSprint' => function () use (&$editCalls) {
                $editCalls++;

                return true;
            },
        ]);

        $service = $this->makeService(sprintRepo: $repo);
        $service->setPermissionService($this->make(PermissionService::class, ['authorize' => fn () => null]));

        $this->expectException(MissingParameterException::class);

        try {
            $service->editSprint(['id' => 5, 'startDate' => '2026-01-01', 'endDate' => '']);
        } finally {
            $this->assertSame(0, $editCalls, 'An invalid update must never reach the repository');
        }
    }

    // ---------------------------------------------------------------------
    // Authorization: sprints are project-scoped; mutators authorize against the SPRINT'S
    // project (entityScoped), closing the IDOR where the id alone identified the row.
    // ---------------------------------------------------------------------

    private function denyingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'authorize' => function (): void {
                throw new AuthorizationException;
            },
        ]);
    }

    public function test_delete_sprint_is_denied_and_does_not_delete_without_permission(): void
    {
        // deleteSprint loads the sprint and authorizes sprints.delete against ITS project before
        // deleting — a denying engine must throw BEFORE the repository delete runs.
        $service = $this->makeService(sprintRepo: $this->make(SprintRepository::class, [
            'getSprint' => fn () => $this->make(SprintModel::class, ['id' => 5, 'projectId' => 9]),
            'delSprint' => function (): void {
                throw new \RuntimeException('delete must not be reached when denied');
            },
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->deleteSprint(5);
    }

    public function test_add_sprint_is_denied_without_create_permission(): void
    {
        session(['currentProject' => 9]);

        $service = $this->makeService(sprintRepo: $this->make(SprintRepository::class, [
            'addSprint' => function () {
                throw new \RuntimeException('add must not be reached when denied');
            },
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->addSprint(['startDate' => '2026-01-01', 'endDate' => '2026-01-14', 'projectId' => 9]);
    }

    public function test_get_sprint_is_denied_when_user_cannot_view_its_project(): void
    {
        // Read-side IDOR fence: getSprint loads the sprint, then authorizes VIEW against ITS project
        // (not the session project). A denying engine must throw before any cross-project sprint
        // metadata (name/dates/projectId) is returned.
        $service = $this->makeService(sprintRepo: $this->make(SprintRepository::class, [
            'getSprint' => fn () => $this->make(SprintModel::class, ['id' => 7, 'projectId' => 9]),
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->getSprint(7);
    }

    public function test_get_sprint_returns_the_sprint_when_view_is_allowed(): void
    {
        $service = $this->makeService(sprintRepo: $this->make(SprintRepository::class, [
            'getSprint' => fn () => $this->make(SprintModel::class, ['id' => 7, 'projectId' => 9]),
        ]));
        $service->setPermissionService($this->make(PermissionService::class, ['authorize' => fn () => null]));

        $this->assertSame(7, $service->getSprint(7)->id);
    }

    public function test_get_sprint_returns_false_for_unknown_id_without_authorizing(): void
    {
        // A missing sprint short-circuits to false BEFORE authorize, so there is no enumeration
        // oracle (allowed vs denied looks identical for a non-existent id) and no false lockout.
        $authorizeCalls = 0;
        $service = $this->makeService(sprintRepo: $this->make(SprintRepository::class, [
            'getSprint' => fn () => false, // repo returns false (not null) for a missing row
        ]));
        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => function () use (&$authorizeCalls): void {
                $authorizeCalls++;
            },
        ]));

        $this->assertFalse($service->getSprint(999));
        $this->assertSame(0, $authorizeCalls, 'A non-existent sprint must short-circuit before authorize');
    }

    public function test_get_all_sprints_never_queries_every_project(): void
    {
        $queried = [];
        $service = $this->makeService(sprintRepo: $this->make(SprintRepository::class, [
            'getAllSprints' => function ($projectId) use (&$queried) {
                $queried[] = $projectId;

                return [];
            },
        ]));
        $service->setPermissionService($this->make(PermissionService::class, ['currentUserCan' => fn () => true]));

        // No session project: a null project must not become the unscoped "all projects" query.
        session(['currentProject' => null]);
        $this->assertSame([], $service->getAllSprints());
        $this->assertSame([], $service->getAllSprints(0));
        $this->assertSame([], $service->getAllSprints(-3));
        $this->assertSame([], $queried);

        // Null resolves to the session project.
        session(['currentProject' => 9]);
        $service->getAllSprints();
        $this->assertSame([9], $queried);
    }

    public function test_get_all_sprints_is_empty_for_a_project_the_caller_cannot_view(): void
    {
        $service = $this->makeService(sprintRepo: $this->make(SprintRepository::class, [
            'getAllSprints' => function () {
                throw new \RuntimeException('must not list sprints of a project the caller cannot view');
            },
        ]));
        $service->setPermissionService($this->make(PermissionService::class, ['currentUserCan' => fn () => false]));

        $this->assertSame([], $service->getAllSprints(7));
    }

    public function test_sprint_burndown_authorizes_the_reloaded_sprints_project(): void
    {
        $authorizedProject = null;
        $service = $this->makeService(
            sprintRepo: $this->make(SprintRepository::class, [
                // The stored sprint 7 belongs to project 4, whatever the caller's model claims.
                'getSprint' => fn () => $this->make(SprintModel::class, ['id' => 7, 'projectId' => 4]),
            ]),
            reportRepo: $this->make(ReportRepository::class, [
                'getSprintReport' => function () {
                    throw new \RuntimeException('report data must not load before authorization');
                },
            ]),
        );
        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => function (string $key, ?int $projectId = null) use (&$authorizedProject): void {
                $authorizedProject = $projectId;

                throw new AuthorizationException;
            },
        ]));

        try {
            $service->getSprintBurndown($this->make(SprintModel::class, ['id' => 7, 'projectId' => 9]));
            $this->fail('getSprintBurndown must authorize the sprint\'s real project');
        } catch (AuthorizationException) {
            $this->assertSame(4, $authorizedProject);
        }
    }

    /**
     * The session sprint filter can hold a sprint id or a non-id selection such as "all" or
     * "backlog"; only a positive numeric id is a current sprint (report burndown relied on this).
     *
     * @dataProvider currentSprintSessionValues
     */
    public function test_get_current_sprint_id_returns_only_real_sprint_ids(mixed $sessionValue, int|false $expected): void
    {
        session(['currentSprint' => $sessionValue]);

        $this->assertSame($expected, $this->makeService()->getCurrentSprintId(9));
    }

    /**
     * @return array<string, array{mixed, int|false}>
     */
    public static function currentSprintSessionValues(): array
    {
        return [
            'numeric string id' => ['12', 12],
            'integer id' => [7, 7],
            'all' => ['all', false],
            'backlog' => ['backlog', false],
            'empty' => ['', false],
            'null' => [null, false],
            'zero' => ['0', false],
        ];
    }
}
