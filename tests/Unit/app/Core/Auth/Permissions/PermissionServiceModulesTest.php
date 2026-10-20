<?php

namespace Tests\Unit\app\Core\Auth\Permissions;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\RoleResolver;
use Leantime\Core\Modules\Contracts\StoresProjectModules;
use Leantime\Core\Modules\Module;
use Leantime\Core\Modules\ModuleRegistry;
use Leantime\Core\Modules\ProjectModules;

/**
 * A module switched off in a project is a blanket switch: no role can use its project-scoped
 * permissions there. Everything else about the decision is unchanged.
 */
class PermissionServiceModulesTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.stores.installation' => ['driver' => 'array'],
            'cache.stores.instance' => ['driver' => 'array'],
            'cache.default' => 'instance',
        ]);
        Cache::forgetDriver(['installation', 'instance']);
    }

    /**
     * An admin who holds wiki.view everywhere and sees all projects, in an install where project 7
     * has the wiki off.
     */
    private function service(): PermissionService
    {
        $repo = $this->make(PermissionRepository::class, [
            'getRolePermissionMap' => fn (): array => ['admin' => ['wiki.view', 'timesheets.view', 'projects.viewAll']],
            'getAllPermissions' => fn (): array => [
                ['permissionKey' => 'wiki.view', 'isProjectScoped' => 1],
                ['permissionKey' => 'timesheets.view', 'isProjectScoped' => 0],
            ],
        ]);

        $roles = $this->makeEmpty(RoleResolver::class, [
            'effectiveRoleForProject' => 'admin',
            'effectiveRole' => 'admin',
            'globalRole' => 'admin',
        ]);

        $registry = new ModuleRegistry;
        $registry->register(new Module(id: 'wiki', title: 'Docs', routes: ['wiki'], permissionDomain: 'wiki'));
        $registry->register(new Module(id: 'timesheets', title: 'Timesheets', permissionDomain: 'timesheets'));

        $store = $this->makeEmpty(StoresProjectModules::class, [
            'getModuleOverrides' => fn (int $projectId): array => $projectId === 7 ? ['wiki' => false, 'timesheets' => false] : [],
        ]);

        return new PermissionService(
            $repo,
            $roles,
            $this->makeEmpty(ChecksProjectAccess::class),
            new ProjectModules($registry, $store),
        );
    }

    public function test_a_switched_off_module_denies_even_an_admin_in_that_project(): void
    {
        $this->assertFalse($this->service()->currentUserCan('wiki.view', 7));
    }

    public function test_other_projects_are_unaffected(): void
    {
        $this->assertTrue($this->service()->currentUserCan('wiki.view', 8));
    }

    public function test_global_checks_are_unaffected(): void
    {
        $service = $this->service();

        // Company-wide checks (global search) and global-scoped permissions aren't about one project.
        $this->assertTrue($service->currentUserCan('wiki.view', 7, true));
        $this->assertTrue($service->currentUserCan('timesheets.view', 7));
    }
}
