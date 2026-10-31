<?php

namespace Tests\Unit\app\Core\Auth\Permissions;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\RoleResolver;

/**
 * Lists check permissions per row (kanban cards, milestone rows). The project role and the
 * membership behind a project-scoped check are resolved once per request, not once per row.
 */
class PermissionServiceProjectMemoTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private int $roleLookups = 0;

    private int $membershipLookups = 0;

    private bool $isMember = true;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.stores.installation' => ['driver' => 'array'],
            'cache.stores.instance' => ['driver' => 'array'],
            'cache.default' => 'instance',
        ]);
        Cache::forgetDriver(['installation', 'instance']);
        session(['userdata' => ['id' => 9, 'role' => 'editor']]);
    }

    private function service(): PermissionService
    {
        $repo = $this->make(PermissionRepository::class, [
            'getRolePermissionMap' => fn (): array => ['editor' => ['tickets.edit', 'tickets.delete']],
            'getAllPermissions' => fn (): array => [
                ['permissionKey' => 'tickets.edit', 'isProjectScoped' => 1],
                ['permissionKey' => 'tickets.delete', 'isProjectScoped' => 1],
                ['permissionKey' => 'projects.viewAll', 'isProjectScoped' => 0],
            ],
        ]);

        $roles = $this->makeEmpty(RoleResolver::class, [
            'effectiveRoleForProject' => function (): string {
                $this->roleLookups++;

                return 'editor';
            },
            'effectiveRole' => 'editor',
            'globalRole' => 'editor',
        ]);

        $projectAccess = $this->makeEmpty(ChecksProjectAccess::class, [
            'isUserAssignedToProject' => function (): bool {
                $this->membershipLookups++;

                return $this->isMember;
            },
        ]);

        return new PermissionService($repo, $roles, $projectAccess, $this->allModulesOn());
    }

    public function test_repeated_checks_in_one_project_resolve_role_and_membership_once(): void
    {
        $service = $this->service();

        foreach (range(1, 5) as $row) {
            $this->assertTrue($service->currentUserCan('tickets.edit', 7));
            $this->assertTrue($service->currentUserCan('tickets.delete', 7));
        }

        $this->assertSame(1, $this->roleLookups);
        $this->assertSame(1, $this->membershipLookups);

        $service->currentUserCan('tickets.edit', 8);
        $this->assertSame(2, $this->roleLookups, 'another project resolves on its own');
    }

    public function test_a_missing_membership_is_asked_again(): void
    {
        $this->isMember = false;
        $service = $this->service();

        $this->assertFalse($service->currentUserCan('tickets.edit', 7));

        // Added to the project later in the same request: not refused from a remembered miss.
        $this->isMember = true;
        $this->assertTrue($service->currentUserCan('tickets.edit', 7));
        $this->assertSame(2, $this->membershipLookups);
    }

    public function test_flushing_the_cache_forgets_resolved_projects(): void
    {
        $service = $this->service();
        $service->currentUserCan('tickets.edit', 7);

        $service->flushCache();
        $service->currentUserCan('tickets.edit', 7);

        $this->assertSame(2, $this->roleLookups);
        $this->assertSame(2, $this->membershipLookups);
    }

    public function test_another_session_user_resolves_separately(): void
    {
        $service = $this->service();
        $service->currentUserCan('tickets.edit', 7);

        session(['userdata' => ['id' => 10, 'role' => 'editor']]);
        $service->currentUserCan('tickets.edit', 7);

        $this->assertSame(2, $this->roleLookups);
        $this->assertSame(2, $this->membershipLookups);
    }
}
