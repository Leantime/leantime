<?php

namespace Tests\Unit\app\Core\Auth\Permissions;

use Illuminate\Database\Connection;
use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\PermissionRegistry;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Core\Auth\Permissions\PermissionSeeder;
use Leantime\Core\Auth\Permissions\PermissionService;
use Unit\app\Domain\Install\SqliteStandInForInstalledDatabase;

/**
 * When an update adds permissions, every role gets the new ones it gets by default (custom roles
 * through their base role), so new features work for everyone. Grants an administrator removed
 * are never added back, and a sync that fails can simply be run again.
 */
class PermissionSeederCustomRolesTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private Connection $connection;

    /** @var array<string, Permission> */
    private array $catalog = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new SqliteStandInForInstalledDatabase;
        $this->connection->statement('CREATE TABLE zp_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(50), displayName VARCHAR(100), roleKey INTEGER, level INTEGER, baseRole VARCHAR(50), isSystem INTEGER DEFAULT 0, description TEXT, createdOn DATETIME, modified DATETIME)');
        $this->connection->statement('CREATE TABLE zp_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, permissionKey VARCHAR(150), domain VARCHAR(60), action VARCHAR(100), label VARCHAR(191), isProjectScoped INTEGER DEFAULT 1, createdOn DATETIME, modified DATETIME)');
        $this->connection->statement('CREATE TABLE zp_role_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, roleId INTEGER, permissionId INTEGER)');

        $this->useCatalog([
            new Permission('tickets.view', 'View', true),
            new Permission('tickets.create', 'Create', true),
        ]);
    }

    /**
     * @param  array<int, Permission>  $permissions
     */
    private function useCatalog(array $permissions): void
    {
        $this->catalog = [];
        foreach ($permissions as $permission) {
            $this->catalog[$permission->key] = $permission;
        }
    }

    private function repository(): PermissionRepository
    {
        $repository = (new \ReflectionClass(PermissionRepository::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(PermissionRepository::class, 'db'))->setValue($repository, $this->connection);

        return $repository;
    }

    private function seeder(?PermissionRepository $repository = null): PermissionSeeder
    {
        return new PermissionSeeder(
            $repository ?? $this->repository(),
            $this->make(PermissionRegistry::class, ['all' => fn (): array => $this->catalog]),
            $this->make(PermissionService::class, ['flushCache' => null]),
        );
    }

    /** @return array<int, string> */
    private function grantsOf(string $roleName): array
    {
        return $this->connection->table('zp_role_permissions as rp')
            ->join('zp_roles as r', 'r.id', '=', 'rp.roleId')
            ->join('zp_permissions as p', 'p.id', '=', 'rp.permissionId')
            ->where('r.name', $roleName)
            ->orderBy('p.permissionKey')
            ->pluck('p.permissionKey')
            ->all();
    }

    /** An install with the built-in roles and a QA Lead (editor-based) that can't create to-dos. */
    private function installWithQaLead(): void
    {
        $seeder = $this->seeder();
        $seeder->syncDiscoveredPermissions();
        $seeder->seedBuiltInRoles();

        $repository = $this->repository();
        $qaLeadId = $repository->createRole(['name' => 'qa_lead', 'displayName' => 'QA Lead', 'level' => 20, 'baseRole' => 'editor']);
        $repository->grant($qaLeadId, 'tickets.view');
    }

    public function test_new_permissions_reach_custom_roles_whose_base_role_gets_them(): void
    {
        $this->installWithQaLead();

        // An update adds a project feature and an admin-only setting.
        $this->useCatalog([
            new Permission('tickets.view', 'View', true),
            new Permission('tickets.create', 'Create', true),
            new Permission('wiki.create', 'Create', true),
            new Permission('roles.manage', 'Manage roles', false),
        ]);
        $this->seeder()->syncDiscoveredPermissions();

        $this->assertSame(['tickets.view', 'wiki.create'], $this->grantsOf('qa_lead'));
    }

    public function test_a_removed_grant_stays_removed_after_a_reseed(): void
    {
        $this->installWithQaLead();

        $seeder = $this->seeder();
        $seeder->syncDiscoveredPermissions();
        $seeder->seedBuiltInRoles();

        $this->assertSame(['tickets.view'], $this->grantsOf('qa_lead'));
        $this->assertSame(['tickets.create', 'tickets.view'], $this->grantsOf('editor'));
    }

    public function test_a_custom_role_without_a_base_role_uses_the_built_in_role_at_its_level(): void
    {
        $this->installWithQaLead();
        $this->repository()->createRole(['name' => 'team_lead', 'displayName' => 'Team Lead', 'level' => 35]);

        $this->useCatalog([
            new Permission('tickets.view', 'View', true),
            new Permission('tickets.create', 'Create', true),
            new Permission('reports.export', 'Export', true),
        ]);
        $this->seeder()->syncDiscoveredPermissions();

        // Level 35 sits between manager (30) and admin (40): manager defaults apply ('*' on project permissions).
        $this->assertSame(['reports.export'], $this->grantsOf('team_lead'));
    }

    public function test_new_permissions_reach_built_in_roles_without_restoring_revoked_grants(): void
    {
        $this->installWithQaLead();

        // An administrator took tickets.create away from editors.
        $repository = $this->repository();
        $repository->revoke((int) $repository->getRoleByName('editor')['id'], 'tickets.create');

        $this->useCatalog([
            new Permission('tickets.view', 'View', true),
            new Permission('tickets.create', 'Create', true),
            new Permission('projects.viewAll', 'See all projects', false),
            new Permission('roles.manage', 'Manage roles', false),
        ]);
        $this->seeder()->syncDiscoveredPermissions();

        $this->assertSame(['tickets.view'], $this->grantsOf('editor'));
        $this->assertSame(['projects.viewAll', 'roles.manage', 'tickets.create', 'tickets.view'], $this->grantsOf('admin'));
        $this->assertSame(['projects.viewAll', 'roles.manage', 'tickets.create', 'tickets.view'], $this->grantsOf('owner'));
    }

    public function test_a_failed_sync_can_be_retried(): void
    {
        $this->installWithQaLead();

        $this->useCatalog([
            new Permission('tickets.view', 'View', true),
            new Permission('tickets.create', 'Create', true),
            new Permission('wiki.create', 'Create', true),
        ]);

        $failingRepository = new class extends PermissionRepository
        {
            public function __construct() {}

            public function grant(int $roleId, string $permissionKey): void
            {
                throw new \RuntimeException('connection lost');
            }
        };
        (new \ReflectionProperty(PermissionRepository::class, 'db'))->setValue($failingRepository, $this->connection);

        try {
            $this->seeder($failingRepository)->syncDiscoveredPermissions();
            $this->fail('the grant failure should surface');
        } catch (\RuntimeException $e) {
            $this->assertSame('connection lost', $e->getMessage());
        }

        $this->assertFalse($this->connection->table('zp_permissions')->where('permissionKey', 'wiki.create')->exists(), 'the sync rolled back');

        $this->seeder()->syncDiscoveredPermissions();

        $this->assertSame(['tickets.view', 'wiki.create'], $this->grantsOf('qa_lead'));
        $this->assertContains('wiki.create', $this->grantsOf('editor'));
    }
}
