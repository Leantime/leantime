<?php

namespace Tests\Unit\app\Core\Auth\Permissions;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\RoleResolver;

/**
 * Seeing every project without being a member is the `projects.viewAll` permission, not a rank:
 * a custom role can hold it, and a role without it needs membership like everyone else.
 */
class PermissionServiceViewAllTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private int $membershipChecks = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.stores.installation' => ['driver' => 'array'],
            'cache.stores.instance' => ['driver' => 'array'],
            'cache.default' => 'instance',
        ]);
        Cache::forgetDriver(['installation', 'instance']);
        session(['userdata' => ['id' => 9]]);
    }

    /**
     * @param  array<string, array<int, string>>  $grantMap
     */
    private function serviceFor(string $roleName, array $grantMap): PermissionService
    {
        $repo = $this->make(PermissionRepository::class, [
            'getRolePermissionMap' => fn (): array => $grantMap,
            'getAllPermissions' => fn (): array => [
                ['permissionKey' => 'tickets.view', 'isProjectScoped' => 1],
                ['permissionKey' => 'projects.viewAll', 'isProjectScoped' => 0],
            ],
        ]);

        $roles = $this->makeEmpty(RoleResolver::class, [
            'effectiveRoleForProject' => $roleName,
            'effectiveRole' => $roleName,
            'globalRole' => $roleName,
        ]);

        $projectAccess = $this->makeEmpty(ChecksProjectAccess::class, [
            'isUserAssignedToProject' => function (): bool {
                $this->membershipChecks++;

                return false;
            },
        ]);

        return new PermissionService($repo, $roles, $projectAccess, $this->allModulesOn());
    }

    public function test_a_custom_role_with_view_all_reads_projects_it_is_not_a_member_of(): void
    {
        $service = $this->serviceFor('auditor', ['auditor' => ['tickets.view', 'projects.viewAll']]);

        $this->assertTrue($service->currentUserCan('tickets.view', 42));
        $this->assertSame(0, $this->membershipChecks);
        $this->assertTrue($service->roleSeesAllProjects('auditor'));
    }

    public function test_an_admin_level_role_without_view_all_still_needs_membership(): void
    {
        $service = $this->serviceFor('deputy_admin', ['deputy_admin' => ['tickets.view'], 'owner' => ['projects.viewAll']]);

        $this->assertFalse($service->currentUserCan('tickets.view', 42));
        $this->assertSame(1, $this->membershipChecks);
        $this->assertFalse($service->roleSeesAllProjects('deputy_admin'));
    }

    public function test_unseeded_grants_fall_back_to_admin_rank_for_project_lists(): void
    {
        $service = $this->serviceFor('admin', []);

        $this->assertTrue($service->roleSeesAllProjects('admin'));
        $this->assertFalse($service->roleSeesAllProjects('editor'));
    }
}
