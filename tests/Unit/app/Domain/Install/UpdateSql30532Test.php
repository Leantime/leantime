<?php

namespace Unit\app\Domain\Install;

use Illuminate\Database\Connection;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Domain\Install\Repositories\Install;
use Unit\TestCase;

/**
 * update_sql_30532 gives zp_roles a stored key separate from the rank (`roleKey`) and a
 * `baseRole`, so custom roles can exist. Built-in roles keep the keys users already store (5..50).
 * Custom roles created afterwards get keys from 100 up.
 */
class UpdateSql30532Test extends TestCase
{
    private function installer(Connection $connection): Install
    {
        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Install::class, 'connection'))->setValue($install, $connection);

        return $install;
    }

    private function repository(Connection $connection): PermissionRepository
    {
        $repository = (new \ReflectionClass(PermissionRepository::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(PermissionRepository::class, 'db'))->setValue($repository, $connection);

        return $repository;
    }

    /** zp_roles as update_sql_30518 created it. */
    private function createRolesTableFrom30518(Connection $connection): void
    {
        $connection->statement('CREATE TABLE zp_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(50), displayName VARCHAR(100), level INTEGER, isSystem INTEGER DEFAULT 0, description TEXT, createdOn DATETIME, modified DATETIME)');

        foreach (['readonly' => 5, 'commenter' => 10, 'editor' => 20, 'manager' => 30, 'admin' => 40, 'owner' => 50] as $name => $level) {
            $connection->table('zp_roles')->insert(['name' => $name, 'displayName' => ucfirst($name), 'level' => $level, 'isSystem' => 1]);
        }
    }

    public function test_built_in_roles_keep_their_stored_keys(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createRolesTableFrom30518($connection);

        $this->assertTrue($this->installer($connection)->update_sql_30532());

        $roleKeysByName = $connection->table('zp_roles')->pluck('roleKey', 'name')->map(fn ($key) => (int) $key)->all();
        $this->assertSame(['readonly' => 5, 'commenter' => 10, 'editor' => 20, 'manager' => 30, 'admin' => 40, 'owner' => 50], $roleKeysByName);
        $this->assertTrue($connection->getSchemaBuilder()->hasColumn('zp_roles', 'baseRole'));
        $this->assertTrue($connection->getSchemaBuilder()->hasIndex('zp_roles', ['roleKey'], 'unique'));
    }

    public function test_running_twice_changes_nothing(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createRolesTableFrom30518($connection);

        $this->assertTrue($this->installer($connection)->update_sql_30532());
        $this->assertTrue($this->installer($connection)->update_sql_30532());

        $this->assertSame(6, $connection->table('zp_roles')->whereNotNull('roleKey')->count());
    }

    public function test_skipped_without_a_roles_table(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;

        $this->assertTrue($this->installer($connection)->update_sql_30532());
    }

    private function migratedDatabase(): SqliteStandInForInstalledDatabase
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createRolesTableFrom30518($connection);
        $connection->statement('CREATE TABLE zp_user (id INTEGER PRIMARY KEY AUTOINCREMENT, role VARCHAR(200))');
        $connection->statement('CREATE TABLE zp_relationuserproject (id INTEGER PRIMARY KEY AUTOINCREMENT, userId INTEGER, projectId INTEGER, projectRole VARCHAR(20))');
        $connection->statement('CREATE TABLE zp_role_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, roleId INTEGER, permissionId INTEGER)');
        $this->installer($connection)->update_sql_30532();

        return $connection;
    }

    public function test_custom_roles_get_keys_from_their_id(): void
    {
        $connection = $this->migratedDatabase();
        $repository = $this->repository($connection);

        $qaLeadId = $repository->createRole(['name' => 'qa_lead', 'displayName' => 'QA Lead', 'level' => 20, 'baseRole' => 'editor']);
        $teamLeadId = $repository->createRole(['name' => 'team_lead', 'displayName' => 'Team Lead', 'level' => 30, 'baseRole' => 'manager']);
        // Saving an existing role again keeps its key.
        $repository->createRole(['name' => 'qa_lead', 'displayName' => 'QA Lead (renamed)', 'level' => 20, 'baseRole' => 'editor']);

        $qaLead = $repository->getRoleByName('qa_lead');
        $this->assertSame(100 + $qaLeadId, (int) $qaLead['roleKey']);
        $this->assertGreaterThanOrEqual(100, (int) $qaLead['roleKey']);
        $this->assertSame('editor', $qaLead['baseRole']);
        $this->assertSame('QA Lead (renamed)', $qaLead['displayName']);
        $this->assertSame(100 + $teamLeadId, (int) $repository->getRoleByName('team_lead')['roleKey']);
    }

    public function test_a_deleted_role_key_is_never_given_to_a_new_role(): void
    {
        $connection = $this->migratedDatabase();
        $repository = $this->repository($connection);

        $tempId = $repository->createRole(['name' => 'temp', 'level' => 20, 'baseRole' => 'editor']);
        $tempKey = (int) $repository->getRoleByName('temp')['roleKey'];
        $this->assertTrue($repository->deleteRole($tempId));

        $repository->createRole(['name' => 'deputy_admin', 'level' => 40, 'baseRole' => 'admin']);

        $this->assertNotSame($tempKey, (int) $repository->getRoleByName('deputy_admin')['roleKey']);
    }

    public function test_a_role_still_held_by_users_or_projects_cannot_be_deleted(): void
    {
        $connection = $this->migratedDatabase();
        $repository = $this->repository($connection);

        $userRoleId = $repository->createRole(['name' => 'held_by_user', 'level' => 20, 'baseRole' => 'editor']);
        $projectRoleId = $repository->createRole(['name' => 'held_by_project', 'level' => 20, 'baseRole' => 'editor']);
        $connection->table('zp_user')->insert(['role' => (string) $repository->getRoleById($userRoleId)['roleKey']]);
        $connection->table('zp_relationuserproject')->insert(['userId' => 1, 'projectId' => 3, 'projectRole' => (string) $repository->getRoleById($projectRoleId)['roleKey']]);

        $this->assertFalse($repository->deleteRole($userRoleId));
        $this->assertFalse($repository->deleteRole($projectRoleId));
        $this->assertNotNull($repository->getRoleById($userRoleId));

        $editorId = (int) $repository->getRoleByName('editor')['id'];
        $this->assertFalse($repository->deleteRole($editorId), 'built-in roles are never deleted');
    }

    public function test_seeding_before_the_migration_still_works(): void
    {
        // Upgrades from before 3.5.18 seed built-in roles (30518) before 30532 adds the columns.
        $connection = new SqliteStandInForInstalledDatabase;
        $connection->statement('CREATE TABLE zp_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(50), displayName VARCHAR(100), level INTEGER, isSystem INTEGER DEFAULT 0, description TEXT, createdOn DATETIME, modified DATETIME)');

        $this->repository($connection)->upsertRole('owner', 'Owner', 50, isSystem: true, roleKey: 50);

        $this->assertSame(50, (int) $connection->table('zp_roles')->where('name', 'owner')->value('level'));
    }
}
