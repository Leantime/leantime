<?php

namespace Unit\app\Domain\Sprints\Services;

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
            'delSprint' => function ($id) use (&$deletedId) {
                $deletedId = $id;
            },
        ]);

        $this->makeService(sprintRepo: $repo)->deleteSprint(42);

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
}
