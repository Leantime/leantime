<?php

namespace Unit\app\Domain\Reports\Services;

use Leantime\Core\Configuration\AppSettings as AppSettingCore;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\UI\Template as TemplateCore;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Reports\Repositories\Reports as ReportRepository;
use Leantime\Domain\Reports\Services\Reports;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Leantime\Domain\Sprints\Models\Sprints as SprintModel;
use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * Unit tests for the sprint-burndown selection logic extracted from the
 * Reports\Controllers\Show controller into the Reports service.
 */
class ReportsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds the Reports service with every constructor dependency stubbed,
     * injecting the provided Sprints service (the only dependency the method
     * under test actually exercises).
     */
    private function makeService(SprintService $sprintService): Reports
    {
        return new Reports(
            $this->make(TemplateCore::class),
            $this->make(AppSettingCore::class),
            $this->make(EnvironmentCore::class),
            $this->make(ProjectRepository::class),
            $this->make(SprintRepository::class),
            $this->make(ReportRepository::class),
            $this->make(SettingsService::class),
            $this->make(TicketRepository::class),
            $sprintService,
        );
    }

    /**
     * Creates a Sprints model with the given id.
     */
    private function sprint(int $id): SprintModel
    {
        $sprint = new SprintModel;
        $sprint->id = $id;

        return $sprint;
    }

    public function test_returns_false_when_project_has_no_sprints(): void
    {
        $sprintService = $this->make(SprintService::class, [
            'getAllSprints' => fn () => [],
        ]);

        $result = $this->makeService($sprintService)->getSprintBurndownForReport(7, null);

        $this->assertFalse($result['chart']);
        $this->assertFalse($result['currentSprintId']);
    }

    public function test_uses_requested_sprint_id_and_echoes_it_back(): void
    {
        $sprintService = $this->make(SprintService::class, [
            'getAllSprints' => fn () => [$this->sprint(1), $this->sprint(2)],
            'getSprint' => fn ($id) => $this->sprint($id),
            'getSprintBurndown' => fn () => [['date' => '2024-01-01']],
        ]);

        $result = $this->makeService($sprintService)->getSprintBurndownForReport(7, 2);

        $this->assertSame([['date' => '2024-01-01']], $result['chart']);
        $this->assertSame(2, $result['currentSprintId']);
    }

    public function test_requested_sprint_id_is_echoed_even_when_sprint_missing(): void
    {
        $sprintService = $this->make(SprintService::class, [
            'getAllSprints' => fn () => [$this->sprint(1)],
            'getSprint' => fn () => false,
        ]);

        $result = $this->makeService($sprintService)->getSprintBurndownForReport(7, 99);

        $this->assertFalse($result['chart']);
        $this->assertSame(99, $result['currentSprintId']);
    }

    public function test_falls_back_to_current_sprint_when_none_requested(): void
    {
        $sprintService = $this->make(SprintService::class, [
            'getAllSprints' => fn () => [$this->sprint(1), $this->sprint(5)],
            'getCurrentSprintId' => fn () => 5,
            'getSprint' => fn ($id) => $this->sprint($id),
            'getSprintBurndown' => fn () => [['date' => '2024-02-02']],
        ]);

        $result = $this->makeService($sprintService)->getSprintBurndownForReport(7, null);

        $this->assertSame([['date' => '2024-02-02']], $result['chart']);
        $this->assertSame(5, $result['currentSprintId']);
    }

    public function test_falls_back_to_first_sprint_when_no_current_sprint(): void
    {
        $sprintService = $this->make(SprintService::class, [
            'getAllSprints' => fn () => [$this->sprint(11), $this->sprint(12)],
            'getCurrentSprintId' => fn () => false,
            'getSprintBurndown' => fn () => [['date' => '2024-03-03']],
        ]);

        $result = $this->makeService($sprintService)->getSprintBurndownForReport(7, null);

        $this->assertSame([['date' => '2024-03-03']], $result['chart']);
        $this->assertSame(11, $result['currentSprintId']);
    }
}
