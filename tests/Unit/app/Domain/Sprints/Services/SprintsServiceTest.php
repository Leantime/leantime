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

        $this->expectException(MissingParameterException::class);

        try {
            $this->makeService(sprintRepo: $repo)->addSprint(['startDate' => '', 'endDate' => '']);
        } finally {
            $this->assertSame(0, $addCalls, 'An invalid sprint must never reach the repository');
        }
    }

    public function test_edit_sprint_throws_when_end_date_missing(): void
    {
        $editCalls = 0;
        $repo = $this->make(SprintRepository::class, [
            'editSprint' => function () use (&$editCalls) {
                $editCalls++;

                return true;
            },
        ]);

        $this->expectException(MissingParameterException::class);

        try {
            $this->makeService(sprintRepo: $repo)->editSprint(['id' => 5, 'startDate' => '2026-01-01', 'endDate' => '']);
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
}
