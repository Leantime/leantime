<?php

namespace Unit\app\Domain\Auth\Models;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Core\Auth\RoleCeiling;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;

/**
 * Roles come from zp_roles. A custom role has its own key (100+), a rank (level) and a base role:
 * every rank check uses the level, every check by role name treats it as its base role, and it
 * never passes as admin just because its key is a big number.
 */
class RolesTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.stores.instance' => ['driver' => 'array'],
            'cache.default' => 'instance',
        ]);
        Cache::forgetDriver(['instance']);
        Roles::flushCache();
    }

    protected function tearDown(): void
    {
        $this->app->forgetInstance(PermissionRepository::class);
        Roles::flushCache();

        parent::tearDown();
    }

    /**
     * zp_roles as the repository returns it: the six built-ins plus the given custom rows.
     *
     * @param  array<int, array<string, mixed>>  $customRoles
     */
    private function useRoleTable(array $customRoles): void
    {
        $roleRows = [];
        foreach ([5 => 'readonly', 10 => 'commenter', 20 => 'editor', 30 => 'manager', 40 => 'admin', 50 => 'owner'] as $key => $name) {
            $roleRows[] = ['name' => $name, 'displayName' => ucfirst($name), 'roleKey' => $key, 'level' => $key, 'baseRole' => null, 'isSystem' => 1];
        }

        foreach ($customRoles as $customRole) {
            $roleRows[] = $customRole + ['isSystem' => 0, 'baseRole' => null];
        }

        $this->app->instance(PermissionRepository::class, $this->make(PermissionRepository::class, [
            'getAllRoles' => fn (): array => $roleRows,
        ]));
        Roles::flushCache();
    }

    private function useQaLeadRole(): void
    {
        $this->useRoleTable([
            ['name' => 'qa_lead', 'displayName' => 'QA Lead', 'roleKey' => 100, 'level' => 20, 'baseRole' => 'editor'],
        ]);
    }

    public function test_without_a_database_the_built_in_roles_are_used(): void
    {
        $this->app->instance(PermissionRepository::class, $this->make(PermissionRepository::class, [
            'getAllRoles' => fn () => throw new \RuntimeException('no database'),
        ]));
        Roles::flushCache();

        $this->assertSame([5 => 'readonly', 10 => 'commenter', 20 => 'editor', 30 => 'manager', 40 => 'admin', 50 => 'owner'], Roles::getRoles());
        $this->assertSame(40, Roles::getRoleLevel('admin'));
        $this->assertSame(40, Roles::getRoleKey('admin'));
        $this->assertSame('manager', Roles::getBaseRole(30));
        $this->assertFalse(Roles::getRoleLevel('superuser'));
    }

    public function test_a_custom_role_has_its_own_key_but_ranks_by_level(): void
    {
        $this->useQaLeadRole();

        $this->assertSame('qa_lead', Roles::getRoleString(100));
        $this->assertSame(100, Roles::getRoleKey('qa_lead'));
        $this->assertSame(20, Roles::getRoleLevel('qa_lead'));
        $this->assertSame(20, Roles::getRoleLevel('100'));

        $this->assertTrue(Roles::isAtLeast('qa_lead', Roles::$editor));
        $this->assertFalse(Roles::isAtLeast('qa_lead', Roles::$manager));
        $this->assertTrue(Roles::isAtLeast(Roles::$manager, 'qa_lead'));
    }

    public function test_a_big_custom_key_never_counts_as_admin(): void
    {
        $this->useQaLeadRole();

        $this->assertSame(['40', '50'], Roles::keysAtLeast(Roles::$admin));
        $this->assertSame(['20', '100', '30', '40', '50'], Roles::keysAtLeast(Roles::$editor));
    }

    public function test_base_role_is_stored_or_derived_from_the_level(): void
    {
        $this->useRoleTable([
            ['name' => 'qa_lead', 'displayName' => 'QA Lead', 'roleKey' => 100, 'level' => 20, 'baseRole' => 'editor'],
            ['name' => 'team_lead', 'displayName' => 'Team Lead', 'roleKey' => 101, 'level' => 30, 'baseRole' => null],
            ['name' => 'bad_base', 'displayName' => 'Bad', 'roleKey' => 102, 'level' => 10, 'baseRole' => 'qa_lead'],
        ]);

        $this->assertSame('editor', Roles::getBaseRole('qa_lead'));
        $this->assertSame('manager', Roles::getBaseRole('team_lead'));
        $this->assertSame('commenter', Roles::getBaseRole('bad_base'), 'a base role must be a built-in role');
        $this->assertSame('owner', Roles::getBaseRole('owner'));
    }

    public function test_custom_roles_below_admin_can_be_given_per_project(): void
    {
        $this->useRoleTable([
            ['name' => 'qa_lead', 'displayName' => 'QA Lead', 'roleKey' => 100, 'level' => 20, 'baseRole' => 'editor'],
            ['name' => 'deputy_admin', 'displayName' => 'Deputy', 'roleKey' => 101, 'level' => 40, 'baseRole' => 'admin'],
        ]);

        $this->assertSame([5, 10, 20, 100, 30], array_keys(Roles::getProjectAssignableRoles()));
    }

    public function test_custom_roles_show_their_display_name(): void
    {
        $this->useQaLeadRole();

        $this->assertSame('QA Lead', Roles::label(100));
        $this->assertSame('QA Lead', Roles::label('qa_lead'));
        $this->assertSame(__('label.roles.editor'), Roles::label('editor'));
        $this->assertSame('', Roles::label('superuser'));
    }

    public function test_rows_from_before_the_role_key_column_are_keyed_by_level(): void
    {
        $this->app->instance(PermissionRepository::class, $this->make(PermissionRepository::class, [
            'getAllRoles' => fn (): array => [
                ['name' => 'editor', 'displayName' => 'Editor', 'level' => 20, 'isSystem' => 1],
                ['name' => 'owner', 'displayName' => 'Owner', 'level' => 50, 'isSystem' => 1],
            ],
        ]));
        Roles::flushCache();

        $this->assertSame(20, Roles::getRoleKey('editor'));
        $this->assertSame(50, Roles::getRoleKey('owner'));
        $this->assertSame(40, Roles::getRoleKey('admin'), 'built-ins missing from the table still exist');
    }

    public function test_legacy_checks_treat_a_custom_role_as_its_base_role(): void
    {
        $this->useQaLeadRole();
        session(['userdata' => ['id' => 7, 'role' => 'qa_lead']]);

        $this->assertTrue(Auth::userIsAtLeast(Roles::$editor, true));
        $this->assertFalse(Auth::userIsAtLeast(Roles::$manager, true));
        $this->assertTrue(Auth::userHasRole(Roles::$editor, true));
        $this->assertTrue(Auth::userHasRole([Roles::$owner, Roles::$editor], true));
        $this->assertTrue(Auth::userHasRole('qa_lead', true));
        $this->assertFalse(Auth::userHasRole([Roles::$owner, Roles::$admin], true));
    }

    public function test_a_custom_manager_level_role_keeps_its_global_role_in_projects(): void
    {
        $this->useRoleTable([
            ['name' => 'team_lead', 'displayName' => 'Team Lead', 'roleKey' => 101, 'level' => 30, 'baseRole' => 'manager'],
        ]);
        session(['userdata' => ['id' => 7, 'role' => 'team_lead', 'projectRole' => 'readonly']]);

        $this->assertSame('team_lead', Auth::getRoleToCheck(false));
    }

    public function test_role_ceiling_compares_levels(): void
    {
        $this->useQaLeadRole();
        session(['userdata' => ['id' => 4, 'role' => 'manager']]);

        $ceiling = new RoleCeiling;
        $this->assertTrue($ceiling->canAssign(100));
        $this->assertTrue($ceiling->canAssign('qa_lead'));
        $this->assertSame([5, 10, 20, 100, 30], array_keys($ceiling->assignableRoles()));

        session(['userdata' => ['id' => 4, 'role' => 'qa_lead']]);
        $this->assertFalse((new RoleCeiling)->canAssign(Roles::$manager));
        $this->assertTrue((new RoleCeiling)->canAssign(Roles::$editor));
    }

    public function test_a_queue_worker_picks_up_role_changes_at_the_next_job(): void
    {
        $this->useQaLeadRole();
        $this->assertSame(20, Roles::getRoleLevel('qa_lead'));

        // Another process lowers the role and flushes the shared cache; this worker's copy is stale.
        $this->app->instance(PermissionRepository::class, $this->make(PermissionRepository::class, [
            'getAllRoles' => fn (): array => [
                ['name' => 'qa_lead', 'displayName' => 'QA Lead', 'roleKey' => 100, 'level' => 5, 'baseRole' => 'readonly', 'isSystem' => 0],
            ],
        ]));
        Cache::store()->flush();
        $this->assertSame(20, Roles::getRoleLevel('qa_lead'));

        $this->app->make('events')->dispatch(new JobProcessing('database', $this->makeEmpty(Job::class)));

        $this->assertSame(5, Roles::getRoleLevel('qa_lead'));
        $this->assertSame('readonly', Roles::getBaseRole('qa_lead'));
    }
}
