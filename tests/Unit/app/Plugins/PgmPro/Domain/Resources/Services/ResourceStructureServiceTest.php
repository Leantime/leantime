<?php

declare(strict_types=1);

namespace Unit\app\Plugins\PgmPro\Domain\Resources\Services;

use Leantime\Core\Db\Db;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Plugins\PgmPro\Domain\Resources\Repositories\ResourceStructureRepository;
use Leantime\Plugins\PgmPro\Domain\Resources\Services\ResourceStructureService;
use Leantime\Plugins\PgmPro\Services\Programs;
use Unit\TestCase;

/**
 * Behaviors under test:
 *
 * getForProjects — the ResourcesGateway contract:
 *   1. Empty projectIds → empty ResourceSummary (no walk).
 *   2. Projects with no resource canvas → empty ResourceSummary (honest
 *      "resources not authored here" state, not an error).
 *   3. status='stub' PEOPLE are excluded from totals — matches the tab's
 *      teamStats split. A seeded stub defaults to capacity:40, and counting
 *      it would make the report disagree with the tab the moment anyone
 *      uses LM-Inputs seeding.
 *   4. status='stub' BUDGET LINES are excluded from totals AND from the
 *      budget[] array — stubs are 0/0 so they don't move totals, but they
 *      inflate `count(budget)` and can flip `isEmpty()`.
 *
 * Seeders — the "skip already present" contract:
 *   5. seedPeopleFromChildProjects is idempotent by userId — a second run
 *      with the same source data adds zero rows and reports the correct
 *      skipped count.
 *   6. seedBudgetFromChildProjects is idempotent by projectId — same.
 *
 * All tests avoid touching the DB via a testable subclass of the SUT that
 * overrides `findResourceCanvasIds()`; the repository and the two service
 * collaborators are stubbed with anonymous classes so the test asserts on
 * the exact call surface.
 */
class ResourceStructureServiceTest extends TestCase
{
    public function test_getForProjects_returns_empty_summary_when_projectIds_is_empty(): void
    {
        $svc = $this->makeService(canvasIds: [1], items: []);
        $summary = $svc->getForProjects([]);

        $this->assertSame([], $summary->projectIds);
        $this->assertTrue($summary->isEmpty());
    }

    public function test_getForProjects_returns_empty_summary_when_no_resource_canvas(): void
    {
        $svc = $this->makeService(canvasIds: [], items: []);
        $summary = $svc->getForProjects([1, 2]);

        $this->assertSame([1, 2], $summary->projectIds);
        $this->assertTrue($summary->isEmpty());
        $this->assertSame(0.0, $summary->totalCapacity);
    }

    public function test_getForProjects_excludes_stub_people_from_totals(): void
    {
        // Active person: capacity 40, allocated 30. Stub person: default 40.
        // The report and the tab must agree — the tab excludes stubs from
        // teamStats, so the gateway must too. Otherwise a seeded-but-unlinked
        // person shows 40h in the report and 0h in the tab.
        $svc = $this->makeService(
            canvasIds: [100],
            items: [
                100 => [
                    'people' => [
                        [
                            'id' => 1,
                            'description' => 'Sarah Chen',
                            'status' => 'active',
                            'parsedData' => ['userId' => 1, 'capacity' => 40, 'allocations' => ['7' => 30]],
                        ],
                        [
                            'id' => 2,
                            'description' => 'Unlinked seed',
                            'status' => 'stub',
                            'parsedData' => ['capacity' => 40, 'allocations' => []],
                        ],
                    ],
                ],
            ],
        );

        $summary = $svc->getForProjects([7]);

        $this->assertCount(1, $summary->people, 'Stubs must not appear in the people array');
        $this->assertSame(40.0, $summary->totalCapacity, 'Stub 40h capacity must not be counted');
        $this->assertSame(30.0, $summary->totalAllocated);
    }

    public function test_getForProjects_excludes_stub_budget_lines_from_array_and_totals(): void
    {
        $svc = $this->makeService(
            canvasIds: [100],
            items: [
                100 => [
                    'budget' => [
                        [
                            'id' => 10,
                            'description' => 'Community Health Fairs',
                            'status' => 'active',
                            'parsedData' => ['projectId' => 7, 'budgeted' => 10000, 'spent' => 2000],
                        ],
                        [
                            'id' => 11,
                            'description' => '',
                            'status' => 'stub',
                            'parsedData' => ['projectId' => 8, 'budgeted' => 0, 'spent' => 0],
                        ],
                    ],
                ],
            ],
        );

        $summary = $svc->getForProjects([7, 8]);

        $this->assertCount(1, $summary->budget, 'Stub budget rows must not inflate the array count (would flip isEmpty)');
        $this->assertSame(10000.0, $summary->totalBudgeted);
        $this->assertSame(2000.0, $summary->totalSpent);
    }

    public function test_getForProjects_aggregates_across_multiple_canvases(): void
    {
        // A strategy with two programs, each with its own resource canvas.
        // The gateway must sum across both.
        $svc = $this->makeService(
            canvasIds: [100, 200],
            items: [
                100 => [
                    'people' => [
                        ['id' => 1, 'description' => 'A', 'status' => 'active',
                            'parsedData' => ['userId' => 1, 'capacity' => 40, 'allocations' => ['7' => 20]]],
                    ],
                ],
                200 => [
                    'people' => [
                        ['id' => 2, 'description' => 'B', 'status' => 'active',
                            'parsedData' => ['userId' => 2, 'capacity' => 30, 'allocations' => ['9' => 15]]],
                    ],
                ],
            ],
        );

        $summary = $svc->getForProjects([2, 15, 7, 9]);

        $this->assertCount(2, $summary->people);
        $this->assertSame(70.0, $summary->totalCapacity);
        $this->assertSame(35.0, $summary->totalAllocated);
    }

    public function test_seedPeopleFromChildProjects_is_idempotent_by_userId(): void
    {
        // Two child projects; user 5 assigned to both, user 7 to one.
        // First run: 2 people added (5 and 7). Second run: 0 added, 2 skipped
        // per encounter — the "skip already present" contract is what makes
        // the seeder safe to re-run on demand from the UI.
        $existingItems = [];
        $addCalls = [];
        $repo = $this->makeRepo(
            canvasId: 500,
            itemsByBoxProvider: function (int $canvasId, string $box) use (&$existingItems) {
                return $existingItems;
            },
            onAddItem: function (int $canvasId, array $values) use (&$existingItems, &$addCalls): int {
                $addCalls[] = $values;
                $existingItems[] = [
                    'id' => count($existingItems) + 1,
                    'description' => $values['description'],
                    'status' => $values['status'],
                    'parsedData' => $values['data'],
                ];

                return count($existingItems);
            },
        );

        $programs = $this->makePrograms(childProjects: [
            ['id' => 7, 'name' => 'Project A'],
            ['id' => 8, 'name' => 'Project B'],
        ]);

        $projects = $this->makeProjects(usersByProject: [
            7 => [['id' => 5, 'firstname' => 'Sarah', 'lastname' => 'Chen', 'jobTitle' => 'PM']],
            8 => [
                ['id' => 5, 'firstname' => 'Sarah', 'lastname' => 'Chen', 'jobTitle' => 'PM'],
                ['id' => 7, 'firstname' => 'Aisha', 'lastname' => 'Patel', 'jobTitle' => 'Dev'],
            ],
        ]);

        $svc = new ResourceStructureService($repo, $this->makeDb(), $projects, $programs);

        $first = $svc->seedPeopleFromChildProjects(500);
        $this->assertSame(2, $first['added'], 'First run adds the two distinct users');
        $this->assertSame(1, $first['skipped'], 'Sarah appearing on the second project is skipped');
        $this->assertSame(500, $first['canvasId']);

        $second = $svc->seedPeopleFromChildProjects(500);
        $this->assertSame(0, $second['added'], 'Second run must add nothing (idempotent by userId)');
        $this->assertSame(3, $second['skipped'], 'Every source-user encounter is a skip on re-run');

        // Also validates the add-payload shape: userId is captured, status is 'active' (not 'stub').
        $this->assertCount(2, $addCalls);
        $this->assertSame('active', $addCalls[0]['status']);
        $this->assertSame(5, $addCalls[0]['data']['userId']);
    }

    public function test_seedBudgetFromChildProjects_is_idempotent_by_projectId(): void
    {
        // Same skip-when-present contract, keyed on projectId. A child with
        // dollarBudget=0 is skipped entirely (no line to seed).
        $existingItems = [];
        $addCalls = [];
        $repo = $this->makeRepo(
            canvasId: 500,
            itemsByBoxProvider: function (int $canvasId, string $box) use (&$existingItems) {
                return $existingItems;
            },
            onAddItem: function (int $canvasId, array $values) use (&$existingItems, &$addCalls): int {
                $addCalls[] = $values;
                $existingItems[] = [
                    'id' => count($existingItems) + 1,
                    'description' => $values['description'],
                    'status' => $values['status'],
                    'parsedData' => $values['data'],
                ];

                return count($existingItems);
            },
        );

        $programs = $this->makePrograms(childProjects: [
            ['id' => 7, 'name' => 'Health Fairs', 'dollarBudget' => 45000, 'color' => '#3E937A'],
            ['id' => 8, 'name' => 'Zero-budget project', 'dollarBudget' => 0, 'color' => '#000'],
            ['id' => 9, 'name' => 'Walk-in Days', 'dollarBudget' => 12000, 'color' => '#C09035'],
        ]);

        $svc = new ResourceStructureService($repo, $this->makeDb(), $this->makeProjects(), $programs);

        $first = $svc->seedBudgetFromChildProjects(500);
        $this->assertSame(2, $first['added'], 'The zero-budget project is skipped (no line to seed)');
        $this->assertSame(0, $first['skipped']);

        $second = $svc->seedBudgetFromChildProjects(500);
        $this->assertSame(0, $second['added'], 'Re-run must add nothing');
        $this->assertSame(2, $second['skipped'], 'Both real-budget projects skip on re-run');
    }

    // ─── Test doubles ────────────────────────────────────────────────

    /**
     * Testable subclass of the SUT that overrides `findResourceCanvasIds()`
     * with a canned list, so the test doesn't need to mock a Db query chain.
     * `getItemsByBox()` returns items keyed by `[canvasId][box]`.
     *
     * @param  int[]  $canvasIds
     * @param  array<int, array<string, array<int, array<string, mixed>>>>  $items
     */
    private function makeService(array $canvasIds, array $items): ResourceStructureService
    {
        $repo = $this->makeRepo(
            canvasId: 0,
            itemsByBoxProvider: fn (int $canvasId, string $box) => $items[$canvasId][$box] ?? [],
        );

        return new class ($repo, $this->makeDb(), $this->makeProjects(), $this->makePrograms(), $canvasIds) extends ResourceStructureService
        {
            /** @param int[] $canvasIds */
            public function __construct(
                ResourceStructureRepository $repo,
                Db $dbCore,
                ProjectService $projectService,
                Programs $programService,
                private array $canvasIds,
            ) {
                parent::__construct($repo, $dbCore, $projectService, $programService);
            }

            protected function findResourceCanvasIds(array $projectIds): array
            {
                return $this->canvasIds;
            }
        };
    }

    private function makeRepo(
        int $canvasId = 0,
        ?\Closure $itemsByBoxProvider = null,
        ?\Closure $onAddItem = null,
    ): ResourceStructureRepository {
        $repo = $this->createMock(ResourceStructureRepository::class);
        $repo->method('getOrCreateResourceCanvas')->willReturn($canvasId);
        $repo->method('getItemsByBox')->willReturnCallback(
            $itemsByBoxProvider ?? fn (int $canvasId, string $box) => []
        );
        if ($onAddItem !== null) {
            $repo->method('addItem')->willReturnCallback($onAddItem);
        }

        return $repo;
    }

    private function makeDb(): Db
    {
        // Not used by the SUT paths under test — `findResourceCanvasIds` is
        // overridden in makeService(), and seeders don't touch dbCore.
        return $this->createMock(Db::class);
    }

    /**
     * @param  array<int, array<int, array<string, mixed>>>  $usersByProject  projectId => user rows
     */
    private function makeProjects(array $usersByProject = []): ProjectService
    {
        $projects = $this->createMock(ProjectService::class);
        $projects->method('getUsersAssignedToProject')->willReturnCallback(
            fn (int $projectId) => $usersByProject[$projectId] ?? []
        );

        return $projects;
    }

    /**
     * @param  array<int, array<string, mixed>>  $childProjects
     */
    private function makePrograms(array $childProjects = []): Programs
    {
        $programs = $this->createMock(Programs::class);
        $programs->method('getColoredProgramProjects')->willReturn($childProjects);

        return $programs;
    }
}
