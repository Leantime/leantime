<?php

namespace Unit\app\Domain\Install;

use Illuminate\Database\Connection;
use Leantime\Core\Auth\Permissions\PermissionRegistry;
use Leantime\Core\Auth\Permissions\PermissionSeeder;
use Leantime\Domain\Install\Repositories\Install;
use Unit\TestCase;

/**
 * update_sql_30533 adds `projects.viewAll` and `roles.manage`: it rediscovers the permission
 * providers (one is new), syncs the vocabulary and grants the built-in roles their defaults.
 */
class UpdateSql30533Test extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, string> registry and seeder calls, in order */
    private array $calls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(PermissionRegistry::class, $this->make(PermissionRegistry::class, [
            'flush' => function (): void {
                $this->calls[] = 'flush';
            },
        ]));
        $this->app->instance(PermissionSeeder::class, $this->make(PermissionSeeder::class, [
            'syncDiscoveredPermissions' => function (): array {
                $this->calls[] = 'syncDiscoveredPermissions';

                return [];
            },
            'seedBuiltInRoles' => function (): void {
                $this->calls[] = 'seedBuiltInRoles';
            },
        ]));
    }

    private function installer(Connection $connection): Install
    {
        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Install::class, 'connection'))->setValue($install, $connection);

        return $install;
    }

    public function test_providers_are_rediscovered_before_the_sync_and_seed(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $connection->statement('CREATE TABLE zp_roles (id INTEGER PRIMARY KEY, name VARCHAR(50), level INTEGER)');
        $connection->statement('CREATE TABLE zp_permissions (id INTEGER PRIMARY KEY, permissionKey VARCHAR(150))');

        $this->assertTrue($this->installer($connection)->update_sql_30533());

        $this->assertSame(['flush', 'syncDiscoveredPermissions', 'seedBuiltInRoles'], $this->calls);
    }

    public function test_skipped_without_permission_tables(): void
    {
        $this->assertTrue($this->installer(new SqliteStandInForInstalledDatabase)->update_sql_30533());

        $this->assertSame([], $this->calls);
    }
}
