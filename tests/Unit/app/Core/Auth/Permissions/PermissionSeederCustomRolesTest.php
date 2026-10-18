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
 * When an update adds permissions, custom roles get the ones their base role gets by default, so
 * new features work for them. Grants an administrator removed are never added back.
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

    private function seeder(): PermissionSeeder
    {
        return new PermissionSeeder(
            $this->repository(),
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
}
