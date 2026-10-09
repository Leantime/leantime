<?php

namespace Unit\app\Domain\Install;

use Illuminate\Database\Connection;
use Leantime\Core\Auth\Permissions\PermissionRegistry;
use Leantime\Core\Auth\Permissions\PermissionSeeder;
use Leantime\Domain\Install\Repositories\Install;
use Unit\TestCase;

/**
 * update_sql_30528 re-seeds the permission engine on installs whose role tables are empty.
 *
 * 30518 creates zp_roles / zp_permissions / zp_role_permissions and seeds the built-in roles, but
 * a cloud workspace was found at 3.5.27 with all three tables empty — every permission check then
 * denies, owners included. 30528 re-runs the seed there and leaves seeded installs alone, so
 * operator role edits survive.
 */
class UpdateSql30528Test extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, string> seeder calls, in order */
    private array $seederCalls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(PermissionRegistry::class, $this->make(PermissionRegistry::class, ['flush' => null]));
        $this->app->instance(PermissionSeeder::class, $this->make(PermissionSeeder::class, [
            'syncDiscoveredPermissions' => function (): array {
                $this->seederCalls[] = 'syncDiscoveredPermissions';

                return [];
            },
            'seedBuiltInRoles' => function (): void {
                $this->seederCalls[] = 'seedBuiltInRoles';
            },
        ]));
    }

    private function installer(Connection $connection): Install
    {
        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Install::class, 'connection'))->setValue($install, $connection);

        return $install;
    }

    private function createPermissionTables(Connection $connection): void
    {
        $connection->statement('CREATE TABLE zp_roles (id INTEGER PRIMARY KEY, name VARCHAR(50), level INTEGER)');
        $connection->statement('CREATE TABLE zp_permissions (id INTEGER PRIMARY KEY, permissionKey VARCHAR(150))');
        $connection->statement('CREATE TABLE zp_role_permissions (id INTEGER PRIMARY KEY, roleId INTEGER, permissionId INTEGER)');
    }

    public function test_empty_permission_tables_are_reseeded(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createPermissionTables($connection);

        $this->assertTrue($this->installer($connection)->update_sql_30528());

        $this->assertSame(['syncDiscoveredPermissions', 'seedBuiltInRoles'], $this->seederCalls);
    }

    public function test_roles_without_any_grants_are_reseeded(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createPermissionTables($connection);
        $connection->table('zp_roles')->insert(['name' => 'owner', 'level' => 50]);

        $this->assertTrue($this->installer($connection)->update_sql_30528());

        $this->assertSame(['syncDiscoveredPermissions', 'seedBuiltInRoles'], $this->seederCalls);
    }

    public function test_a_seeded_install_is_left_untouched(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createPermissionTables($connection);
        $connection->table('zp_roles')->insert(['name' => 'owner', 'level' => 50]);
        $connection->table('zp_permissions')->insert(['permissionKey' => 'users.view']);
        $connection->table('zp_role_permissions')->insert(['roleId' => 1, 'permissionId' => 1]);

        $this->assertTrue($this->installer($connection)->update_sql_30528());

        $this->assertSame([], $this->seederCalls, 'Operator role edits must survive: no reseed when roles and grants exist');
    }

    public function test_missing_permission_tables_are_skipped(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;

        $this->assertTrue($this->installer($connection)->update_sql_30528());

        $this->assertSame([], $this->seederCalls);
    }
}
