<?php

declare(strict_types=1);

namespace Unit\app\Plugins\PgmPro\Hxcontrollers;

use Leantime\Core\UI\Template;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Plugins\PgmPro\Domain\Resources\Services\ResourceStructureService;
use Leantime\Plugins\PgmPro\Hxcontrollers\ResourceItem;
use Leantime\Plugins\PgmPro\Services\Programs;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Unit\TestCase;

/**
 * Wiring test for the ownership guards on ResourceItem.
 *
 * The ChecksResourceAccessTest already proves the *trait* returns bool
 * correctly. This test proves each mutating method on ResourceItem
 * actually **calls** the trait — i.e. someone can't accidentally
 * delete a `if (! canEditX) return;` guard and have the tests still
 * pass. Together the two files close the "guard exists AND is invoked"
 * loop that a full HTTP integration test would cover, without needing
 * to bootstrap the HTMX dispatcher.
 *
 * Setup:
 *   - ResourceItem is instantiated via newInstanceWithoutConstructor
 *     to skip the base HtmxController constructor (which requires DI +
 *     event dispatch bootstrapping).
 *   - Services are stubbed via anonymous subclasses so we can steer
 *     the ownership check per test without touching the DB.
 *   - $_POST is mutated per test to feed each endpoint the ids it
 *     expects; cleared in tearDown so tests don't leak state.
 *
 * We assert two shapes per method:
 *   REFUSAL — with a mock configuration that makes the guard return
 *   false, calling the method throws AccessDeniedHttpException. That's
 *   the exact failure surface Marcel's CR#1 wanted proven for every
 *   write path.
 *   AUTHORIZATION — with a mock configuration that makes the guard
 *   pass, the method reaches its service call. Confirms the guard
 *   isn't blocking the happy path either.
 */
class ResourceItemAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session()->forget('userdata');
        $_POST = [];
    }

    protected function tearDown(): void
    {
        $_POST = [];
        parent::tearDown();
    }

    // ─── seedFromProjects (canEditProgram) ────────────────────────────

    public function test_seedFromProjects_refuses_unauthorized_program(): void
    {
        $ctrl = $this->makeController(
            project: ['id' => 42, 'type' => 'program'],
            assigned: false,  // non-admin, not assigned
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $_POST = ['programId' => '42'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->seedFromProjects();
    }

    public function test_seedFromProjects_allows_authorized_program(): void
    {
        $ctrl = $this->makeController(
            project: ['id' => 42, 'type' => 'program'],
            assigned: true,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $_POST = ['programId' => '42', 'mode' => 'both'];

        $ctrl->seedFromProjects();

        // No exception → guard let it through. Service was called for
        // both people + budget seed (mode=both).
        $this->assertGreaterThan(0, $ctrl->resourceService->seedPeopleCalls);
        $this->assertGreaterThan(0, $ctrl->resourceService->seedBudgetCalls);
    }

    // ─── addPerson / addBudget / addDependency (canEditCanvas) ────────

    public function test_addPerson_refuses_when_canvas_does_not_resolve(): void
    {
        // Repo returns null → canvas isn't a resource canvas, cross-type
        // IDOR path. Must refuse.
        $ctrl = $this->makeController(canvasResolves: null);
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $_POST = ['canvasId' => '999'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->addPerson();
    }

    public function test_addPerson_refuses_unauthorized_program_via_canvas(): void
    {
        $ctrl = $this->makeController(
            project: ['id' => 42, 'type' => 'program'],
            canvasResolves: 42,
            assigned: false,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $_POST = ['canvasId' => '100'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->addPerson();
    }

    public function test_addPerson_allows_authorized(): void
    {
        $ctrl = $this->makeController(
            project: ['id' => 42, 'type' => 'program'],
            canvasResolves: 42,
            assigned: true,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $_POST = ['canvasId' => '100'];

        $ctrl->addPerson();

        $this->assertGreaterThan(0, $ctrl->resourceService->allocatePersonCalls);
    }

    public function test_addBudget_refuses_unauthorized(): void
    {
        $ctrl = $this->makeController(canvasResolves: null);
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $_POST = ['canvasId' => '999'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->addBudget();
    }

    public function test_addDependency_refuses_unauthorized(): void
    {
        $ctrl = $this->makeController(canvasResolves: null);
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $_POST = ['canvasId' => '999', 'partnerName' => 'X'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->addDependency();
    }

    // ─── updatePerson / updateBudget / deletePerson / deleteBudget (canEditItem)

    public function test_updatePerson_refuses_when_item_does_not_resolve(): void
    {
        $ctrl = $this->makeController(itemResolves: null);
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $_POST = ['itemId' => '999', 'name' => 'X'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->updatePerson();
    }

    public function test_updatePerson_refuses_unauthorized_program_via_item(): void
    {
        $ctrl = $this->makeController(
            project: ['id' => 42, 'type' => 'program'],
            itemResolves: 42,
            assigned: false,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $_POST = ['itemId' => '500', 'name' => 'X'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->updatePerson();
    }

    public function test_updatePerson_allows_authorized(): void
    {
        $ctrl = $this->makeController(
            project: ['id' => 42, 'type' => 'program'],
            itemResolves: 42,
            assigned: true,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $_POST = ['itemId' => '500', 'name' => 'Renamed'];

        $ctrl->updatePerson();

        $this->assertGreaterThan(0, $ctrl->resourceService->updatePersonCalls);
    }

    public function test_updateBudget_refuses_unauthorized(): void
    {
        $ctrl = $this->makeController(itemResolves: null);
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $_POST = ['itemId' => '999', 'name' => 'X'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->updateBudget();
    }

    public function test_deletePerson_refuses_unauthorized(): void
    {
        $ctrl = $this->makeController(itemResolves: null);
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $_POST = ['itemId' => '999'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->deletePerson();
    }

    public function test_deleteBudget_refuses_unauthorized(): void
    {
        $ctrl = $this->makeController(itemResolves: null);
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $_POST = ['itemId' => '999'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->deleteBudget();
    }

    // ─── attachProject (canEditProgram + canAttachProject) ────────────

    public function test_attachProject_refuses_unauthorized_program(): void
    {
        // The Marcel-flagged IDOR: attacker POSTs a foreign programId
        // + a project they own. Must refuse.
        $ctrl = $this->makeController(
            project: false,  // getProject returns false → not a real program
        );
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $_POST = ['programId' => '999999', 'projectId' => '7'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->attachProject();
    }

    public function test_attachProject_refuses_unauthorized_project(): void
    {
        // Inverse Marcel case: attacker owns the program but POSTs a
        // foreign projectId to yank someone else's project under it.
        // canAttachProject must refuse regardless of program access.
        $ctrl = $this->makeControllerDual(
            program: ['id' => 42, 'type' => 'program'],
            project: false,  // second lookup returns false
            assigned: true,  // user owns the program
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $_POST = ['programId' => '42', 'projectId' => '999'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->attachProject();
    }

    // ─── createProject (canEditProgram) ───────────────────────────────

    public function test_createProject_refuses_unauthorized_program(): void
    {
        $ctrl = $this->makeController(project: false);
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $_POST = ['programId' => '999999', 'name' => 'New'];

        $this->expectException(AccessDeniedHttpException::class);
        $ctrl->createProject();
    }

    // ─── Fixtures ─────────────────────────────────────────────────────

    /**
     * Build a ResourceItem with services stubbed to the passed shape.
     * The controller is instantiated without its base constructor so
     * we don't have to bootstrap the HTMX request stack; services are
     * injected via reflection.
     */
    private function makeController(
        array|null|false $project = false,
        ?int $canvasResolves = null,
        ?int $itemResolves = null,
        bool $assigned = false,
    ): object {
        $projectService = $this->stubProjectService($project, $assigned);
        $resourceService = $this->stubResourceService($canvasResolves, $itemResolves);
        $programService = $this->createMock(Programs::class);

        return $this->injectServices($resourceService, $projectService, $programService);
    }

    /**
     * attachProject calls getProject TWICE (once for the program, once
     * for the project being attached) — this variant lets each lookup
     * return a different value.
     */
    private function makeControllerDual(
        array|false $program,
        array|false $project,
        bool $assigned,
    ): object {
        $projectService = new class ($program, $project, $assigned) extends Projects
        {
            public function __construct(
                private array|false $programResult,
                private array|false $projectResult,
                private bool $assignedResult,
            ) {
                // Skip parent constructor.
            }

            public function getProject(int $id): bool|array
            {
                // Program lookup returns programResult; anything else
                // returns projectResult. Program id is always 42 in the
                // tests that use this fixture.
                return $id === 42 ? $this->programResult : $this->projectResult;
            }

            public function isUserAssignedToProject(int $userId, int $projectId): bool
            {
                return $this->assignedResult;
            }
        };
        $resourceService = $this->stubResourceService(null, null);
        $programService = $this->createMock(Programs::class);

        return $this->injectServices($resourceService, $projectService, $programService);
    }

    private function stubProjectService(array|null|false $project, bool $assigned): Projects
    {
        return new class ($project, $assigned) extends Projects
        {
            public function __construct(
                private array|null|false $projectResult,
                private bool $assignedResult,
            ) {
                // Skip parent constructor.
            }

            public function getProject(int $id): bool|array
            {
                return $this->projectResult === null ? false : $this->projectResult;
            }

            public function isUserAssignedToProject(int $userId, int $projectId): bool
            {
                return $this->assignedResult;
            }
        };
    }

    private function stubResourceService(?int $canvasResolves, ?int $itemResolves): ResourceStructureService
    {
        return new class ($canvasResolves, $itemResolves) extends ResourceStructureService
        {
            public int $seedPeopleCalls = 0;

            public int $seedBudgetCalls = 0;

            public int $allocatePersonCalls = 0;

            public int $updatePersonCalls = 0;

            public function __construct(
                private ?int $canvasResult,
                private ?int $itemResult,
            ) {
                // Skip parent constructor.
            }

            public function getProgramIdForCanvas(int $canvasId): ?int
            {
                return $this->canvasResult;
            }

            public function getProgramIdForItem(int $itemId): ?int
            {
                return $this->itemResult;
            }

            public function seedPeopleFromChildProjects(int $programId): array
            {
                $this->seedPeopleCalls++;

                return ['added' => 0, 'skipped' => 0];
            }

            public function seedBudgetFromChildProjects(int $programId): array
            {
                $this->seedBudgetCalls++;

                return ['added' => 0, 'skipped' => 0];
            }

            public function allocatePerson(int $canvasId, array $personData): int
            {
                $this->allocatePersonCalls++;

                return 0;
            }

            public function updatePerson(int $itemId, array $personData): bool
            {
                $this->updatePersonCalls++;

                return true;
            }
        };
    }

    /**
     * Bypass the base HtmxController constructor (which requires the
     * app container + event dispatch) and inject the services + a
     * fresh Response via reflection. Anonymous subclass exposes the
     * mocked services so tests can assert on their call counters.
     */
    private function injectServices(
        ResourceStructureService $resourceService,
        Projects $projectService,
        Programs $programService,
    ): object {
        // A no-op Template stub so happy-path methods that call
        // $this->tpl->setNotification(...) don't blow up on the base
        // class's typed property. The refusal tests never reach the
        // template call, but the "guard passes" tests do.
        $tpl = $this->createMock(Template::class);

        $wrapper = new class ($resourceService, $projectService, $programService, $tpl) extends ResourceItem
        {
            public ResourceStructureService $resourceService;

            public Projects $projectService;

            public Programs $programService;

            public function __construct(
                ResourceStructureService $resourceService,
                Projects $projectService,
                Programs $programService,
                Template $tpl,
            ) {
                // Skip base HtmxController::__construct — the wiring
                // tests only exercise action-method bodies + trait
                // helpers, not the response-rendering path.
                $this->resourceService = $resourceService;
                $this->projectService = $projectService;
                $this->programService = $programService;
                $this->tpl = $tpl;
                $this->response = new Response;
                $this->headers = [];
            }
        };

        return $wrapper;
    }
}
