<?php

declare(strict_types=1);

namespace Unit\app\Plugins\PgmPro\Hxcontrollers\Concerns;

use Leantime\Domain\Projects\Services\Projects;
use Leantime\Plugins\PgmPro\Domain\Resources\Services\ResourceStructureService;
use Leantime\Plugins\PgmPro\Hxcontrollers\Concerns\ChecksResourceAccess;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Unit\TestCase;

/**
 * Behaviors under test — the ownership contract every mutating
 * ResourceItem/modal HxController endpoint depends on:
 *
 *   1. canEditProgram enforces program-type. A POST'd id that resolves
 *      to a regular project or a strategy is REFUSED — this is the
 *      Marcel-flagged IDOR guard, not a nicety.
 *   2. canEditProgram admin bypass. Admins skip the assignment check
 *      but still must target a program-type project.
 *   3. canEditProgram assignment path. Non-admins must be assigned to
 *      the specific program.
 *   4. canEditCanvas / canEditItem refuse ids that the repo resolvers
 *      reject (canvas isn't a resource canvas, item's canvas isn't a
 *      resource canvas). That refusal is what blocks cross-canvas-type
 *      IDOR — a POST'd Blueprints canvas id must not slip through.
 *   5. canAttachProject enforces project-type (not program/strategy)
 *      — programs and strategies have their own hierarchy and cannot
 *      be nested under another program.
 *   6. forbid() throws AccessDeniedHttpException so Laravel renders 403.
 *      Kept as the standard exit path so any endpoint that adds a new
 *      write in the future gets 403 automatically.
 */
class ChecksResourceAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session()->forget('userdata');
    }

    // ─── canEditProgram ───────────────────────────────────────────────

    public function test_canEditProgram_refuses_zero_id(): void
    {
        $host = $this->makeHost(project: null);
        $this->assertFalse($host->probeCanEditProgram(0));
    }

    public function test_canEditProgram_refuses_nonexistent_project(): void
    {
        // Marcel's exact concern: id from POST must resolve to a real
        // project. getProject returning bool false must NOT be treated
        // as "unknown, allow through" — that would open the guard.
        $host = $this->makeHost(project: false);
        session(['userdata' => ['id' => 5, 'role' => 'owner']]);
        $this->assertFalse($host->probeCanEditProgram(999));
    }

    public function test_canEditProgram_refuses_regular_project_type(): void
    {
        // The whole reason type-check is baked into the helper: an
        // attacker POSTing a regular project id (their own or someone
        // else's) must not be able to run program-scoped writes.
        $host = $this->makeHost(project: ['id' => 42, 'type' => 'project']);
        session(['userdata' => ['id' => 5, 'role' => 'owner']]);
        $this->assertFalse($host->probeCanEditProgram(42));
    }

    public function test_canEditProgram_refuses_strategy_type(): void
    {
        // Strategies aren't programs. Same guard.
        $host = $this->makeHost(project: ['id' => 42, 'type' => 'strategy']);
        session(['userdata' => ['id' => 5, 'role' => 'owner']]);
        $this->assertFalse($host->probeCanEditProgram(42));
    }

    public function test_canEditProgram_refuses_when_no_session_user(): void
    {
        $host = $this->makeHost(project: ['id' => 42, 'type' => 'program']);
        // session userdata deliberately not set
        $this->assertFalse($host->probeCanEditProgram(42));
    }

    public function test_canEditProgram_owner_bypasses_assignment_check(): void
    {
        // Owner (role 50) is above admin (40) — same bypass path.
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'program'],
            assigned: false,   // NOT assigned, and admin bypass should still let this pass
        );
        session(['userdata' => ['id' => 5, 'role' => 'owner']]);
        $this->assertTrue($host->probeCanEditProgram(42));
    }

    public function test_canEditProgram_admin_bypasses_assignment_check(): void
    {
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'program'],
            assigned: false,
        );
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $this->assertTrue($host->probeCanEditProgram(42));
    }

    public function test_canEditProgram_assigned_non_admin_is_allowed(): void
    {
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'program'],
            assigned: true,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $this->assertTrue($host->probeCanEditProgram(42));
    }

    public function test_canEditProgram_unassigned_non_admin_is_refused(): void
    {
        // The core IDOR case: a non-admin user who isn't on the program
        // POSTs to a write endpoint — must be refused.
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'program'],
            assigned: false,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $this->assertFalse($host->probeCanEditProgram(42));
    }

    // ─── canEditCanvas / canEditItem ──────────────────────────────────

    public function test_canEditCanvas_refuses_when_repo_returns_null(): void
    {
        // Repo resolver returns null when the canvas doesn't exist OR
        // isn't a resource-type canvas. Both cases must refuse the write
        // — the second is what blocks cross-canvas-type IDOR.
        $host = $this->makeHost(
            project: null,
            resolveCanvas: null,
        );
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $this->assertFalse($host->probeCanEditCanvas(999));
    }

    public function test_canEditCanvas_delegates_to_canEditProgram(): void
    {
        // When the repo maps canvas → program 42, canEditCanvas must
        // hand off to canEditProgram(42) — inheriting its type + access
        // guards. Set up canEditProgram to refuse via wrong type.
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'project'],  // wrong type
            resolveCanvas: 42,
        );
        session(['userdata' => ['id' => 5, 'role' => 'owner']]);
        $this->assertFalse($host->probeCanEditCanvas(100));
    }

    public function test_canEditCanvas_allows_when_program_check_passes(): void
    {
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'program'],
            resolveCanvas: 42,
            assigned: true,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $this->assertTrue($host->probeCanEditCanvas(100));
    }

    public function test_canEditItem_refuses_when_repo_returns_null(): void
    {
        $host = $this->makeHost(
            project: null,
            resolveItem: null,
        );
        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $this->assertFalse($host->probeCanEditItem(999));
    }

    public function test_canEditItem_delegates_to_canEditProgram(): void
    {
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'program'],
            resolveItem: 42,
            assigned: true,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $this->assertTrue($host->probeCanEditItem(500));
    }

    // ─── canAttachProject ─────────────────────────────────────────────

    public function test_canAttachProject_refuses_zero_id(): void
    {
        $host = $this->makeHost(project: null);
        $this->assertFalse($host->probeCanAttachProject(0));
    }

    public function test_canAttachProject_refuses_program_type(): void
    {
        // Programs cannot be children of other programs — that's a
        // hierarchy violation, and cyclic parent protection lives in
        // the Projects service, but the type check here is the first
        // gate.
        $host = $this->makeHost(project: ['id' => 42, 'type' => 'program']);
        session(['userdata' => ['id' => 5, 'role' => 'owner']]);
        $this->assertFalse($host->probeCanAttachProject(42));
    }

    public function test_canAttachProject_refuses_strategy_type(): void
    {
        $host = $this->makeHost(project: ['id' => 42, 'type' => 'strategy']);
        session(['userdata' => ['id' => 5, 'role' => 'owner']]);
        $this->assertFalse($host->probeCanAttachProject(42));
    }

    public function test_canAttachProject_allows_assigned_project(): void
    {
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'project'],
            assigned: true,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $this->assertTrue($host->probeCanAttachProject(42));
    }

    public function test_canAttachProject_owner_bypasses_assignment(): void
    {
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'project'],
            assigned: false,
        );
        session(['userdata' => ['id' => 5, 'role' => 'owner']]);
        $this->assertTrue($host->probeCanAttachProject(42));
    }

    public function test_canAttachProject_unassigned_non_admin_is_refused(): void
    {
        $host = $this->makeHost(
            project: ['id' => 42, 'type' => 'project'],
            assigned: false,
        );
        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $this->assertFalse($host->probeCanAttachProject(42));
    }

    // ─── forbid ───────────────────────────────────────────────────────

    public function test_forbid_throws_accessDenied(): void
    {
        // Kept as the standard rejection path so every future write on
        // this trait gets 403 without each caller reinventing it.
        $host = $this->makeHost(project: null);
        $this->expectException(AccessDeniedHttpException::class);
        $host->probeForbid();
    }

    /**
     * Build a minimal host class that uses the trait with the two
     * services stubbed to the passed behavior. Exposes wrapper methods
     * so tests can invoke the trait's protected helpers.
     */
    private function makeHost(
        array|null|false $project,
        ?int $resolveCanvas = null,
        ?int $resolveItem = null,
        bool $assigned = false,
    ): object {
        $projectService = new class ($project, $assigned) extends Projects
        {
            public function __construct(
                private array|null|false $projectResult,
                private bool $assignedResult,
            ) {
                // Skip parent constructor — no repo/DB needed.
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

        $resourceService = new class ($resolveCanvas, $resolveItem) extends ResourceStructureService
        {
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
        };

        return new class ($resourceService, $projectService)
        {
            use ChecksResourceAccess;

            public function __construct(
                protected ResourceStructureService $resourceService,
                protected Projects $projectService,
            ) {}

            public function probeCanEditProgram(int $id): bool
            {
                return $this->canEditProgram($id);
            }

            public function probeCanEditCanvas(int $id): bool
            {
                return $this->canEditCanvas($id);
            }

            public function probeCanEditItem(int $id): bool
            {
                return $this->canEditItem($id);
            }

            public function probeCanAttachProject(int $id): bool
            {
                return $this->canAttachProject($id);
            }

            public function probeForbid(): void
            {
                $this->forbid();
            }
        };
    }
}
